<?php

/**
 * This file is part of the BankAccount project.
 *
 * (c) Matthieu Leorat <matthieu.leorat@pm.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity(repositoryClass="App\Repository\CriteriaRepository")
 */
class Criteria
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
    private $field;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $compareOperator;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\DetailsToCategory", inversedBy="criteria")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?DetailsToCategory $detailsToCategory;

    public function __toString()
    {
        return $this->field . " " . $this->compareOperator . " ".$this->value;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getField(): ?string
    {
        return $this->field;
    }

    public function setField(string $field): self
    {
        $this->field = $field;

        return $this;
    }

    public function getCompareOperator(): ?string
    {
        return $this->compareOperator;
    }

    public function setCompareOperator(string $compareOperator): self
    {
        $this->compareOperator = $compareOperator;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getDetailsToCategory(): ?DetailsToCategory
    {
        return $this->detailsToCategory;
    }

    public function setDetailsToCategory(?DetailsToCategory $detailsToCategory): self
    {
        $this->detailsToCategory = $detailsToCategory;

        return $this;
    }
}
