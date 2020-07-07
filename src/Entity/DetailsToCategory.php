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
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $regex;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="detailsToCategories")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Category $category;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Filter", mappedBy="detailsToCategory", orphanRemoval=true, cascade={"persist"})
     */
    private $filters;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $label;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $debit;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $credit;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $date;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $comment;

    /**
     * @ORM\ManyToOne(targetEntity=Budget::class, inversedBy="detailsToCategories")
     */
    private $budget;

    public function __construct()
    {
        $this->filters = new ArrayCollection();
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
     * @return Collection|Filter[]
     */
    public function getFilters(): Collection
    {
        return $this->filters;
    }

    public function addFilter(Filter $filter): self
    {
        if (!$this->filters->contains($filter)) {
            $this->filters[] = $filter;
            $filter->setDetailsToCategory($this);
        }

        return $this;
    }

    public function removeFilter(Filter $filter): self
    {
        if ($this->filters->contains($filter)) {
            $this->filters->removeElement($filter);
            // set the owning side to null (unless already changed)
            if ($filter->getDetailsToCategory() === $this) {
                $filter->setDetailsToCategory(null);
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
