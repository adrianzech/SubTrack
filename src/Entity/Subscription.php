<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SubscriptionRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SubscriptionRepository::class)]
#[ORM\Table(name: 'subscriptions')]
class Subscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    private string $name;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $category = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank]
    #[Assert\Positive]
    private float $amount;

    #[ORM\Column(type: Types::SMALLINT, options: ['default' => 1])]
    #[Assert\Positive]
    private int $billingOffset = 1;

    #[ORM\Column(type: Types::STRING, length: 10, enumType: BillingCycleEnum::class)]
    private BillingCycleEnum $billingCycle;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotBlank]
    private DateTimeImmutable $startDate;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $endDate = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private DateTimeImmutable $nextPaymentDate;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $paymentMethod = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $autoRenewal = true;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    public function __construct(
        string $name,
        float $amount,
        BillingCycleEnum $billingCycle,
        DateTimeImmutable $startDate,
        DateTimeImmutable $nextPaymentDate,
        ?string $category = null,
        int $billingOffset = 1,
        ?DateTimeImmutable $endDate = null,
        ?string $paymentMethod = null,
        bool $autoRenewal = true
    )
    {
        $this->name = $name;
        $this->amount = $amount;
        $this->billingCycle = $billingCycle;
        $this->startDate = $startDate;
        $this->nextPaymentDate = $nextPaymentDate;
        $this->category = $category;
        $this->billingOffset = $billingOffset;
        $this->endDate = $endDate;
        $this->paymentMethod = $paymentMethod;
        $this->autoRenewal = $autoRenewal;
        $this->createdAt = new DateTimeImmutable();
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

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    public function getBillingOffset(): int
    {
        return $this->billingOffset;
    }

    public function setBillingOffset(int $billingOffset): self
    {
        $this->billingOffset = $billingOffset;
        return $this;
    }

    public function getBillingCycle(): BillingCycleEnum
    {
        return $this->billingCycle;
    }

    public function setBillingCycle(BillingCycleEnum $billingCycle): self
    {
        $this->billingCycle = $billingCycle;
        return $this;
    }

    public function getStartDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(DateTimeImmutable $startDate): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): ?DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(?DateTimeImmutable $endDate): self
    {
        $this->endDate = $endDate;
        return $this;
    }

    public function getNextPaymentDate(): DateTimeImmutable
    {
        return $this->nextPaymentDate;
    }

    public function setNextPaymentDate(DateTimeImmutable $nextPaymentDate): self
    {
        $this->nextPaymentDate = $nextPaymentDate;
        return $this;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?string $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    public function isAutoRenewal(): bool
    {
        return $this->autoRenewal;
    }

    public function setAutoRenewal(bool $autoRenewal): self
    {
        $this->autoRenewal = $autoRenewal;
        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public function calculateNextPaymentDate(): DateTimeImmutable
    {
        $nextDate = $this->nextPaymentDate;

        return match ($this->billingCycle) {
            BillingCycleEnum::DAILY => $nextDate->modify("+{$this->billingOffset} day"),
            BillingCycleEnum::WEEKLY => $nextDate->modify("+{$this->billingOffset} week"),
            BillingCycleEnum::MONTHLY => $nextDate->modify("+{$this->billingOffset} month"),
            BillingCycleEnum::YEARLY => $nextDate->modify("+{$this->billingOffset} year"),
        };
    }

    public function isActive(): bool
    {
        $now = new DateTimeImmutable();
        return $this->startDate <= $now && ($this->endDate === null || $this->endDate >= $now);
    }
}