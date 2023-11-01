<?php

namespace src\Entity;

use ApiPlatform\Metadata\ApiResource;
use src\Entity\InvoiceLine;
use src\Entity\Membership;
use src\Repository\InvoiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
class Invoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column]
    private ?float $amount = null;

    #[ORM\OneToMany(mappedBy: 'invoice', targetEntity: InvoiceLine::class, orphanRemoval: true)]
    private Collection $invoicelines;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    private ?Membership $membership = null;

    public function __construct()
    {
        $this->invoicelines = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

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

    /**
     * @return Collection<int, InvoiceLine>
     */
    public function getInvoicelines(): Collection
    {
        return $this->invoicelines;
    }

    public function addInvoiceline(InvoiceLine $invoiceline): self
    {
        if (!$this->invoicelines->contains($invoiceline)) {
            $this->invoicelines->add($invoiceline);
            $invoiceline->setInvoice($this);
        }

        return $this;
    }

    public function removeInvoiceline(InvoiceLine $invoiceline): self
    {
        if ($this->invoicelines->removeElement($invoiceline)) {
            // set the owning side to null (unless already changed)
            if ($invoiceline->getInvoice() === $this) {
                $invoiceline->setInvoice(null);
            }
        }

        return $this;
    }

    public function getMembership(): ?Membership
    {
        return $this->membership;
    }

    public function setMembership(?Membership $membership): self
    {
        $this->membership = $membership;

        return $this;
    }
}
