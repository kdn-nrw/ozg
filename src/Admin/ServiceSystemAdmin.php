<?php
/**
 * This file is part of the KDN OZG package.
 *
 * @author    Gert Hammes <info@gerthammes.de>
 * @copyright 2019 Gert Hammes
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Admin;

use App\Admin\Traits\MinistryStateTrait;
use App\Admin\Traits\ServiceTrait;
use App\Admin\Traits\SolutionTrait;
use App\Entity\Jurisdiction;
use App\Entity\ServiceSystem;
use App\Form\DataTransformer\EntityCollectionToIdArrayTransformer;
use App\Model\ExportSettings;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ChoiceFieldMaskType;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class ServiceSystemAdmin extends AbstractAppAdmin implements ExtendedSearchAdminInterface, EnableFullTextSearchAdminInterface
{
    use MinistryStateTrait;
    use ServiceTrait;
    use SolutionTrait;

    /**
     * @var string[]
     */
    protected $customLabels = [
        'app.service_system.entity.situation_subject' => 'app.situation.entity.subject',
    ];

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('app.service_system.tabs.general', ['tab' => true])
            ->with('app.service_system.groups.general', [
                'label' => false,
            ])
            ->add('name', TextType::class, [
                'required' => true,
            ])
            ->add('serviceKey', TextType::class, [
                'required' => true,
            ])
            ->add('situation', ModelType::class, [
                'btn_add' => false,
                'choice_translation_domain' => false,
            ])/*
            ->add('status', ModelType::class, [
                'btn_add' => false,
                'required' => true,
                'choice_translation_domain' => false,
            ])*/
            ->add('priority', ModelType::class, [
                'btn_add' => false,
                'placeholder' => '',
                'required' => false,
                'choice_translation_domain' => false,
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
            ]);
        //$this->addLaboratoriesFormFields($formMapper);
        $formMapper->add('jurisdictions', ChoiceFieldMaskType::class, [
            'label' => 'app.service_system.entity.jurisdictions_form',
            'choices' => [
                'app.jurisdiction.entity.types.country' => Jurisdiction::TYPE_COUNTRY,
                'app.jurisdiction.entity.types.state' => Jurisdiction::TYPE_STATE,
                'app.jurisdiction.entity.types.commune' => Jurisdiction::TYPE_COMMUNE,
            ],
            'multiple' => true,
            'map' => [
                Jurisdiction::TYPE_COUNTRY => [],
                Jurisdiction::TYPE_STATE => ['stateMinistries', 'bureaus'],
                Jurisdiction::TYPE_COMMUNE => ['bureaus'],
            ],
            'required' => true,
        ]);
        $formMapper->get('jurisdictions')->addModelTransformer(new EntityCollectionToIdArrayTransformer(
            $this->getModelManager(),
            Jurisdiction::class
        ));
        $this->addStateMinistriesFormFields($formMapper);
        $formMapper
            ->add('bureaus', ModelType::class, [
                'label' => 'app.service_system.entity.bureaus_form',
                'btn_add' => false,
                'placeholder' => '',
                'required' => false,
                'multiple' => true,
                'by_reference' => false,
                'choice_translation_domain' => false,
            ]);
        $formMapper->add('ruleAuthorities', ChoiceFieldMaskType::class, [
            'label' => 'app.service_system.entity.rule_authorities_form',
            'choices' => [
                'app.jurisdiction.entity.types.country' => Jurisdiction::TYPE_COUNTRY,
                'app.jurisdiction.entity.types.state' => Jurisdiction::TYPE_STATE,
                'app.jurisdiction.entity.types.commune' => Jurisdiction::TYPE_COMMUNE,
            ],
            'multiple' => true,
            'map' => [
                Jurisdiction::TYPE_COUNTRY => [],
                Jurisdiction::TYPE_STATE => ['authorityStateMinistries', 'authorityBureaus'],
                Jurisdiction::TYPE_COMMUNE => ['authorityBureaus'],
            ],
            'required' => true,
        ]);
        $formMapper->get('ruleAuthorities')->addModelTransformer(new EntityCollectionToIdArrayTransformer(
            $this->getModelManager(),
            Jurisdiction::class
        ));
        $formMapper
            ->add('authorityStateMinistries', ModelType::class, [
                'label' => 'app.service_system.entity.authority_state_ministries',
                'btn_add' => false,
                'placeholder' => '',
                'required' => false,
                'multiple' => true,
                'by_reference' => false,
                'choice_translation_domain' => false,
            ])
            ->add('authorityBureaus', ModelType::class, [
                'label' => 'app.service_system.entity.authority_bureaus_form',
                'btn_add' => false,
                'placeholder' => '',
                'required' => false,
                'multiple' => true,
                'by_reference' => false,
                'choice_translation_domain' => false,
            ])
            ->add('communeTypes', ModelType::class, [
                'label' => 'app.service_system.entity.commune_types_form',
                'btn_add' => false,
                'placeholder' => '',
                'required' => false,
                'multiple' => true,
                'by_reference' => false,
                'choice_translation_domain' => false,
            ]);
        $formMapper->end()
            ->end();
        $formMapper->tab('app.service_system.tabs.services')
            ->with('app.service_system.entity.services', [
                'label' => false,
                'box_class' => 'box-tab',
                'translation_domain' => 'messages',
            ])
            /*->add('services', CollectionType::class, [
                'label' => false,
                'type_options' => [
                    'delete' => true,
                ],
                'by_reference' => false,
            ], [
                'admin_code' => ServiceAdmin::class,
                'edit' => 'inline',
                'inline' => 'natural',
                'sortable' => 'position',
                'ba_custom_hide_fields' => ['serviceSystem',],// 'serviceSolutions'
            ])*/
            ->add('services', ModelAutocompleteType::class, [
                'btn_add' => 'app.common.model_list_type.add',
                'property' => 'name',
                'placeholder' => '',
                'required' => false,
                'multiple' => true,
                'by_reference' => false,
                'btn_catalogue' => 'messages',
            ], [
                    'admin_code' => ServiceAdmin::class,
                ]
            )
            ->end()
            ->end();

        $formMapper->tab('app.service_system.tabs.solutions')
            ->with('app.service_system.entity.solutions', [
                'label' => false,
                'box_class' => 'box-tab',
                'translation_domain' => 'messages',
            ])
            ->add('solutions', ModelType::class,
                [
                    'btn_add' => false,
                    'placeholder' => '',
                    'required' => false,
                    'multiple' => true,
                    'by_reference' => false,
                    'choice_translation_domain' => false,
                ],
                [
                    'admin_code' => SolutionAdmin::class,
                ]
            )
            ->end()
            ->end();
    }

    public function preUpdate($object)
    {
        /** @var ServiceSystem $object */
        $object->saveInheritedValues();
    }

    public function prePersist($object)
    {
        /** @var ServiceSystem $object */
        $object->saveInheritedValues();
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('name');
        $datagridMapper->add('serviceKey');
        // $this->addDefaultDatagridFilter($datagridMapper, 'laboratories');
        $this->addDefaultDatagridFilter($datagridMapper, 'jurisdictions');
        $this->addDefaultDatagridFilter($datagridMapper, 'situation');
        $this->addDefaultDatagridFilter($datagridMapper, 'situation.subject');
        $this->addDefaultDatagridFilter($datagridMapper, 'priority');
        //$datagridMapper->add('status');
        $this->addDefaultDatagridFilter($datagridMapper, 'stateMinistries');
        $this->addDefaultDatagridFilter($datagridMapper, 'solutions');
        $this->addDefaultDatagridFilter($datagridMapper, 'bureaus');
        $this->addDefaultDatagridFilter($datagridMapper, 'services.portals');
        $this->addDefaultDatagridFilter($datagridMapper, 'communeTypes');
        $this->addDefaultDatagridFilter($datagridMapper, 'implementationProjects');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name')
            ->add('serviceKey')
            ->add('jurisdictions', null, [
                'sortable' => true, // IMPORTANT! make the column sortable
                'sort_field_mapping' => [
                    'fieldName' => 'name'
                ],
                // https://stackoverflow.com/questions/36153381/sort-list-view-in-sonata-admin-by-related-entity-fields
                'sort_parent_association_mappings' => [
                    ['fieldName' => 'jurisdictions'],
                ],
                'enable_filter_add' => true,
            ])
            ->add('situation', null, [
                'sortable' => true, // IMPORTANT! make the column sortable
                'sort_field_mapping' => [
                    'fieldName' => 'name'
                ],
                // https://stackoverflow.com/questions/36153381/sort-list-view-in-sonata-admin-by-related-entity-fields
                'sort_parent_association_mappings' => [
                    ['fieldName' => 'situation'],
                ],
                'enable_filter_add' => true,
            ])
            ->add('situation.subject', null, [
                'sortable' => true, // IMPORTANT! make the column sortable
                'sort_field_mapping' => [
                    'fieldName' => 'name'
                ],
                // https://stackoverflow.com/questions/36153381/sort-list-view-in-sonata-admin-by-related-entity-fields
                'sort_parent_association_mappings' => [
                    ['fieldName' => 'situation'],
                    ['fieldName' => 'subject'],
                ],
                'enable_filter_add' => true,
            ])
            ->add('priority')/*
            ->add('status', TemplateRegistry::TYPE_CHOICE, [
                'editable' => true,
                'class' => Status::class,
                'catalogue' => 'messages',
            ])*/
            ->add('references', 'string', [
                'label' => 'app.service_system.entity.references',
                'template' => 'ServiceSystemAdmin/list-references.html.twig',
                'filterParamName' => 'serviceSystem__serviceKey',
                'referenceLabel' => 'app.service.type_label',
                'route' => [
                    'prefix' => 'admin_app_service',
                    'name' => 'list',
                ],
            ]);
        $this->addDefaultListActions($listMapper);
    }

    /**
     * @inheritdoc
     */
    public function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('name', null, [
                'template' => 'ServiceAdmin/show_field_inline_label.html.twig',
            ])
            ->add('serviceKey', null, [
                'template' => 'ServiceAdmin/show_field_inline_label.html.twig',
            ])
            ->add('jurisdictions');
        $this->addStateMinistriesShowFields($showMapper);
        $showMapper->add('bureaus');
        $showMapper->add('ruleAuthorities');
        $showMapper->add('authorityBureaus');
        $showMapper->add('authorityStateMinistries');
        $showMapper->add('communeTypes');
        $this->addServicesShowFields($showMapper);
        $this->addSolutionsShowFields($showMapper);
        //$this->addLaboratoriesShowFields($showMapper);
        $showMapper->add('situation.subject', null, [
            'template' => 'ServiceAdmin/show_many_to_one.html.twig',
        ])
            ->add('situation', null, [
                'template' => 'ServiceAdmin/show_many_to_one.html.twig',
            ])
            ->add('priority', null, [
                'template' => 'ServiceAdmin/show_field_inline_label.html.twig',
            ])/*
            ->add('status', TemplateRegistry::TYPE_CHOICE, [
                'editable' => true,
                'class' => Status::class,
                'catalogue' => 'messages',
                'template' => 'ServiceAdmin/show_choice.html.twig',
            ])*/
            ->add('description', null, [
                'template' => 'ServiceAdmin/show_field_inline_label.html.twig',
            ])
            ->add('implementationProjects', null, [
                'admin_code' => ImplementationProjectAdmin::class,
                'route' => [
                    'name' => 'edit',
                ],
            ])
            ->add('modelRegionProjects', null, [
                'admin_code' => ModelRegionProjectAdmin::class,
                'route' => [
                    'name' => 'edit',
                ],
            ]);
    }

    /**
     * @inheritDoc
     */
    public function getExportSettings(): ExportSettings
    {
        $settings = parent::getExportSettings();
        $settings->addExcludeFields(['contact']);
        /*$settings->setAdditionFields([
            'name', 'serviceKey', 'situation', 'situation.subject',
            'priority',
        ]);*/
        return $settings;
    }
}
