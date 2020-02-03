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
     * @ORM\OneToMany(targetEntity="App\Entity\Transaction", mappedBy="category")
     */
    private $transaction;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DetailsToCategory", mappedBy="category", orphanRemoval=true)
     */
    private $detailsToCategories;

    public function __construct()
    {
        $this->transaction = new ArrayCollection();
        $this->detailsToCategories = new ArrayCollection();
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
     * @return Collection|Transaction[]
     */
    public function getTransaction(): Collection
    {
        return $this->transaction;
    }

    public function addTransaction(Transaction $transaction): self
    {
        if (!$this->transaction->contains($transaction)) {
            $this->transaction[] = $transaction;
            $transaction->setCategory($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): self
    {
        if ($this->transaction->contains($transaction)) {
            $this->transaction->removeElement($transaction);
            // set the owning side to null (unless already changed)
            if ($transaction->getCategory() === $this) {
                $transaction->setCategory(null);
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
}
