<?php
/**
 * This file is part of the KDN OZG package.
 *
 * @author    Gert Hammes <info@gerthammes.de>
 * @copyright 2021 Gert Hammes
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Onboarding;


use App\Entity\Onboarding\CommuneInfo;
use App\Service\Onboarding\InjectOnboardingManagerTrait;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CommuneInfoAdminController
 */
class CommuneInfoAdminController extends CRUDController
{
    use InjectOnboardingManagerTrait;

    /**
     * @inheritDoc
     */
    protected function preList(Request $request)
    {
        $this->onboardingManager->createItems(CommuneInfo::class);
        return null;
    }
}