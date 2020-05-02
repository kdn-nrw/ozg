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

namespace App\EventSubscriber;

use App\Admin\AbstractContextAwareAdmin;
use App\Exporter\Source\CustomEntityValueProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class UpdatePropertyAccessorCache
 *
 * @author    Gert Hammes <info@gerthammes.de>
 * @copyright 2020 Gert Hammes
 * @since     2020-05-02
 */
class UpdatePropertyAccessorCache implements EventSubscriberInterface
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * UpdatePropertyAccessorCache constructor.
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            SearchIndexEntityEvent::class => [
                ['updateEntityPropertyCache', 20]
            ],
        ];
    }

    /**
     * Delete thumbnails for uploaded images
     *
     * @param SearchIndexEntityEvent $event
     */
    public function updateEntityPropertyCache(SearchIndexEntityEvent $event)
    {
        $entity = $event->getObject();
        $admin = $event->getAdmin();
        if ($admin instanceof AbstractContextAwareAdmin) {
            // Update the export value cache
            $cacheDir = $this->kernel->getCacheDir();
            $exportFields = $admin->getExportFields();
            $customValueProvider = new CustomEntityValueProvider(
                $exportFields,
                $cacheDir,
                $admin->getAppContext(),
                'd.m.Y H:i:s'
            );
            $customValueProvider->getCacheItemData($entity, true);
        }
    }
}