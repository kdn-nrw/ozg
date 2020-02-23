<?php

namespace App\Admin;

use App\Admin\Traits\CategoryTrait;
use App\Admin\Traits\CommuneTrait;
use App\Admin\Traits\MinistryStateTrait;
use App\Admin\Traits\OrganisationTrait;
use App\Entity\Contact;
use App\Entity\Mailing;
use App\Entity\MailingContact;
use App\Validator\Constraints\MailingSenderEmail;
use DateTime;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ChoiceFieldMaskType;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\Form\Type\DateTimePickerType;
use Sonata\Form\Validator\ErrorElement;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class MailingAdmin extends AbstractAppAdmin
{
    use CategoryTrait;
    use OrganisationTrait;

    protected function configureSideMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        if (!$childAdmin && !in_array($action, ['edit', 'show'])) {
            return;
        }

        $admin = $this->isChild() ? $this->getParent() : $this;
        if (null !== $admin) {
            $id = $admin->getRequest()->get('id');

            $menu->addChild('app.mailing.actions.show', [
                'uri' => $admin->generateUrl('show', ['id' => $id])
            ]);

            if ($this->isGranted('EDIT')) {
                $menu->addChild('app.mailing.actions.edit', [
                    'uri' => $admin->generateUrl('edit', ['id' => $id])
                ]);
            }

            if ($this->isGranted('LIST')) {
                $menu->addChild('app.mailing.actions.contact_list', [
                    'uri' => $admin->getChild(MailingContactAdmin::class)->generateUrl('list', ['id' => $id])
                ]);
            }
        }
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $now = new DateTime();
        $formMapper
            ->with('app.mailing.groups.default')
                ->add('subject', TextType::class)
                ->add('greetingType', ChoiceFieldMaskType::class, [
                    'choices' => [
                        'app.mailing.entity.greeting_type_choices.none' => Mailing::GREETING_TYPE_NONE,
                        'app.mailing.entity.greeting_type_choices.prepend' => Mailing::GREETING_TYPE_PREPEND,
                    ],
                    'map' => [
                        Mailing::GREETING_TYPE_NONE => ['textPlain'],
                        Mailing::GREETING_TYPE_PREPEND => ['greeting', 'textPlain'],
                    ],
                    'required' => true,
                ])
                ->add('greeting', TextType::class, [
                    'label' => false,
                    'mapped' => false,
                    'disabled' => true,
                    'data' => 'Sehr geehrte(r) Frau/Herr Mustermann,',
                ])
                ->add('textPlain', TextareaType::class, [
                    'required' => true,
                ]);
        $formMapper->end();
        $formMapper->with('app.mailing.groups.options', ['class' => 'col-md-6']);
        $formMapper
            ->add('senderName', TextType::class, [
                'required' => true,
                'empty_data' => 'KDN OZG Plattform',
            ])
            ->add('senderEmail', EmailType::class, [
                'required' => true,
                'empty_data' => 'geschaeftsstelle@kdn.de',
                'help' => 'Bitte verwenden Sie nur Adressen ...@kdn.de als Absender. Ansonsten landen die E-Mails sehr wahrscheinlich beim Empfänger im Spam-Ordner.',
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'app.mailing.entity.status_choices.new' => Mailing::STATUS_NEW,
                    'app.mailing.entity.status_choices.prepared' => Mailing::STATUS_PREPARED,
                    'app.mailing.entity.status_choices.active' => Mailing::STATUS_ACTIVE,
                    'app.mailing.entity.status_choices.finished' => Mailing::STATUS_FINISHED,
                    'app.mailing.entity.status_choices.cancelled' => Mailing::STATUS_CANCELLED,
                ],
                'required' => true,
            ])
            ->add('startAt', DateTimePickerType::class, [
                'years' => range((int)$now->format('Y'), (int)$now->format('Y') + 2),
                //'dp_min_date' => '1-1-' . $now->format('Y'),
                //'dp_max_date' => $now->format('c'),
                'dp_use_seconds' => false,
                'dp_use_current' => false,
                'dp_minute_stepping' => 5,
                'required' => false,
            ]);
        $formMapper->end();
        $formMapper->with('app.mailing.groups.recipients', ['class' => 'col-md-6']);
        $this->addOrganisationsFormFields($formMapper);
        $this->addCategoriesFormFields($formMapper);
        $formMapper->add('excludeContacts', ModelAutocompleteType::class, [
            'property' => ['firstName', 'lastName', 'email', 'organisation'],
            'placeholder' => '',
            'required' => false,
            'multiple' => true,
            //'choice_translation_domain' => false,
        ]);
        $formMapper->end();
    }

    public function validate(ErrorElement $errorElement, $object)
    {
        $errorElement
            ->with('senderEmail')
                ->addConstraint(new MailingSenderEmail())
            ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('subject');
        $this->addCategoriesDatagridFilters($datagridMapper);
        $this->addOrganisationsDatagridFilters($datagridMapper);
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('createdAt')
            ->add('status', 'choice', [
                'editable' => true,
                'choices' => [
                    Mailing::STATUS_NEW => 'app.mailing.entity.status_choices.new',
                    Mailing::STATUS_PREPARED => 'app.mailing.entity.status_choices.prepared',
                    Mailing::STATUS_ACTIVE => 'app.mailing.entity.status_choices.active',
                    Mailing::STATUS_FINISHED => 'app.mailing.entity.status_choices.finished',
                    Mailing::STATUS_CANCELLED => 'app.mailing.entity.status_choices.cancelled',
                ],
                'catalogue' => 'messages',
            ])
            ->add('subject')
            ->add('recipientCount')
            ->add('sendStartAt')
            ->add('sendEndAt')
            ->add('sentCount');
        $this->addDefaultListActions($listMapper);
    }

    /**
     * @inheritdoc
     */
    public function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('createdAt')
            ->add('status', 'choice', [
                'editable' => true,
                'choices' => [
                    Mailing::STATUS_NEW => 'app.mailing.entity.status_choices.new',
                    Mailing::STATUS_PREPARED => 'app.mailing.entity.status_choices.prepared',
                    Mailing::STATUS_ACTIVE => 'app.mailing.entity.status_choices.active',
                    Mailing::STATUS_FINISHED => 'app.mailing.entity.status_choices.finished',
                    Mailing::STATUS_CANCELLED => 'app.mailing.entity.status_choices.cancelled',
                ],
                'catalogue' => 'messages',
            ])
            ->add('subject')
            ->add('textPlain')
            ->add('startAt')
            ->add('sendStartAt')
            ->add('sendEndAt')
            ->add('sentCount')
            ->add('recipientCount')
            ->add('mailingContacts', null, [
                'template' => 'Mailing/show-mailing-contacts.html.twig',
            ])
            ->add('excludeContacts');
        $this->addOrganisationsShowFields($showMapper);
        $this->addCategoriesShowFields($showMapper);
    }

    public function preUpdate($object)
    {
        /** @var Mailing $object */
        if (!in_array($object->getStatus(), [
            Mailing::STATUS_FINISHED,
            Mailing::STATUS_CANCELLED,
        ], false)) {
            $this->updateMailingContacts($object);
        }
        $object->updateSentCount();
    }

    public function prePersist($object)
    {
        /** @var Mailing $object */
        if (!in_array($object->getStatus(), [
            Mailing::STATUS_FINISHED,
            Mailing::STATUS_CANCELLED,
        ], false)) {
            $this->updateMailingContacts($object);
            //$this->updateSentCount();
        }
    }

    /**
     * Update the mailing contact list; add all contacts of selected categories, state ministries, service providers
     * and communes
     * @param Mailing $object
     */
    public function updateMailingContacts(Mailing $object): void
    {
        $categories = $object->getCategories();
        foreach ($categories as $child) {
            $contacts = $child->getContacts();
            foreach ($contacts as $contact) {
                $this->addContactToMailingOnce($object, $contact);
            }
        }
        $organisations = $object->getOrganisations();
        foreach ($organisations as $child) {
            $contacts = $child->getContacts();
            foreach ($contacts as $contact) {
                $this->addContactToMailingOnce($object, $contact);
            }
        }
        $mailingContacts = $object->getMailingContacts();
        foreach ($mailingContacts as $child) {
            if ($object->contactIsBlacklisted($child->getContact())) {
                try {
                    $this->modelManager->delete($child);
                } catch (ModelManagerException $e) {
                    unset($e);
                }
                $object->removeMailingContact($child);
            }
        }
        $object->setRecipientCount(count($mailingContacts));
    }

    /**
     * Add the given contact to the mailing; check if contact already exists before adding
     * Contacts set in excludeContacts will be skipped
     *
     * @param Mailing $object
     * @param Contact $contact
     */
    private function addContactToMailingOnce(Mailing $object, Contact $contact): void
    {

        if (!$object->contactIsBlacklisted($contact)) {
            $contactIds = $object->getContactIds();
            if (!in_array($contact->getId(), $contactIds, false)) {
                $mailingContact = new MailingContact();
                $mailingContact->setSendStatus(Mailing::STATUS_NEW);
                if ($object->getStatus() === Mailing::STATUS_PREPARED) {
                    $mailingContact->setSendStatus(Mailing::STATUS_PREPARED);
                }
                $mailingContact->setMailing($object);
                $mailingContact->setContact($contact);
                try {
                    $this->modelManager->create($mailingContact);
                } catch (ModelManagerException $e) {
                    unset($e);
                }
                $object->addMailingContact($mailingContact);
            }
        }
    }
}
