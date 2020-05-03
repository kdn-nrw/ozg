<?php
/**
 * This file is part of the KDN OZG package.
 *
 * @author    Gert Hammes <info@gerthammes.de>
 * @copyright 2020 Gert Hammes
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Exporter\Source;

use App\Entity\Base\BaseEntity;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyPath;

/**
 * Class CustomQuerySourceIterator
 * Support export of collection fields and use caching for collection fields
 *
 * the current function in Sonata\Exporter\Source\DoctrineORMQuerySourceIterator is marked as final and can therefore
 * not be extended
 * => Override whole file to enable caching in "current" function
 */
class CustomEntityValueProvider
{
    public const CACHE_PREFIX = 'cqsi';

    /**
     * @var CacheItemPoolInterface
     */
    private $cache;
    private const DATE_PARTS = [
        'y' => 'Y',
        'm' => 'M',
        'd' => 'D',
    ];
    private const TIME_PARTS = [
        'h' => 'H',
        'i' => 'M',
        's' => 'S',
    ];

    /**
     * @var array
     */
    protected $propertyPaths = [];

    /**
     * @var PropertyAccessor
     */
    protected $propertyAccessor;

    /**
     * @var string default DateTime format
     */
    protected $dateTimeFormat;
    /**
     * @var string
     */
    private $context;

    /**
     * Temporary cache for data records
     * Store data in groups of 200 records. Because the cache is updated in the indexing process the entities are
     * all updated at the same time and normally the cache entry will be valid
     * Since the records are only used in the export and the exported items are usually exported by sorted by id,
     * this will speed up the export process
     * Additionally this reduces he number of cache files
     * If sorted by any other field than id, the export will be a little slower
     *
     * @var array
     */
    private $dataGroupCache = [];

    /**
     * @param array $fields Fields to export
     * @param string|null $cacheDir
     * @param string $context
     * @param string $dateTimeFormat
     */
    public function __construct(array $fields, ?string $cacheDir, string $context, string $dateTimeFormat = 'r')
    {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();

        foreach ($fields as $name => $field) {
            if (\is_string($name) && \is_string($field)) {
                $this->propertyPaths[$name] = new PropertyPath($field);
            } else {
                $this->propertyPaths[$field] = new PropertyPath($field);
            }
        }
        $this->dateTimeFormat = $dateTimeFormat;
        $this->setCache($cacheDir);
        $this->context = $context;
    }

    /**
     * @param string|null $cacheDir
     */
    private function setCache(?string $cacheDir): void
    {
        $this->cache = $this->initCache($cacheDir);
        // Add property accessor cache to improve performance
        $propertyAccessorBuilder = PropertyAccess::createPropertyAccessorBuilder();
        $propertyAccessorBuilder->setCacheItemPool($this->cache);
        $this->propertyAccessor = $propertyAccessorBuilder->getPropertyAccessor();
    }

    /**
     * Get cache instance
     *
     * @param string|null $cacheDir
     * @return FilesystemAdapter
     */
    private function initCache(?string $cacheDir): FilesystemAdapter
    {

        if (null === $cacheDir) {
            $endPos = strrpos(__DIR__, '/src/');
            if ($endPos === false) {
                $endPos = strrpos(__DIR__, '/vendor/');
            }
            $cacheDir = substr(__DIR__, 0, $endPos) . '/var/cache/dev';
        }
        $cacheDir .= '/app';
        return new FilesystemAdapter('pa_iterator', 14 * 86400, $cacheDir);
    }

    public function getItemData($objectOrArray)
    {
        if ($objectOrArray instanceof BaseEntity) {
            $data = $this->getCacheItemData($objectOrArray);
            unset($data['_tstamp']);
        } else {
            $data = $this->processData($objectOrArray);
        }
        return $data;
    }

