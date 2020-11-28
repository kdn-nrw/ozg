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

namespace App\Api\Consumer;

use App\Api\Consumer\DataProcessor\ZuFiDataProcessor;
use App\Api\Consumer\Model\ZuFi\ZuFiResultCollection;
use App\Api\Consumer\Model\ZuFiDemand;
use App\Api\Consumer\Model\ZuFiResult;
use App\Api\Form\Type\ZuFiType;
use App\DependencyInjection\InjectionTraits\InjectManagerRegistryTrait;
use App\Entity\Api\ServiceBaseResult;
use App\Entity\FederalInformationManagementType;
use App\Entity\Repository\CommuneRepository;
use App\Entity\Service;
use App\Entity\StateGroup\Commune;

class ZuFiConsumer extends AbstractApiConsumer
{
    use InjectManagerRegistryTrait;

    /**
     * @required
     * @param ZuFiDataProcessor $dataProcessor
     */
    public function injectDataProcessor(ZuFiDataProcessor $dataProcessor): void
    {
        $this->dataProcessor = $dataProcessor;
    }

    /**
     * Returns the class name for the result model
     *
     * @return string
     */
    protected function getDemandClass(): string
    {
        return ZuFiDemand::class;
    }

    /**
     * Returns the search result template for this consumer
     * @return string
     */
    public function getResultTemplate(): string
    {
        return 'Vsm/Partials/Results/_zu-fi-results.html.twig';
    }

    /**
     * Returns the class name for the search form type
     *
     * @return string
     */
    public function getFormTypeClass(): string
    {
        return ZuFiType::class;
    }

    /**
     * Returns the class name for the result model
     *
     * @return string
     */
    public function getImportModelClass(): string
    {
        return ZuFiResult::class;
    }

    /**
     * Import commune data
     * @param int $limit Limit the number of rows to be imported
     * @param array $serviceKeys Optional list of service keys to be imported
     * @return int
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \ReflectionException
     */
    public function importCommuneServiceResults(int $limit = 500, array $serviceKeys = []): int
    {
        /** @var CommuneRepository $repository */
        $em = $this->getEntityManager();
        $em->getConfiguration()->setSQLLogger(null);
        $repository = $em->getRepository(Commune::class);
        $communes = $repository->findAll();
        // Randomize order of communes
        shuffle($communes);
        $totalImportRowCount = 0;
        foreach ($communes as $commune) {
            /** @var Commune $commune */
            if ($commune->getRegionalKey()) {
                $totalImportRowCount += $this->importServiceResults($limit, null, $commune, $serviceKeys);
                ++$totalImportRowCount;
            }
        }
        return $totalImportRowCount;
    }

    /**
     * Import service data
     * @param int $limit Limit the number of rows to be imported
     * @param string|null $mapToFimType
     * @param Commune|null $commune
     * @param array $serviceKeys Optional list of service keys to be imported
     * @return int
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \ReflectionException
     */
    public function importServiceResults(
        int $limit,
        ?string $mapToFimType,
        ?Commune $commune,
        array $serviceKeys = []
    ): int
    {
        $demand = $this->getDemand();
        /** @var ZuFiDemand $demand */
        if (null !== $commune) {
            $demand->setRegionalKey($commune->getRegionalKey());
        }
        if (!$demand->getZipCode() && !$demand->getRegionalKey()) {
            throw new InvalidParametersException('The demand parameters are not set. You must set the zip code or regional key in the demand!');
        }
        $validFimTypes = array_keys(FederalInformationManagementType::$mapTypes);
        if (null === $commune && (null === $mapToFimType || !in_array($mapToFimType, $validFimTypes, false))) {
            throw new InvalidParametersException(sprintf(
                'The given fim type %s is not valid. Valid values are %s',
                $mapToFimType ?? 'NULL',
                implode(', ', $validFimTypes)
            ));
        }
        $em = $this->getEntityManager();
        $em->getConfiguration()->setSQLLogger(null);
        $repository = $em->getRepository(Service::class);
        $sbrRepository = $em->getRepository(ServiceBaseResult::class);
        $sbrRows = $sbrRepository->findBy(['commune' => $commune]);
        $serviceUpdateMap = [];
        $updateThreshold = strtotime('-2 weeks');
        foreach ($sbrRows as $sbrRow) {
            /** @var ServiceBaseResult $sbrRow */
            $lastUpdate = $sbrRow->getModifiedAt() ?? $sbrRow->getCreatedAt();
            $serviceUpdateMap[$sbrRow->getServiceKey()] = null !== $lastUpdate ? $lastUpdate->getTimestamp() : $updateThreshold;
        }
        $services = $repository->findAll();
        /** @var ZuFiDataProcessor $dataProcessor */
        $dataProcessor = $this->dataProcessor;
        $this->dataProvider->setApiConsumerEntity($this->getApiConsumerEntity());
        $dataProcessor->setImportModelClass($this->getImportModelClass());
        $dataProcessor->setOutput($this->output);
        $dataProcessor->setImportSource($this->getImportSourceKey());
        $totalImportRowCount = 0;
        foreach ($services as $service) {
            /** @var Service $service */
            $serviceKey = $service->getServiceKey();
            $lastUpdate = $serviceUpdateMap[$serviceKey] ?? null;
            if (($lastUpdate && $lastUpdate > $updateThreshold)
                || (!empty($serviceKeys) && !in_array($serviceKey, $serviceKeys, false))) {
                continue;
            }
            $demand->setServiceKey($serviceKey);
            $this->dataProvider->setDemand($demand);
            $this->dataProvider->process($dataProcessor);
            $results = $this->dataProcessor->getResultCollection();
            /** @var ZuFiResultCollection $results */
            $serviceModel = $results->getServiceBase();
            if (null !== $serviceModel && $serviceModel->getServiceKey()) {
                $dataProcessor->addServiceResult($service, $serviceModel);
                ++$totalImportRowCount;
                if ($totalImportRowCount % 100 === 0) {
                    $dataProcessor->processImportedServiceResults($demand->getRegionalKey(), $mapToFimType, $commune);
                }
                if ($totalImportRowCount >= $limit) {
                    break;
                }
            }
        }
        $dataProcessor->processImportedServiceResults($demand->getRegionalKey(), $mapToFimType, $commune);
        return $totalImportRowCount;
    }
}
