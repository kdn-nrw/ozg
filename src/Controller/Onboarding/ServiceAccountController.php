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


use App\Admin\Frontend\ServiceAccountAdmin;
use App\Controller\AbstractEditableFrontendController;

/**
 * Class ServiceAccountController
 */
class ServiceAccountController extends AbstractEditableFrontendController
{
    /**
     * @inheritDoc
     */
    protected function getDefaultRouteName(): string
    {
        return 'frontend_app_onboarding_service_account_list';
    }

    /**
     * @inheritDoc
     */
    protected function getAdminClassName(): string
    {
        return ServiceAccountAdmin::class;
    }
}
