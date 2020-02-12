<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ExpenseRepository")
 */
class Expense
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
    private $label;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="expenses")
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Transaction", inversedBy="expenses")
     * @ORM\JoinColumn(name="transaction", referencedColumnName="id", onDelete="CASCADE")
     */
    private $transaction;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $debit;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $credit;

    /**
     * @ORM\Column(type="date_immutable")
     */
    private $date;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    public function __toString()
    {
        return $this->label;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(?Transaction $transaction): self
    {
        $this->transaction = $transaction;

        return $this;
    }

    public function getDebit(): ?float
    {
        return $this->debit;
    }

    public function setDebit(?float $debit): self
    {
        $this->debit = $debit;

        return $this;
    }

    public function getCredit(): ?float
    {
        return $this->credit;
    }

    public function setCredit(?float $credit): self
    {
        $this->credit = $credit;

        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): self
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

    public function getAmount() : string
    {
        $sign = '';
        $amout = (string) $this->credit;

        if (null !== $this->debit) {
            $sign = '-';
            $amout = (string) $this->debit;
        }

        return $sign.$amout.'€';
    }
}
