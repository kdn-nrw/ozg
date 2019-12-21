<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;
use App\Entity\Base\HideableEntityTrait;
use DateTime;
use Doctrine\ORM\Mapping as ORM;


/**
 * Class mailing contact
 *
 * @ORM\Entity
 * @ORM\Table(name="ozg_mailing_contact")
 */
class MailingContact extends BaseEntity
{
    use HideableEntityTrait;

    const SEND_STATUS_FAILED = 3;
    const SEND_STATUS_DISABLED = 5;

    /**
     * @var Mailing
     * @ORM\ManyToOne(targetEntity="Mailing", inversedBy="mailingContacts", cascade={"persist"})
     * @ORM\JoinColumn(name="mailing_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $mailing;

    /**
     * @var Contact
     * @ORM\ManyToOne(targetEntity="Contact", cascade={"persist"})
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $contact;

    /**
     * @ORM\Column(type="integer", name="send_status")
     * @var int
     */
    private $sendStatus = Mailing::STATUS_NEW;

    /**
     * @var null|DateTime
     *
     * @ORM\Column(nullable=true, type="datetime", name="sent_at")
     */
    protected $sentAt;

    /**
     * @return Mailing
     */
    public function getMailing()
    {
        return $this->mailing;
    }

    /**
     * @param Mailing $mailing
     */
    public function setMailing($mailing): void
    {
        $this->mailing = $mailing;
    }

    /**
     * @return Contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param Contact $contact
     */
    public function setContact($contact): void
    {
        $this->contact = $contact;
    }

    /**
     * @return int
     */
    public function getSendStatus(): int
    {
        return (int)$this->sendStatus;
    }

    /**
     * @param int $sendStatus
     */
    public function setSendStatus(?int $sendStatus): void
    {
        $this->sendStatus = (int)$sendStatus;
    }

    /**
     * @return DateTime|null
     */
    public function getSentAt(): ?DateTime
    {
        return $this->sentAt;
    }

    /**
     * @param DateTime|null $sentAt
     */
    public function setSentAt(?DateTime $sentAt): void
    {
        $this->sentAt = $sentAt;
    }

    /**
     * Mailing contact to string
     *
     * @return string
     */
    public function __toString(): string
    {
        $contact = $this->getContact();
        if (null !== $contact) {
            return $contact->getDisplayName();
        }
        return 'n.a.';
    }

}
