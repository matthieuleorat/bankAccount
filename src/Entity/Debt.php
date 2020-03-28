<?php

namespace App\Entity;

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
}
