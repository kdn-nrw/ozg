<?php

namespace App\Entity;

use App\Entity\Base\BaseNamedEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


/**
 * Class payment type
 *
 * @ORM\Entity
 * @ORM\Table(name="ozg_payment_type")
 * @ORM\HasLifecycleCallbacks
 */
class PaymentType extends BaseNamedEntity
{
    use UrlTrait;

    /**
     * @var Solution[]|Collection
     * @ORM\ManyToMany(targetEntity="Solution", inversedBy="paymentTypes")
     * @ORM\JoinTable(name="ozg_payment_types_solutions",
     *     joinColumns={
     *     @ORM\JoinColumn(name="payment_type_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="solution_id", referencedColumnName="id")
     *   }
     * )
     */
    private $solutions;

    public function __construct()
    {
        $this->solutions = new ArrayCollection();
    }

    /**
     * @param Solution $solution
     * @return self
     */
    public function addSolution($solution)
    {
        if (!$this->solutions->contains($solution)) {
            $this->solutions->add($solution);
            $solution->addPaymentType($this);
        }

        return $this;
    }

    /**
     * @param Solution $solution
     * @return self
     */
    public function removeSolution($solution)
    {
        if ($this->solutions->contains($solution)) {
            $this->solutions->removeElement($solution);
            $solution->removePaymentType($this);
        }

        return $this;
    }

    /**
     * @return Solution[]|Collection
     */
    public function getSolutions()
    {
        return $this->solutions;
    }

    /**
     * @param Solution[]|Collection $solutions
     */
    public function setSolutions($solutions): void
    {
        $this->solutions = $solutions;
    }
}