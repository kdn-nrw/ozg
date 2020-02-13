<?php

namespace App\Admin;

use App\Admin\Traits\ServiceProviderTrait;
use App\Admin\Traits\ServiceTrait;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;


class LaboratoryAdmin extends AbstractAppAdmin
{
    use ServiceTrait;
    use ServiceProviderTrait;

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name', TextType::class)
            ->add('description', TextareaType::class, [
                'required' => false,
            ])
            ->add('url', UrlType::class, [
                'required' => false,
            ]);
        $this->addServiceProvidersFormFields($formMapper);
        $formMapper
            ->add('participantsOther', TextareaType::class, [
                'required' => false,
            ]);
        $this->addServicesFormFields($formMapper);
        $formMapper->add('implementationUrl', UrlType::class, [
                'required' => false,
            ])
            ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('name');
        $this->addServiceProvidersDatagridFilters($datagridMapper);
        $this->addServicesDatagridFilters($datagridMapper);
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name')
            ->add('url', 'url');
        $this->addServicesListFields($listMapper);
        $this->addDefaultListActions($listMapper);
    }

    /**
     * @inheritdoc
     */
    public function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('name')
            ->add('description')
            ->add('url', 'url')
            ->add('participantsOther');
        $this->addServiceProvidersShowFields($showMapper);
        $this->addServicesShowFields($showMapper);
    }
}
