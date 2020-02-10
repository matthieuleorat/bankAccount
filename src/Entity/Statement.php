<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StatementRepository")
 */
class Statement
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Source", inversedBy="statements")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $source;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="date_immutable")
     */
    private $startingDate;

    /**
     * @ORM\Column(type="date_immutable")
     */
    private $endingDate;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $startingBalance;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $endingBalance;

    /**
     * @ORM\Column(type="float")
     */
    private $TotalDebit;

    /**
     * @ORM\Column(type="float")
     */
    private $totalCredit;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Transaction", mappedBy="statement")
     */
    private $transactions;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
    }

    public function __toString() : string
    {
        return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSource(): ?Source
    {
        return $this->source;
    }

    public function setSource(?Source $source): self
    {
        $this->source = $source;

        return $this;
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

    public function getStartingDate(): ?\DateTimeImmutable
    {
        return $this->startingDate;
    }

    public function setStartingDate(\DateTimeImmutable $startingDate): self
    {
        $this->startingDate = $startingDate;

        return $this;
    }

    public function getEndingDate(): ?\DateTimeImmutable
    {
        return $this->endingDate;
    }

    public function setEndingDate(\DateTimeImmutable $endingDate): self
    {
        $this->endingDate = $endingDate;

        return $this;
    }

    public function getStartingBalance(): ?float
    {
        return $this->startingBalance;
    }

    public function setStartingBalance(?float $startingBalance): self
    {
        $this->startingBalance = $startingBalance;

        return $this;
    }

    public function getEndingBalance(): ?float
    {
        return $this->endingBalance;
    }

    public function setEndingBalance(?float $endingBalance): self
    {
        $this->endingBalance = $endingBalance;

        return $this;
    }

    public function getTotalDebit(): ?float
    {
        return $this->TotalDebit;
    }

    public function setTotalDebit(float $TotalDebit): self
    {
        $this->TotalDebit = $TotalDebit;

        return $this;
    }

    public function getTotalCredit(): ?float
    {
        return $this->totalCredit;
    }

    public function setTotalCredit(float $totalCredit): self
    {
        $this->totalCredit = $totalCredit;

        return $this;
    }

    /**
     * @return Collection|Transaction[]
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): self
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions[] = $transaction;
            $transaction->setStatement($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): self
    {
        if ($this->transactions->contains($transaction)) {
            $this->transactions->removeElement($transaction);
            // set the owning side to null (unless already changed)
            if ($transaction->getStatement() === $this) {
                $transaction->setStatement(null);
            }
        }

        return $this;
    }
}
