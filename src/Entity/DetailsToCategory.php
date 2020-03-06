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
     * @ORM\OneToMany(targetEntity="App\Entity\Filter", mappedBy="detailsToCategory", orphanRemoval=true)
     */
    private $filters;

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
}
