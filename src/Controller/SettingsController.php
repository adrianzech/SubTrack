<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Category;
use App\Entity\PaymentMethod;
use App\Form\CategoryFormType;
use App\Form\PaymentMethodFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SettingsController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    #[Route('/settings', name: 'app_settings_index')]
    public function index(): Response
    {
        $categoryRepository = $this->entityManager->getRepository(Category::class);
        $categories = $categoryRepository->findAll();

        $paymentMethodRepository = $this->entityManager->getRepository(PaymentMethod::class);
        $paymentMethods = $paymentMethodRepository->findAll();

        return $this->render('pages/settings/index.html.twig', [
            'categories'     => $categories,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    #[Route('/settings/category//new', name: 'app_settings_category_new', methods: ['GET', 'POST'])]
    public function newCategory(Request $request): Response
    {
        $category = new Category(
            name: '',
        );

        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($category);
            $this->entityManager->flush();

            $this->addFlash('success', 'Category created successfully.');
            return $this->redirectToRoute('app_settings_index');
        }

        return $this->render('pages/settings/category/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/settings/category/{id}/delete', name: 'app_settings_category_delete', methods: ['POST'])]
    public function deleteCategory(Request $request, Category $category): Response
    {
        // Check CSRF token for security
        if ($this->isCsrfTokenValid('delete' . $category->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($category);
            $this->entityManager->flush();
            $this->addFlash('success', 'Category was successfully deleted.');
        }

        return $this->redirectToRoute('app_settings_index');
    }

    #[Route('/settings/category/{id}/edit', name: 'app_settings_category_edit', methods: ['GET', 'POST'])]
    public function editCategory(Request $request, Category $category): Response
    {
        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', 'Category was successfully updated.');

            return $this->redirectToRoute('app_settings_index');
        }

        return $this->render('pages/settings/category/edit.html.twig', [
            'category' => $category,
            'form'     => $form->createView(),
        ]);
    }

    #[Route('/payment_method/category//new', name: 'app_settings_payment_method_new', methods: ['GET', 'POST'])]
    public function newPaymentMethod(Request $request): Response
    {
        $paymentMethod = new PaymentMethod(
            name: '',
        );

        $form = $this->createForm(PaymentMethodFormType::class, $paymentMethod);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($paymentMethod);
            $this->entityManager->flush();

            $this->addFlash('success', 'Payment method created successfully.');
            return $this->redirectToRoute('app_settings_index');
        }

        return $this->render('pages/settings/payment_method/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/payment_method/category/{id}/delete', name: 'app_settings_payment_method_delete', methods: ['POST'])]
    public function deletePaymentMethod(Request $request, PaymentMethod $paymentMethod): Response
    {
        // Check CSRF token for security
        if ($this->isCsrfTokenValid('delete' . $paymentMethod->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($paymentMethod);
            $this->entityManager->flush();
            $this->addFlash('success', 'Payment method was successfully deleted.');
        }

        return $this->redirectToRoute('app_settings_index');
    }

    #[Route('/payment_method/category/{id}/edit', name: 'app_settings_payment_method_edit', methods: ['GET', 'POST'])]
    public function editPaymentMethod(Request $request, PaymentMethod $paymentMethod): Response
    {
        $form = $this->createForm(PaymentMethodFormType::class, $paymentMethod);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', 'Payment method was successfully updated.');

            return $this->redirectToRoute('app_settings_index');
        }

        return $this->render('pages/settings/payment_method/edit.html.twig', [
            'payment_method' => $paymentMethod,
            'form'           => $form->createView(),
        ]);
    }
}