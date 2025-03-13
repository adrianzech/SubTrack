<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\BillingCycleEnum;
use App\Entity\Subscription;
use App\Form\SubscriptionFormType;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SubscriptionController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    #[Route('/', name: 'app_subscription_index')]
    public function index(): Response
    {
        $subscriptionRepository = $this->entityManager->getRepository(Subscription::class);
        $subscriptions = $subscriptionRepository->findAll();

        // Get active subscriptions count and costs
        $activeCount = $subscriptionRepository->countActiveSubscriptions();
        $monthlyCost = $subscriptionRepository->calculateMonthlyTotalCost();
        $yearlyCost = $subscriptionRepository->calculateYearlyTotalCost();

        return $this->render('pages/subscription/index.html.twig', [
            'subscriptions' => $subscriptions,
            'active_count'  => $activeCount,
            'monthly_cost'  => $monthlyCost,
            'yearly_cost'   => $yearlyCost,
        ]);
    }

    #[Route('/subscription/new', name: 'app_subscription_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $startDate = new DateTimeImmutable();
        $subscription = new Subscription(
            name: '',
            amount: 0.0,
            billingCycle: BillingCycleEnum::MONTHLY,
            startDate: $startDate,
            nextPaymentDate: $startDate
        );

        $form = $this->createForm(SubscriptionFormType::class, $subscription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set next payment date based on start date and billing cycle
            $subscription->setNextPaymentDate($subscription->calculateNextPaymentDate());

            $this->entityManager->persist($subscription);
            $this->entityManager->flush();

            $this->addFlash('success', 'Subscription created successfully.');
            return $this->redirectToRoute('app_subscription_index');
        }

        return $this->render('pages/subscription/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/subscription/{id}/delete', name: 'app_subscription_delete', methods: ['POST'])]
    public function delete(Request $request, Subscription $subscription): Response
    {
        // Check CSRF token for security
        if ($this->isCsrfTokenValid('delete' . $subscription->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($subscription);
            $this->entityManager->flush();
            $this->addFlash('success', 'Subscription was successfully deleted.');
        }

        return $this->redirectToRoute('app_subscription_index');
    }

    #[Route('/subscription/{id}/edit', name: 'app_subscription_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Subscription $subscription): Response
    {
        $form = $this->createForm(SubscriptionFormType::class, $subscription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Update next payment date if billing parameters changed
            $subscription->setNextPaymentDate($subscription->calculateNextPaymentDate());

            $this->entityManager->flush();
            $this->addFlash('success', 'Subscription was successfully updated.');

            return $this->redirectToRoute('app_subscription_index');
        }

        return $this->render('pages/subscription/edit.html.twig', [
            'subscription' => $subscription,
            'form'         => $form->createView(),
        ]);
    }
}