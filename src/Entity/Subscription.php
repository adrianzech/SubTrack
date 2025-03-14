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

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Category $category = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank]
    #[Assert\Positive]
    private string $amount;

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

    #[ORM\ManyToOne(targetEntity: PaymentMethod::class, inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: true)]
    private ?PaymentMethod $paymentMethod = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $autoRenewal = true;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    public function __construct(
        string $name,
        string $amount,
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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): self
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

    public function getPaymentMethod(): ?PaymentMethod
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?PaymentMethod $paymentMethod): self
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
        $today = new DateTimeImmutable();
        $startDate = $this->startDate;
        $nextDate = clone $startDate;

        // Calculate how many billing cycles have passed since start date
        $modifyString = match ($this->billingCycle) {
            BillingCycleEnum::DAILY => "+{$this->billingOffset} day",
            BillingCycleEnum::WEEKLY => "+{$this->billingOffset} week",
            BillingCycleEnum::MONTHLY => "+{$this->billingOffset} month",
            BillingCycleEnum::YEARLY => "+{$this->billingOffset} year",
        };

        // Find the next payment date after today
        while ($nextDate <= $today) {
            $nextDate = $nextDate->modify($modifyString);
        }

        return $nextDate;
    }

    public function isActive(): bool
    {
        $now = new DateTimeImmutable();
        return $this->startDate <= $now && ($this->endDate === null || $this->endDate >= $now);
    }
}