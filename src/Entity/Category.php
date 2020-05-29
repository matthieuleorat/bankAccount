<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
// @ORM\Entity(repositoryClass="Gedmo\Tree\Entity\Repository\NestedTreeRepository")
/**
 * @Gedmo\Tree(type="nested")
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
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     */
    private $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    private $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    private $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumn(name="tree_root", referencedColumnName="id", onDelete="CASCADE")
     */
    private $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="Category", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    private $children;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DetailsToCategory", mappedBy="category", orphanRemoval=true)
     */
    private $detailsToCategories;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Expense", mappedBy="category")
     */
    private $expenses;

    /**
     * @ORM\ManyToOne(targetEntity=Budget::class, inversedBy="categories")
     */
    private $budget;

    public function __construct()
    {
        $this->detailsToCategories = new ArrayCollection();
        $this->expenses = new ArrayCollection();
        $this->children = new ArrayCollection();
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

    public function getRoot()
    {
        return $this->root;
    }

    public function setParent(Category $parent = null)
    {
        $this->parent = $parent;
    }

    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return mixed
     */
    public function getLvl()
    {
        return $this->lvl;
    }

    /**
     * @return ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
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
