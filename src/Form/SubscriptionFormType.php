<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\BillingCycleEnum;
use App\Entity\Category;
use App\Entity\Subscription;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubscriptionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => [
                    'placeholder' => 'Netflix, Spotify, etc.'
                ],
            ])
            ->add('category', EntityType::class, [
                'class'        => Category::class,
                'choice_label' => 'name',
                'placeholder'  => 'Select a category',
                'required'     => false,
            ])
            ->add('amount', MoneyType::class, [
                'currency' => 'EUR',
            ])
            ->add('billingOffset', NumberType::class, [
                'attr' => [
                    'min' => 1,
                ],
                'data' => 1,
                #'help' => 'How often the subscription bills (e.g., every 1 month, every 2 weeks)',
            ])
            ->add('billingCycle', EnumType::class, [
                'class'        => BillingCycleEnum::class,
                'choice_label' => fn($choice) => ucfirst($choice->value),
            ])
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'input'  => 'datetime_immutable',
            ])
            ->add('endDate', DateType::class, [
                'widget'   => 'single_text',
                'input'    => 'datetime_immutable',
                'required' => false,
                'help'     => 'Leave empty for subscriptions with no end date',
            ])
            ->add('paymentMethod', TextType::class, [
                'required' => false,
                'attr'     => [
                    'placeholder' => 'Credit Card, PayPal, etc.'
                ],
            ])
            ->add('autoRenewal', CheckboxType::class, [
                'required' => false,
                'data'     => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Subscription::class,
        ]);
    }
}