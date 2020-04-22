<?php
/**
 *
 * @author    Gert Hammes <info@gerthammes.de>
 * @copyright 2019 Gert Hammes
 * @since     2019-11-08
 */

namespace App\Controller;


use App\Admin\Frontend\AbstractFrontendAdmin;
use App\Admin\Frontend\ServiceAdmin;
use Sonata\AdminBundle\Controller\CRUDController;

/**
 * Class ServiceController
 *
 * @author    Gert Hammes <info@gerthammes.de>
 * @copyright 2019 Gert Hammes
 * @since     2019-11-08
 */
class ServiceController extends CRUDController
{

    public function index()
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('sonata_admin_dashboard');
        }
        return $this->redirectToRoute('frontend_app_service_list');
    }

    /**
     * Contextualize the admin class depends on the current request.
     *
     * @throws \RuntimeException
     */
    protected function configure()
    {
        $request = $this->getRequest();
        $request->attributes->set('_sonata_admin', ServiceAdmin::class);
        parent::configure();
        /** @var $admin AbstractFrontendAdmin */
        $admin = $this->admin;
        $admin->setAppContext(AbstractFrontendAdmin::APP_CONTEXT_FE);
    }

    public function getChoicesAction()
    {
        $data = [
            'bla' => 'blubb',
        ];
        return $this->json($data);
    }
}