    /**
     * Returns the property values of the given entity from the cache; sets/updates the cache entry if necessary
     *
     * @param BaseEntity $objectOrArray
     * @param bool $forceUpdate Force update of cache item
     * @return array|mixed
     */
    public function getCacheItemData(BaseEntity $objectOrArray, $forceUpdate = false)
    {
        $itemId = $objectOrArray->getId();
        if (null !== $modifiedAt = $objectOrArray->getModifiedAt()) {
            $tstamp = $modifiedAt->getTimestamp();
            $lastChanged = $tstamp;
        } else {
            $tstamp = time();
            $lastChanged = strtotime('-2 weeks');
        }
        // First check if the given record is in the list of cached records
        if (!$forceUpdate && isset($this->dataGroupCache[$itemId]) &&
            !empty($this->dataGroupCache[$itemId]['_tstamp']) &&
            $this->dataGroupCache[$itemId]['_tstamp'] >= $lastChanged) {
            return $this->dataGroupCache[$itemId];
        }
        $itemGroup = (int) floor($itemId/200);
        $key = str_replace('\\', '.', get_class($objectOrArray)) . '..' . $itemGroup;
        try {
            $item = $this->cache->getItem(self::CACHE_PREFIX . $this->context . rawurlencode($key));
            if (!$forceUpdate && $item->isHit()) {
                $data = $item->get();
                $this->dataGroupCache = $data;
                if (!empty($data[$itemId]) && !empty($data[$itemId]['_tstamp']) &&
                    $data[$itemId]['_tstamp'] >= $lastChanged) {
                    return $data[$itemId];
                }
            }
        } catch (InvalidArgumentException $e) {
            $item = null;
            unset($e);
        }
        $data[$itemId] = $this->processData($objectOrArray);
        $data[$itemId]['_tstamp'] = $tstamp;
        if ($item) {
            $this->cache->save($item->set($data));
            $this->dataGroupCache = $data;
        }
        return $data[$itemId];
    }

    /**
     * @param object|array $objectOrArray
     * @return array
     */
    protected function processData($objectOrArray)
    {
        $data = [];
        foreach ($this->propertyPaths as $name => $propertyPath) {
            try {
                $data[$name] = $this->getValue($this->propertyAccessor->getValue($objectOrArray, $propertyPath));
            } catch (UnexpectedTypeException $e) {
                //non existent object in path will be ignored
                $data[$name] = null;
            }
        }
        return $data;
    }

    final public function setDateTimeFormat(string $dateTimeFormat): void
    {
        $this->dateTimeFormat = $dateTimeFormat;
    }

    final public function getDateTimeFormat(): string
    {
        return $this->dateTimeFormat;
    }

    /**
     * @param \DateInterval $interval
     * @return string An ISO8601 duration
     */
    public function getDuration(\DateInterval $interval): string
    {
        $datePart = '';
        foreach (self::DATE_PARTS as $datePartAttribute => $datePartAttributeString) {
            if ($interval->$datePartAttribute !== 0) {
                $datePart .= $interval->$datePartAttribute . $datePartAttributeString;
            }
        }

        $timePart = '';
        foreach (self::TIME_PARTS as $timePartAttribute => $timePartAttributeString) {
            if ($interval->$timePartAttribute !== 0) {
                $timePart .= $interval->$timePartAttribute . $timePartAttributeString;
            }
        }

        if ('' === $datePart && '' === $timePart) {
            return 'P0Y';
        }

        return 'P' . $datePart . ('' !== $timePart ? 'T' . $timePart : '');
    }

    /**
     * Extend parent getValue function to support Collection objects
     *
     * @param mixed $value
     * @return string|null
     */
    protected function getValue($value)
    {
        //if value is array or collection, creates string
        if (\is_array($value) || $value instanceof \Traversable) {
            $result = array();
            foreach ($value as $item) {
                $result[] = $this->getValue($item);
            }
            $value = implode(', ', $result);
        } elseif ($value instanceof \DateTimeInterface) {
            $value = $value->format($this->dateTimeFormat);
        } elseif ($value instanceof \DateInterval) {
            $value = $this->getDuration($value);
        } elseif (\is_object($value)) {
            $value = (string)$value;
        } elseif (\is_string($value)) {
            $value = trim(strip_tags($value));
        }

        return $value;
    }
}