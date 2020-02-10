<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 */
class Category
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
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DetailsToCategory", mappedBy="category", orphanRemoval=true)
     */
    private $detailsToCategories;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Expense", mappedBy="category")
     */
    private $expenses;

    public function __construct()
    {
        $this->detailsToCategories = new ArrayCollection();
        $this->expenses = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param mixed $parent
     * @return Category
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @return Collection|DetailsToCategory[]
     */
    public function getDetailsToCategories(): Collection
    {
        return $this->detailsToCategories;
    }

    public function addDetailsToCategory(DetailsToCategory $detailsToCategory): self
    {
        if (!$this->detailsToCategories->contains($detailsToCategory)) {
            $this->detailsToCategories[] = $detailsToCategory;
            $detailsToCategory->setCategory($this);
        }

        return $this;
    }

    public function removeDetailsToCategory(DetailsToCategory $detailsToCategory): self
    {
        if ($this->detailsToCategories->contains($detailsToCategory)) {
            $this->detailsToCategories->removeElement($detailsToCategory);
            // set the owning side to null (unless already changed)
            if ($detailsToCategory->getCategory() === $this) {
                $detailsToCategory->setCategory(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Expense[]
     */
    public function getExpenses(): Collection
    {
        return $this->expenses;
    }

    public function addExpense(Expense $expense): self
    {
        if (!$this->expenses->contains($expense)) {
            $this->expenses[] = $expense;
            $expense->setCategory($this);
        }

        return $this;
    }

    public function removeExpense(Expense $expense): self
    {
        if ($this->expenses->contains($expense)) {
            $this->expenses->removeElement($expense);
            // set the owning side to null (unless already changed)
            if ($expense->getCategory() === $this) {
                $expense->setCategory(null);
            }
        }

        return $this;
    }
}
