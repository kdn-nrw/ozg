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

namespace App\Entity;

use App\Entity\Base\BaseNamedEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Modellregion
 *
 * @ORM\Entity
 * @ORM\Table(name="ozg_model_region")
 * @ORM\HasLifecycleCallbacks
 */
class ModelRegion extends BaseNamedEntity
{
    use AddressTrait;
    use UrlTrait;

    /**
     * @var ModelRegionProject[]|Collection
     * @ORM\ManyToMany(targetEntity="ModelRegionProject", inversedBy="modelRegions")
     * @ORM\JoinTable(name="ozg_model_region_project_list",
     *     joinColumns={
     *     @ORM\JoinColumn(name="model_region_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="model_region_project_id", referencedColumnName="id")
     *   }
     * )
     */
    private $modelRegionProjects;

    public function __construct()
    {
        $this->modelRegionProjects = new ArrayCollection();
    }

    /**
     * @param ModelRegionProject $modelRegionProject
     * @return self
     */
    public function addModelRegionProject($modelRegionProject): self
    {
        if (!$this->modelRegionProjects->contains($modelRegionProject)) {
            $this->modelRegionProjects->add($modelRegionProject);
            $modelRegionProject->addModelRegion($this);
        }

        return $this;
    }

    /**
     * @param ModelRegionProject $modelRegionProject
     * @return self
     */
    public function removeModelRegionProject($modelRegionProject): self
    {
        if ($this->modelRegionProjects->contains($modelRegionProject)) {
            $this->modelRegionProjects->removeElement($modelRegionProject);
            $modelRegionProject->removeModelRegion($this);
        }

        return $this;
    }

    /**
     * @return ModelRegionProject[]|Collection
     */
    public function getModelRegionProjects()
    {
        return $this->modelRegionProjects;
    }

    /**
     * @param ModelRegionProject[]|Collection $modelRegionProjects
     */
    public function setModelRegionProjects($modelRegionProjects): void
    {
        $this->modelRegionProjects = $modelRegionProjects;
    }
}