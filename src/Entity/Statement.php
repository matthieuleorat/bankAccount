<?php

namespace App\Entity;

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
     * @ORM\ManyToOne(targetEntity="App\Entity\Account", inversedBy="statements")
     * @ORM\JoinColumn(nullable=false)
     */
    private $account;

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

    public function __toString() : string
    {
        return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): self
    {
        $this->account = $account;

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
}
