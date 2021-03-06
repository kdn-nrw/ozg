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

namespace App\Admin\StateGroup;

use App\Admin\AbstractAppAdmin;
use App\Admin\EnableFullTextSearchAdminInterface;
use App\Admin\ExtendedSearchAdminInterface;
use App\Admin\ManufacturerAdmin;
use App\Admin\ServiceAdmin;
use App\Admin\ServiceSystemAdmin;
use App\Admin\SolutionAdmin;
use App\Admin\Traits\AddressTrait;
use App\Admin\Traits\CentralAssociationTrait;
use App\Admin\Traits\ContactTrait;
use App\Admin\Traits\LaboratoryTrait;
use App\Admin\Traits\OrganisationOneToOneTrait;
use App\Admin\Traits\PortalTrait;
use App\Admin\Traits\ServiceProviderTrait;
use App\Admin\Traits\SpecializedProcedureTrait;
use App\Entity\Contact;
use App\Entity\Organisation;
use App\Entity\OrganisationEntityInterface;
use App\Entity\StateGroup\Commune;
use App\Model\ExportSettings;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Model\ModelManager;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;


class CommuneAdmin extends AbstractAppAdmin implements ExtendedSearchAdminInterface, EnableFullTextSearchAdminInterface
{
    protected $baseRoutePattern = 'state/commune';

    use AddressTrait;
    use CentralAssociationTrait;
    use ContactTrait;
    use LaboratoryTrait;
    use OrganisationOneToOneTrait;
    use PortalTrait;
    use ServiceProviderTrait;
    use SpecializedProcedureTrait;

