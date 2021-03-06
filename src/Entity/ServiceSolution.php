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

namespace App\Entity;

use App\Entity\Base\BaseEntity;
use App\Entity\Base\HideableEntityInterface;
use App\Entity\Base\HideableEntityTrait;
use Doctrine\ORM\Mapping as ORM;


/**
 * Class service solution
 *
 * @ORM\Entity
 * @ORM\Table(name="ozg_service_solution")
 * @ORM\HasLifecycleCallbacks
 */
class ServiceSolution extends BaseEntity implements HideableEntityInterface
{
    use HideableEntityTrait;

    /**
     * @var Service|null
     * @ORM\ManyToOne(targetEntity="App\Entity\Service", inversedBy="serviceSolutions", cascade={"persist"})
     * @ORM\JoinColumn(name="service_id", referencedColumnName="id")
     */
    private $service;

    /**
     * @var Solution|null
     * @ORM\ManyToOne(targetEntity="App\Entity\Solution", inversedBy="serviceSolutions", cascade={"persist"})
     * @ORM\JoinColumn(name="solution_id", referencedColumnName="id")
     */
    private $solution;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description = '';

    /**
     * Status
     * @var Status|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Status")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $status;

    /**
     * @var Maturity|null
     *
     * @ORM\ManyToOne(targetEntity="Maturity", cascade={"persist"})
     * @ORM\JoinColumn(name="maturity_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $maturity;

    /**
     * @return Service|null
     */
    public function getService(): ?Service
    {
        return $this->service;
    }

    /**
     * @param Service $service
     */
    public function setService($service): void
    {
        $this->service = $service;
    }

    /**
     * @return Solution|null
     */
    public function getSolution(): ?Solution
    {
        return $this->solution;
    }

    /**
     * @param Solution $solution
     */
    public function setSolution($solution): void
    {
        $this->solution = $solution;
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
     * @return Status|null
     */
    public function getStatus(): ?Status
    {
        return $this->status;
    }

    /**
     * @param Status|null $status
     */
    public function setStatus(?Status $status): void
    {
        $this->status = $status;
    }

    /**
     * @return Maturity|null
     */
    public function getMaturity(): ?Maturity
    {
        return $this->maturity;
    }

    /**
     * @param Maturity|null $maturity
     */
    public function setMaturity($maturity): void
    {
        $this->maturity = $maturity;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function __toString(): string
    {
        $service = $this->getService();
        if (null === $service) {
            $name = (string) $this->getId();
        } else {
            $name = '';
            $serviceSystem = $service->getServiceSystem();
            if (null !== $serviceSystem) {
                $name = 'OZG-Leistung ' . $serviceSystem->getName() .': Leika-Leistung ';
            }
            $name .= $service->getName();
        }
        if (null !== $maturity = $this->getMaturity()) {
            $name .= ' (Reifegrad: '.$maturity->getName().')';
        }
        return $name;
    }

}
