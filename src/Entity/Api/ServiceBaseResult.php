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

namespace App\Entity\Api;

use App\Entity\Base\BaseNamedEntity;
use App\Entity\FederalInformationManagementType;
use App\Entity\ImportEntityInterface;
use App\Entity\ImportTrait;
use App\Entity\Service;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class API consumer
 *
 * @ORM\Entity
 * @ORM\Table(name="ozg_api_service_base_result")
 */
class ServiceBaseResult extends BaseNamedEntity implements ImportEntityInterface
{
    use ImportTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Service", cascade={"persist"})
     * @ORM\JoinColumn(name="service_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $service;

    /**
     * @var FederalInformationManagementType
     * @ORM\OneToOne(targetEntity="App\Entity\FederalInformationManagementType", inversedBy="serviceBaseResult", cascade={"all"})
     * @ORM\JoinColumn(name="fim_type_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $fimType;

    /**
     * Service key
     *
     * @var string|null
     *
     * @ORM\Column(name="service_key", type="text", nullable=true)
     */
    protected $serviceKey;

    /**
     * Group
     *
     * @var string|null
     *
     * @ORM\Column(name="service_group", type="text", nullable=true)
     */
    protected $group;

    /**
     * Call sign
     *
     * @var string|null
     *
     * @ORM\Column(name="call_sign", type="text", nullable=true)
     */
    protected $callSign;

    /**
     * Performance
     *
     * @var string|null
     *
     * @ORM\Column(name="performance", type="text", nullable=true)
     */
    protected $performance;

    /**
     *
     * @var string|null
     *
     * @ORM\Column(name="performance_detail", type="text", nullable=true)
     */
    protected $performanceDetail;

    /**
     *
     * @var string|null
     *
     * @ORM\Column(name="name2", type="text", nullable=true)
     */
    protected $name2;

    /**
     *
     * @var string|null
     *
     * @ORM\Column(name="type", type="text", nullable=true)
     */
    protected $type;

    /**
     *
     * @var string|null
     *
     * @ORM\Column(name="service_type", type="text", nullable=true)
     */
    protected $serviceType;

    /**
     *
     * @var string|null
     *
     * @ORM\Column(name="date", type="text", nullable=true)
     */
    protected $date;

    /**
     * @var array|null
     *
     * @ORM\Column(name="special_features", type="json", nullable=true)
     */
    protected $specialFeatures = [];

    /**
     * @var array|null
     * @ORM\Column(name="synonyms", type="json", nullable=true)
     */
    protected $synonyms = [];

    /**
     * @var string|null
     *
     * @ORM\Column(name="short_text", type="text", nullable=true)
     */
    protected $shortText;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @var string|null
     *
     * @ORM\Column(name="legal_basis", type="text", nullable=true)
     */
    protected $legalBasis;

    /**
     * @var array|null
     *
     * @ORM\Column(name="legal_basis_uris", type="json", nullable=true)
     */
    protected $legalBasisUris;

    /**
     * @var string|null
     *
     * @ORM\Column(name="required_documents", type="text", nullable=true)
     */
    protected $requiredDocuments;

    /**
     * @var string|null
     *
     * @ORM\Column(name="requirements", type="text", nullable=true)
     */
    protected $requirements;

    /**
     * @var string|null
     *
     * @ORM\Column(name="costs", type="text", nullable=true)
     */
    protected $costs;

    /**
     * @var string|null
     *
     * @ORM\Column(name="processing_time", type="text", nullable=true)
     */
    protected $processingTime;

    /**
     * @var string|null
     *
     * @ORM\Column(name="process_flow", type="text", nullable=true)
     */
    protected $processFlow;

    /**
     * @var string|null
     *
     * @ORM\Column(name="deadlines", type="text", nullable=true)
     */
    protected $deadlines;

    /**
     * @var string|null
     *
     * @ORM\Column(name="forms", type="text", nullable=true)
     */
    protected $forms;

    /**
     * @var string|null
     *
     * @ORM\Column(name="further_information", type="text", nullable=true)
     */
    protected $furtherInformation;

    /**
     * @var string|null
     *
     * @ORM\Column(name="url_online_service", type="string", nullable=true)
     */
    protected $urlOnlineService;

    /**
     * @var string|null
     *
     * @ORM\Column(name="teaser", type="text", nullable=true)
     */
    protected $teaser;

    /**
     * @var string|null
     *
     * @ORM\Column(name="point_of_contact", type="string", nullable=true)
     */
    protected $pointOfContact;

    /**
     * @var string|null
     *
     * @ORM\Column(name="technically_approved_at", type="string", nullable=true)
     */
    protected $technicallyApprovedAt;

    /**
     * @var string|null
     *
     * @ORM\Column(name="technically_approved_by", type="string", nullable=true)
     */
    protected $technicallyApprovedBy;

    /**
     * @var string|null
     *
     * @ORM\Column(name="hints", type="text", nullable=true)
     */
    protected $hints;

    /**
     * @return Service|null
     */
    public function getService(): ?Service
    {
        return $this->service;
    }

    /**
     * @param Service|null $service
     */
    public function setService(?Service $service): void
    {
        $this->service = $service;
    }

    /**
     * @return string|null
     */
    public function getServiceKey(): ?string
    {
        return $this->serviceKey;
    }

    /**
     * @param string|null $serviceKey
     */
    public function setServiceKey(?string $serviceKey): void
    {
        $this->serviceKey = $serviceKey;
    }

    /**
     * @return string|null
     */
    public function getGroup(): ?string
    {
        return $this->group;
    }

    /**
     * @param string|null $group
     */
    public function setGroup(?string $group): void
    {
        $this->group = $group;
    }

    /**
     * @return string|null
     */
    public function getCallSign(): ?string
    {
        return $this->callSign;
    }

    /**
     * @param string|null $callSign
     */
    public function setCallSign(?string $callSign): void
    {
        $this->callSign = $callSign;
    }

    /**
     * @return string|null
     */
    public function getPerformance(): ?string
    {
        return $this->performance;
    }

    /**
     * @param string|null $performance
     */
    public function setPerformance(?string $performance): void
    {
        $this->performance = $performance;
    }

    /**
     * @return string|null
     */
    public function getPerformanceDetail(): ?string
    {
        return $this->performanceDetail;
    }

    /**
     * @param string|null $performanceDetail
     */
    public function setPerformanceDetail(?string $performanceDetail): void
    {
        $this->performanceDetail = $performanceDetail;
    }

    /**
     * @return string|null
     */
    public function getName2(): ?string
    {
        return $this->name2;
    }

    /**
     * @param string|null $name2
     */
    public function setName2(?string $name2): void
    {
        $this->name2 = $name2;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     */
    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string|null
     */
    public function getServiceType(): ?string
    {
        return $this->serviceType;
    }

    /**
     * @param string|null $serviceType
     */
    public function setServiceType(?string $serviceType): void
    {
        $this->serviceType = $serviceType;
    }

    /**
     * @return string|null
     */
    public function getDate(): ?string
    {
        return $this->date;
    }

    /**
     * @param string|null $date
     */
    public function setDate(?string $date): void
    {
        $this->date = $date;
    }

    /**
     * @return array|null
     */
    public function getSpecialFeatures(): ?array
    {
        return $this->specialFeatures;
    }

    /**
     * @param array|null $specialFeatures
     */
    public function setSpecialFeatures(?array $specialFeatures): void
    {
        $this->specialFeatures = $specialFeatures;
    }

    /**
     * @return array|null
     */
    public function getSynonyms(): ?array
    {
        return $this->synonyms;
    }

    /**
     * @param array|null $synonyms
     */
    public function setSynonyms(?array $synonyms): void
    {
        $this->synonyms = $synonyms;
    }

    /**
     * @return string|null
     */
    public function getShortText(): ?string
    {
        return $this->shortText;
    }

    /**
     * @param string|null $shortText
     */
    public function setShortText(?string $shortText): void
    {
        $this->shortText = $shortText;
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
     * @return string|null
     */
    public function getLegalBasis(): ?string
    {
        return $this->legalBasis;
    }

    /**
     * @param string|null $legalBasis
     */
    public function setLegalBasis(?string $legalBasis): void
    {
        $this->legalBasis = $legalBasis;
    }

    /**
     * @return array|null
     */
    public function getLegalBasisUris(): ?array
    {
        return $this->legalBasisUris;
    }

    /**
     * @param array|null $legalBasisUris
     */
    public function setLegalBasisUris(?array $legalBasisUris): void
    {
        $this->legalBasisUris = $legalBasisUris;
    }

    /**
     * @return string|null
     */
    public function getRequiredDocuments(): ?string
    {
        return $this->requiredDocuments;
    }

    /**
     * @param string|null $requiredDocuments
     */
    public function setRequiredDocuments(?string $requiredDocuments): void
    {
        $this->requiredDocuments = $requiredDocuments;
    }

    /**
     * @return string|null
     */
    public function getRequirements(): ?string
    {
        return $this->requirements;
    }

    /**
     * @param string|null $requirements
     */
    public function setRequirements(?string $requirements): void
    {
        $this->requirements = $requirements;
    }

    /**
     * @return string|null
     */
    public function getCosts(): ?string
    {
        return $this->costs;
    }

    /**
     * @param string|null $costs
     */
    public function setCosts(?string $costs): void
    {
        $this->costs = $costs;
    }

    /**
     * @return string|null
     */
    public function getProcessingTime(): ?string
    {
        return $this->processingTime;
    }

    /**
     * @param string|null $processingTime
     */
    public function setProcessingTime(?string $processingTime): void
    {
        $this->processingTime = $processingTime;
    }

    /**
     * @return string|null
     */
    public function getProcessFlow(): ?string
    {
        return $this->processFlow;
    }

    /**
     * @param string|null $processFlow
     */
    public function setProcessFlow(?string $processFlow): void
    {
        $this->processFlow = $processFlow;
    }

    /**
     * @return string|null
     */
    public function getDeadlines(): ?string
    {
        return $this->deadlines;
    }

    /**
     * @param string|null $deadlines
     */
    public function setDeadlines(?string $deadlines): void
    {
        $this->deadlines = $deadlines;
    }

    /**
     * @return string|null
     */
    public function getForms(): ?string
    {
        return $this->forms;
    }

    /**
     * @param string|null $forms
     */
    public function setForms(?string $forms): void
    {
        $this->forms = $forms;
    }

    /**
     * @return string|null
     */
    public function getFurtherInformation(): ?string
    {
        return $this->furtherInformation;
    }

    /**
     * @param string|null $furtherInformation
     */
    public function setFurtherInformation(?string $furtherInformation): void
    {
        $this->furtherInformation = $furtherInformation;
    }

    /**
     * @return string|null
     */
    public function getUrlOnlineService(): ?string
    {
        return $this->urlOnlineService;
    }

    /**
     * @param string|null $urlOnlineService
     */
    public function setUrlOnlineService(?string $urlOnlineService): void
    {
        $this->urlOnlineService = $urlOnlineService;
    }

    /**
     * @return string|null
     */
    public function getTeaser(): ?string
    {
        return $this->teaser;
    }

    /**
     * @param string|null $teaser
     */
    public function setTeaser(?string $teaser): void
    {
        $this->teaser = $teaser;
    }

    /**
     * @return string|null
     */
    public function getPointOfContact(): ?string
    {
        return $this->pointOfContact;
    }

    /**
     * @param string|null $pointOfContact
     */
    public function setPointOfContact(?string $pointOfContact): void
    {
        $this->pointOfContact = $pointOfContact;
    }

    /**
     * @return string|null
     */
    public function getTechnicallyApprovedAt(): ?string
    {
        return $this->technicallyApprovedAt;
    }

    /**
     * @param string|null $technicallyApprovedAt
     */
    public function setTechnicallyApprovedAt(?string $technicallyApprovedAt): void
    {
        $this->technicallyApprovedAt = $technicallyApprovedAt;
    }

    /**
     * @return string|null
     */
    public function getTechnicallyApprovedBy(): ?string
    {
        return $this->technicallyApprovedBy;
    }

    /**
     * @param string|null $technicallyApprovedBy
     */
    public function setTechnicallyApprovedBy(?string $technicallyApprovedBy): void
    {
        $this->technicallyApprovedBy = $technicallyApprovedBy;
    }

    /**
     * @return string|null
     */
    public function getHints(): ?string
    {
        return $this->hints;
    }

    /**
     * @param string|null $hints
     */
    public function setHints(?string $hints): void
    {
        $this->hints = $hints;
    }

    /**
     * @return FederalInformationManagementType
     */
    public function getFimType(): FederalInformationManagementType
    {
        return $this->fimType;
    }

    /**
     * @param FederalInformationManagementType $fimType
     */
    public function setFimType(FederalInformationManagementType $fimType): void
    {
        $this->fimType = $fimType;
    }

    public function __toString(): string
    {
        return $this->getName() . ' - ' . $this->getServiceKey();
    }
}