    /**
     * @var string[]
     */
    protected $customLabels = [
        'app.commune.entity.organisation_contacts' => 'app.organisation.entity.contacts',
        'app.commune.entity.organisation_url' => 'app.organisation.entity.url',
        'app.commune.entity.organisation_street' => 'app.organisation.entity.street',
        'app.commune.entity.organisation_zip_code' => 'app.organisation.entity.zip_code',
        'app.commune.entity.organisation_town' => 'app.organisation.entity.town',
    ];

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->tab('default', ['label' => 'app.commune.group.general_data']);
        $formMapper->with('general', [
            'label' => 'app.commune.group.general_data',
            'class' => 'col-xs-12 col-md-6',
        ]);
        $this->addOrganisationOneToOneFormFields($formMapper);
        $formMapper->end();
        $formMapper->with('meta_data', [
            'label' => 'app.commune.group.meta_data',
            'class' => 'col-xs-12 col-md-6',
        ]);
        $formMapper
            ->add('communeType', ModelType::class, [
                'btn_add' => false,
                'placeholder' => '',
                'required' => true,
                'choice_translation_domain' => false,
            ])
            ->add('constituency', ModelType::class,
                [
                    'btn_add' => false,
                    'placeholder' => '',
                    'required' => false,
                    'query' => $this->getConstituencyQueryBuilder(),
                    'choice_translation_domain' => false,
                ],
                [
                    'admin_code' => self::class,
                ])
            ->add('administrativeDistrict', ModelType::class, [
                'btn_add' => false,
                'placeholder' => '',
                'required' => false,
                'choice_translation_domain' => false,
            ])
            ->add('mainEmail', EmailType::class, [
                'required' => false,
            ])
            ->add('officialCommunityKey', TextType::class, [
                'required' => false,
            ])
            ->add('regionalKey', TextType::class, [
                'required' => false,
            ]);
        $formMapper->end();
        $formMapper->with('administration_contact', [
            'label' => 'app.commune.group.administration_contact',
            'class' => 'clear-left-md col-xs-12 col-md-6',
        ]);
        $formMapper
            ->add('administrationPhoneNumber', TextType::class, [
                'required' => false,
            ])
            ->add('administrationFaxNumber', TextType::class, [
                'required' => false,
            ])
            ->add('administrationEmail', EmailType::class, [
                'required' => false,
            ])
            ->add('administrationUrl', UrlType::class, [
                'required' => false,
            ]);
        $formMapper->end();
        $formMapper->with('services_data', [
            'label' => 'app.commune.group.reference_data',
            'class' => 'col-xs-12 col-md-6',
        ]);
        $this->addServiceProvidersFormFields($formMapper);
        $this->addCentralAssociationsFormFields($formMapper);
        $this->addSpecializedProceduresFormFields($formMapper);
        $this->addPortalsFormFields($formMapper);
        $this->addLaboratoriesFormFields($formMapper);
        $formMapper->end();
        $formMapper->end();
    }

    public function preUpdate($object)
    {
        /** @var OrganisationEntityInterface $object */
        $this->updateOrganisation($object);
    }

    public function prePersist($object)
    {
        /** @var OrganisationEntityInterface $object */
        $this->updateOrganisation($object);
    }

    private function updateOrganisation(OrganisationEntityInterface $object)
    {
        /** @var ModelManager $modelManager */
        $modelManager = $this->getModelManager();
        /** @var OrganisationEntityInterface $object */
        $organisation = $object->getOrganisation();
        $organisation->setFromReference($object);
        $orgEm = $modelManager->getEntityManager(Organisation::class);
        if (!$orgEm->contains($organisation)) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $orgEm->persist($organisation);
        }
        $contacts = $organisation->getContacts();
        $contactEm = $modelManager->getEntityManager(Contact::class);
        foreach ($contacts as $contact) {
            if (!$contactEm->contains($contact)) {
                $contact->setOrganisation($organisation);
                /** @noinspection PhpUnhandledExceptionInspection */
                $contactEm->persist($contact);
            }
        }
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('name');
        $this->addOrganisationOneToOneDatagridFilters($datagridMapper);
        $this->addDefaultDatagridFilter($datagridMapper, 'serviceProviders');
        $this->addDefaultDatagridFilter($datagridMapper, 'centralAssociations');
        $this->addDefaultDatagridFilter($datagridMapper, 'specializedProcedures');
        $this->addDefaultDatagridFilter($datagridMapper, 'portals');
        $this->addDefaultDatagridFilter($datagridMapper, 'laboratories');
        $datagridMapper->add('constituency',
            null,
            [
                'admin_code' => self::class,
            ],
            null,
            [
                'expanded' => false,
                'multiple' => true,
                'query_builder' => $this->getConstituencyQueryBuilder()
            ]
        );
        $this->addDefaultDatagridFilter($datagridMapper, 'administrativeDistrict');
        $this->addDefaultDatagridFilter($datagridMapper, 'communeType');
        $datagridMapper->add('officialCommunityKey');
        $datagridMapper->add('regionalKey');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper->addIdentifier('name');
        $this->addOrganisationOneToOneListFields($listMapper);
        $listMapper
            ->add('constituency', null, [
                'admin_code' => self::class,
                'sortable' => true, // IMPORTANT! make the column sortable
                'sort_field_mapping' => [
                    'fieldName' => 'name'
                ],
                'sort_parent_association_mappings' => [
                    ['fieldName' => 'constituency'],
                ]
            ])
            ->add('officialCommunityKey')
            ->add('communeType', null, [
                'sortable' => true, // IMPORTANT! make the column sortable
                'sort_field_mapping' => [
                    'fieldName' => 'name'
                ],
                'sort_parent_association_mappings' => [
                    ['fieldName' => 'communeType'],
                ]
            ])
            ->add('administrativeDistrict', null, [
                'sortable' => true, // IMPORTANT! make the column sortable
                'sort_field_mapping' => [
                    'fieldName' => 'name'
                ],
                'sort_parent_association_mappings' => [
                    ['fieldName' => 'administrativeDistrict'],
                ]
            ]);
        $this->addDefaultListActions($listMapper);
    }

    /**
     * @inheritdoc
     */
    public function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper->add('name')
            ->add('organisation.zipCode')
            ->add('organisation.town')
            ->add('organisation.url', 'url')
            ->add('officialCommunityKey')
            ->add('regionalKey')
            ->add('administrativeDistrict')
            ->add('constituency', null, [
                'admin_code' => self::class,
            ])
            ->add('communeType')
            ->add('mainEmail');
        $this->addContactsShowFields($showMapper, true, 'organisation.contacts');
        $this->addServiceProvidersShowFields($showMapper);
        $this->addCentralAssociationsShowFields($showMapper);
        $this->addSpecializedProceduresShowFields($showMapper);
        $this->addPortalsShowFields($showMapper);
        $this->addLaboratoriesShowFields($showMapper);
        $showMapper->add('specializedProcedures.manufacturers', null, [
            'label' => 'app.specialized_procedure.entity.manufacturers',
            'admin_code' => ManufacturerAdmin::class,
            'template' => 'General/show-specialized-procedures-manufacturers.html.twig',
        ]);
        $showMapper->add('solutions', null, [
            'label' => 'app.commune_type.entity.online_solutions',
            'admin_code' => SolutionAdmin::class,
            'is_custom_field' => true,
            'is_tab_field' => true,
            'is_custom_rendered' => true,
            'reference_field_list' => ['name', 'url', 'description', 'jurisdictions', 'maturity',],// 'status'
            'show_export' => true,
        ]);
        $showMapper->add('communeType.serviceSystems', null, [
            'label' => 'app.commune_type.entity.service_systems',
            'admin_code' => ServiceSystemAdmin::class,
            'is_custom_field' => true,
            'is_tab_field' => true,
            'is_custom_rendered' => true,
            'reference_field_list' => ['name', 'service_key', 'jurisdictions', 'situation', 'subject', 'priority',],// 'status'
            'show_export' => true,
        ]);
        $showMapper->add('communeType.services', null, [
            'label' => 'app.commune_type.entity.services',
            'admin_code' => ServiceAdmin::class,
            'is_custom_field' => true,
            'is_tab_field' => true,
            'is_custom_rendered' => true,
            'reference_field_list' => ['name', 'service_created_at', 'service_key', 'service_type', 'law_shortcuts', 'relevance1', 'relevance2',],// 'status'
            'show_export' => true,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getExportSettings(): ExportSettings
    {
        $settings = parent::getExportSettings();
        $settings->addExcludeFields(['specializedProcedures.manufacturers', 'communeType.serviceSystems', 'communeType.services']);
        $settings->setAdditionFields(['manufacturers']);
        return $settings;
    }

    public function toString($object)
    {
        return $object instanceof Commune
            ? $object->getName()
            : 'Commune'; // shown in the breadcrumb on the create view
    }

    /**
     * Returns the query builder for the constituencies (sub-set of communes)
     *
     * @return QueryBuilder
     */
    private function getConstituencyQueryBuilder(): QueryBuilder
    {
        /** @var EntityManager $em */
        $em = $this->modelManager->getEntityManager(Commune::class);

        $queryBuilder = $em->createQueryBuilder()
            ->select('c')
            ->from(Commune::class, 'c')
            ->leftJoin( 'c.communeType', 'ct')
            ->where('ct.constituency = 1')
            ->orderBy('c.name', 'ASC');
        return $queryBuilder;
    }
}
