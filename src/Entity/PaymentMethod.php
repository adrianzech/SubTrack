<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PaymentMethodRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaymentMethodRepository::class)]
class PaymentMethod
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    private string $name;

    #[ORM\OneToMany(targetEntity: Subscription::class, mappedBy: 'paymentMethod')]
    private Collection $items;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return Collection<int, Subscription>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(Subscription $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setPaymentMethod($this);
        }

        return $this;
    }

    public function removeItem(Subscription $item): self
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getPaymentMethod() === $this) {
                $item->setPaymentMethod(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}