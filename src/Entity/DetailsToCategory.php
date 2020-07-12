<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DetailsToCategoryRepository")
 */
class DetailsToCategory
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $regex;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="detailsToCategories")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

    /**
     * @ORM\OneToMany(targetEntity="Criteria", mappedBy="detailsToCategory", orphanRemoval=true, cascade={"persist"})
     */
    private $criteria;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $label;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $debit;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $credit;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $date;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\ManyToOne(targetEntity=Budget::class, inversedBy="detailsToCategories")
     */
    private $budget;

    public function __construct()
    {
        $this->criteria = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getRegex();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRegex(): ?string
    {
        return $this->regex;
    }

    public function setRegex(string $regex): self
    {
        $this->regex = $regex;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection|Criteria[]
     */
    public function getCriteria(): Collection
    {
        return $this->criteria;
    }

    public function addCriterium(Criteria $criteria): self
    {
        if (!$this->criteria->contains($criteria)) {
            $this->criteria[] = $criteria;
            $criteria->setDetailsToCategory($this);
        }

        return $this;
    }

    public function removeCriterium(Criteria $criteria): self
    {
        if ($this->criteria->contains($criteria)) {
            $this->criteria->removeElement($criteria);
            // set the owning side to null (unless already changed)
            if ($criteria->getDetailsToCategory() === $this) {
                $criteria->setDetailsToCategory(null);
            }
        }

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getDebit(): ?string
    {
        return $this->debit;
    }

    public function setDebit(?string $debit): self
    {
        $this->debit = $debit;

        return $this;
    }

    public function getCredit(): ?string
    {
        return $this->credit;
    }

    public function setCredit(?string $credit): self
    {
        $this->credit = $credit;

        return $this;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(?string $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getBudget(): ?Budget
    {
        return $this->budget;
    }

    public function setBudget(?Budget $budget): self
    {
        $this->budget = $budget;

        return $this;
    }
}
