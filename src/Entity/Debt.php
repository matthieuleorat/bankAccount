<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DebtRepository")
 */
class Debt
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="debts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $debtor;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="credits")
     * @ORM\JoinColumn(nullable=false)
     */
    private $creditor;

    /**
     * @ORM\Column(type="float")
     */
    private $amount;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\Column(type="date_immutable")
     */
    private $date;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Expense", inversedBy="debts")
     */
    private $expense;

    public function __construct()
    {
        $this->expense = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDebtor(): ?User
    {
        return $this->debtor;
    }

    public function setDebtor(?User $debtor): self
    {
        $this->debtor = $debtor;

        return $this;
    }

    public function getCreditor(): ?User
    {
        return $this->creditor;
    }

    public function setCreditor(?User $creditor): self
    {
        $this->creditor = $creditor;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

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

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): self
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return Collection|Expense[]
     */
    public function getExpense(): Collection
    {
        return $this->expense;
    }

    public function addExpense(Expense $expense): self
    {
        if (!$this->expense->contains($expense)) {
            $this->expense[] = $expense;
        }

        return $this;
    }

    public function removeExpense(Expense $expense): self
    {
        if ($this->expense->contains($expense)) {
            $this->expense->removeElement($expense);
        }

        return $this;
    }
}
