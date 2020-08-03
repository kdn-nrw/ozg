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

namespace App\Admin\Frontend;

use App\Admin\Traits\AddressTrait;
use App\Admin\Traits\DatePickerTrait;
use App\Admin\Traits\OrganisationTrait;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;


class ModelRegionProjectAdmin extends AbstractFrontendAdmin
{
    use AddressTrait;
    use DatePickerTrait;
    use OrganisationTrait;

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $this->addFullTextDatagridFilter($datagridMapper);
        $datagridMapper->add('name');
        $this->addOrganisationsDatagridFilters($datagridMapper);
        $this->addDatePickersDatagridFilters($datagridMapper, 'projectStartAt');
        $this->addDatePickersDatagridFilters($datagridMapper, 'projectEndAt');
        $datagridMapper
            ->add('description')
            ->add('usp')
            ->add('communesBenefits')
            ->add('transferableService')
            ->add('transferableStart');
        $datagridMapper->add('modelRegions',
            null, [
                'admin_code' => ModelRegionAdmin::class,
            ],
            null,
            ['expanded' => false, 'multiple' => true]
        );
        $datagridMapper->add('solutions',
            null, [
                'admin_code' => SolutionAdmin::class,
            ],
            null,
            ['expanded' => false, 'multiple' => true]
        );
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper->addIdentifier('name');
        $this->addDatePickersListFields($listMapper, 'projectStartAt');
        $this->addDatePickersListFields($listMapper, 'projectEndAt');
        $this->addOrganisationsListFields($listMapper);
        $this->addDefaultListActions($listMapper);
    }

    /**
     * @inheritdoc
     */
    public function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper->add('name')
            ->add('description');
        $this->addDatePickersShowFields($showMapper, 'projectStartAt');
        $this->addDatePickersShowFields($showMapper, 'projectEndAt');
        $showMapper
            ->add('usp')
            ->add('communesBenefits')
            ->add('transferableService')
            ->add('transferableStart');
        $this->addOrganisationsShowFields($showMapper);
        $showMapper
            ->add('modelRegions', null, [
                'admin_code' => ModelRegionAdmin::class,
            ]);
        $showMapper
            ->add('solutions', null, [
                'admin_code' => SolutionAdmin::class,
                'template' => 'General/Show/show-solutions.html.twig',
                'route' => [
                    'name' => 'show',
                ],
                'showServices' => true,
            ]);
    }


    protected function getRoutePrefix(): string
    {
        return 'frontend_app_modelregionproject';
    }
}
