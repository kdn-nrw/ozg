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

namespace App\Entity\Onboarding;

use App\Entity\Base\BaseEntity;
use App\Entity\Base\BlameableInterface;
use App\Entity\Base\BlameableTrait;
use App\Entity\Base\HasGroupEmailEntityInterface;
use App\Entity\Base\HideableEntityInterface;
use App\Entity\Base\HideableEntityTrait;
use App\Entity\Configuration\CustomValue;
use App\Entity\Configuration\CustomValuesCollectionAggregateTrait;
use App\Entity\Configuration\HasCustomFieldsEntityInterface;
use App\Entity\MetaData\HasMetaDateEntityInterface;
use App\Entity\StateGroup\Commune;
use App\Entity\StateGroup\ServiceProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


/**
 * Abstract on-boarding entity
 *
 * @ORM\Entity
 * @ORM\Table(name="ozg_onboarding")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="record_type", type="string")
 */
abstract class AbstractOnboardingEntity extends BaseEntity implements
    BlameableInterface,
    HideableEntityInterface,
    HasCustomFieldsEntityInterface,
    HasMetaDateEntityInterface,
    HasGroupEmailEntityInterface
{
    use BlameableTrait;
    use HideableEntityTrait;
    use CustomValuesCollectionAggregateTrait;

    /**
     * @var Commune
     * @ORM\ManyToOne(targetEntity="App\Entity\StateGroup\Commune", cascade={"persist"})
     * @ORM\JoinColumn(name="commune_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $commune;

    /**
     * @var string|null
     */
    protected $communeName;

    /**
     * @var string|null
     */
    protected $officialCommuneKey;

    /**
     * @var ServiceProvider|null
     * @ORM\OneToOne(targetEntity="App\Entity\StateGroup\ServiceProvider", mappedBy="organisation", cascade={"all"})
     * @ORM\JoinColumn(name="service_provider_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $serviceProvider;

    /**
     * @ORM\Column(type="string", name="group_email", length=255, nullable=true)
     * @var string|null
     */
    protected $groupEmail;

    public const STATUS_NEW = 0;
    public const STATUS_INCOMPLETE = 2;
    public const STATUS_COMPLETE = 9;

    public static $statusChoices = [
        AbstractOnboardingEntity::STATUS_NEW => 'app.commune_info.entity.status_choices.new',
        AbstractOnboardingEntity::STATUS_INCOMPLETE => 'app.commune_info.entity.status_choices.incomplete',
        AbstractOnboardingEntity::STATUS_COMPLETE => 'app.commune_info.entity.status_choices.complete',
    ];

    /**
     * @ORM\Column(type="integer", name="status")
     * @var int
     */
    protected $status = self::STATUS_NEW;

    /**
     * Completion rate
     *
     * @var int
     *
     * @ORM\Column(name="completion_rate", type="integer", nullable=true)
     */
    protected $completionRate = 0;

    /**
     * Description
     *
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description = '';

    /**
     * Custom values for this entity
     *
     * @var ArrayCollection|OnboardingCustomValue[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Onboarding\OnboardingCustomValue", mappedBy="onboarding")
     */
    protected $customValues;

    /**
     * @ORM\Column(name="message_count", type="integer", nullable=true)
     * @var int|null
     */
    protected $messageCount = 0;

    public function __construct(Commune $commune)
    {
        $this->commune = $commune;
        $this->communeName = $commune->getName();
        $this->officialCommuneKey = $commune->getOfficialCommunityKey();
        $this->customValues = new ArrayCollection();
    }

    /**
     * @return Commune
     */
    public function getCommune(): Commune
    {
        return $this->commune;
    }

    /**
     * @param Commune $commune
     */
    public function setCommune(Commune $commune): void
    {
        $this->commune = $commune;
    }

    /**
     * @return string|null
     */
    public function getCommuneName(): ?string
    {
        if (!$this->communeName) {
            return $this->getCommune()->getName();
        }
        return $this->communeName;
    }

    /**
     * @param string|null $communeName
     */
    public function setCommuneName(?string $communeName): void
    {
        $this->communeName = $communeName;
    }

    /**
     * @return string|null
     */
    public function getOfficialCommuneKey(): ?string
    {
        if (!$this->officialCommuneKey) {
            return $this->getCommune()->getOfficialCommunityKey();
        }
        return $this->officialCommuneKey;
    }

    /**
     * @param string|null $officialCommuneKey
     */
    public function setOfficialCommuneKey(?string $officialCommuneKey): void
    {
        $this->officialCommuneKey = $officialCommuneKey;
    }

    /**
     * @return ServiceProvider|null
     */
    public function getServiceProvider(): ?ServiceProvider
    {
        return $this->serviceProvider;
    }

    /**
     * @param ServiceProvider|null $serviceProvider
     */
    public function setServiceProvider(?ServiceProvider $serviceProvider): void
    {
        $this->serviceProvider = $serviceProvider;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status ?? self::STATUS_NEW;
    }

    /**
     * @param int|null $status
     */
    public function setStatus(?int $status): void
    {
        $this->status = (int)$status;
    }

    /**
     * @param CustomValue $customValue
     * @return void
     */
    public function addCustomValue(CustomValue $customValue): void
    {
        $customValues = $this->getCustomValues();
        if (!$customValues->contains($customValue)) {
            if ($customValue instanceof OnboardingCustomValue) {
                $customValue->setOnboarding($this);
            }
            $customValues->add($customValue);
        }
    }

    /**
     * @return int
     */
    public function getCompletionRate(): int
    {
        return (int) $this->completionRate;
    }

    /**
     * @param int $completionRate
     */
    public function setCompletionRate(int $completionRate): void
    {
        $this->completionRate = (int) max(0, min($completionRate, 100));
        if ($this->completionRate === 100) {
            $this->setStatus(self::STATUS_COMPLETE);
        } elseif ($this->completionRate > 0) {
            $this->setStatus(self::STATUS_INCOMPLETE);
        } else {
            $this->setStatus(self::STATUS_NEW);
        }
    }

    /**
     * @return string
     */
    public function getStatusLabelKey(): string
    {
        $status = $this->getStatus();
        if (array_key_exists($status, self::$statusChoices)) {
            return self::$statusChoices[$status];
        }
        return self::$statusChoices[self::STATUS_NEW];
    }

    /**
     * Get name
     *
     * @return string
     */
    public function __toString(): string
    {
        $commune = $this->getCommune();
        $serviceProvider = $this->getServiceProvider();
        $string = $commune . '';
        if (null !== $serviceProvider) {
            if (empty($string)) {
                $string = (string) $serviceProvider;
            } else {
                $string .= ' / ' . $serviceProvider;
            }
        }
        if (empty($string)) {
            return 'n/a';
        }
        return $string;
    }

    /**
     * Returns true, if the given property is filled
     * @param string $property
     * @return bool
     */
    protected function isPropertyCompleted(string $property): bool
    {
        $getter = 'get' . ucfirst($property);
        if (!method_exists($this, $getter)) {
            $getter = 'is' . ucfirst($property);
        }
        $value = $this->$getter();
        if ($value instanceof Collection) {
            $itemCount = count($value);
            $hasIncompleteSubItems = false;
            foreach ($value as $item) {
                if (method_exists($item, 'calculateCompletionRate')) {
                    if ($item->calculateCompletionRate() < 100) {
                        $hasIncompleteSubItems = true;
                        break;
                    }
                }
            }
            $isCompleted = $itemCount > 0 && !$hasIncompleteSubItems;
        } elseif ($value instanceof Contact) {
            $isCompleted = $value->calculateCompletionRate() === 100;
        } else {
            $isCompleted = !empty($value);
        }
        return $isCompleted;
    }

    /**
     * Calculates the completion rate for this entity
     *
     * @return int
     */
    public function calculateCompletionRate(): int
    {
        $completionRate = 0;
        $calcProperties = $this->getRequiredPropertiesForCompletion();
        $ratePerProperty = ceil(100 / count($calcProperties));
        foreach ($calcProperties as $property) {
            if (is_array($property)) {
                $propertyIsFilled = false;
                foreach ($property as $orProperty) {
                    $propertyIsFilled = $this->isPropertyCompleted($orProperty);
                    if ($propertyIsFilled) {
                        break;
                    }
                }
            } else {
                $propertyIsFilled = $this->isPropertyCompleted($property);
            }
            if ($propertyIsFilled) {
                $completionRate += $ratePerProperty;
            }
        }
        $newCompletionRate = min(100, $completionRate);
        $this->setCompletionRate($newCompletionRate);
        return $newCompletionRate;
    }

    /**
     * @return int
     */
    public function getMessageCount(): int
    {
        return (int) $this->messageCount;
    }

    /**
     * @param int|null $messageCount
     */
    public function setMessageCount(?int $messageCount): void
    {
        $this->messageCount = (int) $messageCount;
    }

    /**
     * @param bool $fallbackToDefault
     * @return string|null
     */
    public function getGroupEmail($fallbackToDefault = false): ?string
    {
        if ($fallbackToDefault && null === $this->groupEmail && $mainCommuneEmail = $this->commune->getMainEmail()) {
            $mailParts = explode('@', $mainCommuneEmail);
            return 'epaybl@' . $mailParts[1];
        }
        return $this->groupEmail;
    }

    /**
     * @param string|null $groupEmail
     */
    public function setGroupEmail(?string $groupEmail): void
    {
        $this->groupEmail = $groupEmail;
    }

    /**
     * Returns the properties that are relevant for the completion rate calculation
     *
     * @return array
     */
    abstract protected function getRequiredPropertiesForCompletion(): array;
}
