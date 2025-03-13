<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\BillingCycleEnum;
use App\Entity\Subscription;
use App\Form\SubscriptionFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use DateTimeImmutable;

class SubscriptionController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    #[Route('/', name: 'app_subscription_index')]
    public function index(): Response
    {
        $subscriptions = $this->entityManager->getRepository(Subscription::class)->findAll();

        return $this->render('pages/subscription/index.html.twig', [
            'subscriptions' => $subscriptions,
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
}