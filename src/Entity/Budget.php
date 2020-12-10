<?php declare(strict_types=1);

/**
 * This file is part of the BankAccount project.
 *
 * (c) Matthieu Leorat <matthieu.leorat@pm.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BudgetRepository")
 */
class Budget
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
     * @ORM\OneToMany(targetEntity="App\Entity\Expense", mappedBy="budget")
     */
    private $expenses;

    /**
     * @ORM\OneToMany(targetEntity=Category::class, mappedBy="budget")
     */
    private $categories;

    /**
     * @ORM\OneToMany(targetEntity=DetailsToCategory::class, mappedBy="budget")
     */
    private $detailsToCategories;

    public function __toString()
    {
        return $this->name;
    }

    public function __construct()
    {
        $this->expenses = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->detailsToCategories = new ArrayCollection();
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
            $expense->setBudget($this);
        }

        return $this;
    }

    public function removeExpense(Expense $expense): self
    {
        if ($this->expenses->contains($expense)) {
            $this->expenses->removeElement($expense);
            // set the owning side to null (unless already changed)
            if ($expense->getBudget() === $this) {
                $expense->setBudget(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Category[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
            $category->setBudget($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
            // set the owning side to null (unless already changed)
            if ($category->getBudget() === $this) {
                $category->setBudget(null);
            }
        }

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
            $detailsToCategory->setBudget($this);
        }

        return $this;
    }

    public function removeDetailsToCategory(DetailsToCategory $detailsToCategory): self
    {
        if ($this->detailsToCategories->contains($detailsToCategory)) {
            $this->detailsToCategories->removeElement($detailsToCategory);
            // set the owning side to null (unless already changed)
            if ($detailsToCategory->getBudget() === $this) {
                $detailsToCategory->setBudget(null);
            }
        }

        return $this;
    }
}
