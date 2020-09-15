<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TransactionRepository")
 */
class Transaction
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date_immutable")
     */
    private $date;

    /**
     * @ORM\Column(type="text")
     */
    private $details;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $debit;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $credit;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Statement", inversedBy="transactions")
     * @ORM\JoinColumn(name="statement", referencedColumnName="id", onDelete="CASCADE")
     */
    private $statement;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Expense", mappedBy="transaction")
     */
    private $expenses;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $ignore = false;

    /**
     * @var bool
     */
    private $createExpense = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $type;

    public function __construct()
    {
        $this->expenses = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->details;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(string $details): self
    {
        $this->details = $details;

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

    public function getStatement(): ?Statement
    {
        return $this->statement;
    }

    public function setStatement(?Statement $statement): self
    {
        $this->statement = $statement;

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
            $expense->setTransaction($this);
        }

        return $this;
    }

    public function removeExpense(Expense $expense): self
    {
        if ($this->expenses->contains($expense)) {
            $this->expenses->removeElement($expense);
            // set the owning side to null (unless already changed)
            if ($expense->getTransaction() === $this) {
                $expense->setTransaction(null);
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isCreateExpense(): bool
    {
        return $this->createExpense;
    }

    public function setCreateExpense(bool $createExpense): Transaction
    {
        $this->createExpense = $createExpense;
        return $this;
    }

    public function getType()
    {
        return unserialize(base64_decode($this->type));
    }

    public function setType($type): self
    {
        $this->type = base64_encode(serialize($type));

        return $this;
    }

    public function isIgnore(): ? bool
    {
        return $this->ignore;
    }

    public function setIgnore(bool $ignore): void
    {
        $this->ignore = $ignore;
    }
}
