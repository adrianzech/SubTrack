<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\BillingCycleEnum;
use App\Entity\Subscription;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Subscription>
 *
 * @method Subscription|null find($id, $lockMode = null, $lockVersion = null)
 * @method Subscription|null findOneBy(array $criteria, array $orderBy = null)
 * @method Subscription[]    findAll()
 * @method Subscription[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subscription::class);
    }

    /**
     * Count all active subscriptions
     */
    public function countActiveSubscriptions(): int
    {
        $now = new DateTimeImmutable();

        return $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.startDate <= :now')
            ->andWhere('s.endDate IS NULL OR s.endDate >= :now')
            ->setParameter('now', $now)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Calculate total monthly costs for all active subscriptions
     * Takes into account different billing cycles and offsets
     */
    public function calculateMonthlyTotalCost(): float
    {
        $subscriptions = $this->findActiveSubscriptions();
        $monthlyCost = 0.0;

        foreach ($subscriptions as $subscription) {
            $monthlyCost += $this->calculateSubscriptionMonthlyRate($subscription);
        }

        return $monthlyCost;
    }

    /**
     * Find all active subscriptions
     *
     * @return Subscription[]
     */
    private function findActiveSubscriptions(): array
    {
        $now = new DateTimeImmutable();

        return $this->createQueryBuilder('s')
            ->where('s.startDate <= :now')
            ->andWhere('s.endDate IS NULL OR s.endDate >= :now')
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();
    }

    /**
     * Calculate monthly rate for a single subscription
     * Normalizes rates based on billing cycle and offset
     */
    private function calculateSubscriptionMonthlyRate(Subscription $subscription): float
    {
        $amount = $subscription->getAmount();
        $billingCycle = $subscription->getBillingCycle();
        $billingOffset = $subscription->getBillingOffset();

        return match ($billingCycle) {
            BillingCycleEnum::DAILY => $amount * 30 / $billingOffset,
            BillingCycleEnum::WEEKLY => $amount * 4.33 / $billingOffset, // Average 4.33 weeks per month
            BillingCycleEnum::MONTHLY => $amount / $billingOffset,
            BillingCycleEnum::YEARLY => $amount / (12 * $billingOffset),
        };
    }

    /**
     * Calculate total yearly costs for all active subscriptions
     * Takes into account different billing cycles and offsets
     */
    public function calculateYearlyTotalCost(): float
    {
        $subscriptions = $this->findActiveSubscriptions();
        $yearlyCost = 0.0;

        foreach ($subscriptions as $subscription) {
            $yearlyCost += $this->calculateSubscriptionYearlyRate($subscription);
        }

        return $yearlyCost;
    }

    /**
     * Calculate yearly rate for a single subscription
     * Normalizes rates based on billing cycle and offset
     */
    private function calculateSubscriptionYearlyRate(Subscription $subscription): float
    {
        $amount = $subscription->getAmount();
        $billingCycle = $subscription->getBillingCycle();
        $billingOffset = $subscription->getBillingOffset();

        return match ($billingCycle) {
            BillingCycleEnum::DAILY => $amount * 365 / $billingOffset,
            BillingCycleEnum::WEEKLY => $amount * 52 / $billingOffset,
            BillingCycleEnum::MONTHLY => $amount * 12 / $billingOffset,
            BillingCycleEnum::YEARLY => $amount / $billingOffset,
        };
    }

    /**
     * Calculate monthly and yearly costs for a specific category
     *
     * @return array{monthly: float, yearly: float}
     */
    public function calculateCostsByCategory(string $category): array
    {
        $subscriptions = $this->createQueryBuilder('s')
            ->where('s.category = :category')
            ->andWhere('s.startDate <= :now')
            ->andWhere('s.endDate IS NULL OR s.endDate >= :now')
            ->setParameter('category', $category)
            ->setParameter('now', new DateTimeImmutable())
            ->getQuery()
            ->getResult();

        $monthlyCost = 0.0;
        $yearlyCost = 0.0;

        foreach ($subscriptions as $subscription) {
            $monthlyCost += $this->calculateSubscriptionMonthlyRate($subscription);
            $yearlyCost += $this->calculateSubscriptionYearlyRate($subscription);
        }

        return [
            'monthly' => $monthlyCost,
            'yearly'  => $yearlyCost,
        ];
    }
}
