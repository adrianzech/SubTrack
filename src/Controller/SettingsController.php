<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Category;
use App\Entity\PaymentMethod;
use App\Entity\User;
use App\Form\AccountFormType;
use App\Form\CategoryFormType;
use App\Form\PaymentMethodFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SettingsController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {}

    #[Route('/settings', name: 'app_settings_index')]
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            throw new AccessDeniedException('User not found');
        }

        $form = $this->createForm(AccountFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $emailChanged = false;
            $passwordChanged = false;

            // Get form data
            $currentPassword = $form->get('currentPassword')->getData();
            $plainPassword = $form->get('plainPassword')->getData();

            // Email update logic
            $submittedEmail = $form->get('email')->getData();
            $originalEmail = $user->getEmail();

            // Only update if a non-empty email was submitted and it's different from the current one
            if (!empty($submittedEmail) && $submittedEmail !== $originalEmail) {
                $user->setEmail($submittedEmail);
                $emailChanged = true;
            }

            // Password update logic
            // Check if both current password and new password (either field) are filled in
            if (!empty($currentPassword) && !empty($plainPassword)) {
                // Verify current password
                if (!$this->passwordHasher->isPasswordValid($user, $currentPassword)) {
                    $this->addFlash('error', 'app_settings_current_password_incorrect');
                    return $this->redirectToRoute('app_settings_index');
                }

                // Hash new password
                $hashedPassword = $this->passwordHasher->hashPassword(
                    $user,
                    $plainPassword
                );
                $user->setPassword($hashedPassword);
                $passwordChanged = true;
            } elseif (!empty($currentPassword) && empty($plainPassword)) {
                // Current password provided but no new password
                $this->addFlash('error', 'app_settings_enter_password');
                return $this->redirectToRoute('app_settings_index');
            } elseif (empty($currentPassword) && !empty($plainPassword)) {
                // New password provided but no current password
                $this->addFlash('error', 'app_settings_enter_password_to_confirm');
                return $this->redirectToRoute('app_settings_index');
            }

            // Only flush if there were actual changes
            if ($emailChanged || $passwordChanged) {
                $this->entityManager->flush();

                if ($emailChanged) {
                    $this->addFlash('success', 'app_settings_password_successfully_updated');
                }

                if ($passwordChanged) {
                    $this->addFlash('success', '');
                }
            } else if ($form->isSubmitted()) {
                $this->addFlash('info', 'app_settings_no_changes');
            }

            return $this->redirectToRoute('app_settings_index');
        }

        $categoryRepository = $this->entityManager->getRepository(Category::class);
        $categories = $categoryRepository->findAll();

        $paymentMethodRepository = $this->entityManager->getRepository(PaymentMethod::class);
        $paymentMethods = $paymentMethodRepository->findAll();

        return $this->render('pages/settings/index.html.twig', [
            'accountForm'    => $form,
            'categories'     => $categories,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    #[Route('/settings/category/new', name: 'app_settings_category_new', methods: ['GET', 'POST'])]
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

            $this->addFlash('success', 'app_category_successfully_created');
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
            $this->addFlash('success', 'app_category_successfully_deleted');
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
            $this->addFlash('success', 'app_category_successfully_updated');

            return $this->redirectToRoute('app_settings_index');
        }

        return $this->render('pages/settings/category/edit.html.twig', [
            'category' => $category,
            'form'     => $form->createView(),
        ]);
    }

    #[Route('/settings/payment_method/category/new', name: 'app_settings_payment_method_new', methods: ['GET', 'POST'])]
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

            $this->addFlash('success', 'app_payment_method_successfully_created');
            return $this->redirectToRoute('app_settings_index');
        }

        return $this->render('pages/settings/payment_method/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/settings/payment_method/category/{id}/delete', name: 'app_settings_payment_method_delete', methods: ['POST'])]
    public function deletePaymentMethod(Request $request, PaymentMethod $paymentMethod): Response
    {
        // Check CSRF token for security
        if ($this->isCsrfTokenValid('delete' . $paymentMethod->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($paymentMethod);
            $this->entityManager->flush();
            $this->addFlash('success', 'app_payment_method_successfully_deleted');
        }

        return $this->redirectToRoute('app_settings_index');
    }

    #[Route('/settings/payment_method/category/{id}/edit', name: 'app_settings_payment_method_edit', methods: ['GET', 'POST'])]
    public function editPaymentMethod(Request $request, PaymentMethod $paymentMethod): Response
    {
        $form = $this->createForm(PaymentMethodFormType::class, $paymentMethod);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', 'app_payment_method_successfully_updated');

            return $this->redirectToRoute('app_settings_index');
        }

        return $this->render('pages/settings/payment_method/edit.html.twig', [
            'payment_method' => $paymentMethod,
            'form'           => $form->createView(),
        ]);
    }

    #[Route('/settings/change-locale/{locale}', name: 'app_settings_change_locale')]
    public function changeLocale(string $locale, Request $request): Response
    {
        // Validate locale is supported
        if (!in_array($locale, ['en', 'de'])) {
            $locale = 'en'; // Default to English if not supported
        }

        // Set locale in session
        $request->getSession()->set('_locale', $locale);

        // Redirect back to the referring page or homepage
        $referer = $request->headers->get('referer');
        return $this->redirect($referer ?: $this->generateUrl('app_settings_index'));
    }
}