<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoginFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'app_email',
                'attr'  => [
                    'placeholder' => 'app_email_placeholder',
                    'autofocus'   => true,
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'app_password',
                'attr'  => [
                    'placeholder' => 'app_password_placeholder',
                ],
            ])
            ->add('rememberMe', CheckboxType::class, [
                'label'    => 'app_login_remember_me',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_field_name' => '_csrf_token',
            'csrf_token_id'   => 'authenticate',
        ]);
    }

    public function getBlockPrefix(): string
    {
        // This allows us to use field names like "_username" instead of "login[_username]"
        return '';
    }
}