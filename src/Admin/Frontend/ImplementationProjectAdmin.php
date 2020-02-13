<?php

namespace App\Admin\Frontend;

use App\Datagrid\CustomDatagrid;
use App\Entity\ImplementationStatus;
use App\Entity\Subject;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;


class ImplementationProjectAdmin extends AbstractFrontendAdmin
{
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('name');
        $datagridMapper->add('solutions',
            null,[
                'admin_code' => SolutionAdmin::class,
            ],
            null,
            ['expanded' => false, 'multiple' => true]
        );
        $datagridMapper->add('serviceSystems',
            null,
            [
                'admin_code' => ServiceSystemAdmin::class,
            ],
            null,
            ['expanded' => false, 'multiple' => true]
        );
        $datagridMapper->add('serviceSystems.situation.subject',
            null,
            ['label' => 'app.situation.entity.subject'],
            null,
            ['expanded' => false, 'multiple' => true]
        );
        $datagridMapper->add('status');
        $datagridMapper->add('projectStartAt');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name')
            ->add('serviceSystems.situation.subject', 'string', [
                'label' => 'app.situation.entity.subject',
                //'associated_property' => 'name',
                'template' => 'ImplementationProjectAdmin/list-service-system-subjects.html.twig',
            ])
            ->add('status', 'choice', [
                'editable' => true,
                'class' => ImplementationStatus::class,
                'catalogue' => 'messages',
            ])
            ->add('projectStartAt', null, [
                // https://unicode-org.github.io/icu-docs/apidoc/released/icu4c/classSimpleDateFormat.html#details
                'pattern' => 'MMMM yyyy',
            ]);
        $this->addDefaultListActions($listMapper);
    }

    /**
     * @inheritdoc
     */
    public function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('name')
            ->add('solutions', null, [
                'admin_code' => SolutionAdmin::class,
            ])
            ->add('serviceSystems', null, [
                'admin_code' => ServiceSystemAdmin::class,
            ])
            ->add('description')
            ->add('status', 'choice', [
                'editable' => false,
                'class' => ImplementationStatus::class,
                'catalogue' => 'messages',
            ])
            ->add('projectStartAt', null, [
                // https://unicode-org.github.io/icu-docs/apidoc/released/icu4c/classSimpleDateFormat.html#details
                'pattern' => 'MMMM yyyy',
            ])
            ->add('notes', 'html');
    }

    public function isGranted($name, $object = null)
    {
        if (in_array($name, ['LIST', 'VIEW', 'SHOW'])) {//, 'EXPORT'
            return true;
        }
        return parent::isGranted($name, $object);
    }

    public function buildDatagrid()
    {
        if ($this->datagrid) {
            return;
        }
        parent::buildDatagrid();
        /** @var CustomDatagrid $datagrid */
        $datagrid = $this->datagrid;
        $modelManager = $this->getModelManager();
        //$situations = $modelManager->findBy(Situation::class);
        //$datagrid->addFilterMenu('serviceSystem.situation', $situations, 'app.service_system.entity.situation');
        $subjects = $modelManager->findBy(Subject::class);
        $datagrid->addFilterMenu('serviceSystems.situation.subject', $subjects, 'app.situation.entity.subject');
    }
}
