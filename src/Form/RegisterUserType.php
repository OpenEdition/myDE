<?php

namespace MyDigitalEnvironment\MyDigitalEnvironmentBundle\Form;

use MyDigitalEnvironment\MyDigitalEnvironmentBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegisterUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => 'rform.email.label',
                'attr' => [
                    'placeholder' => 'rform.email.example',
                ],
            ])
            // todo: add password strength validation
            ->add('password', RepeatedType::class, [
                'required' => true,
                'type' => PasswordType::class,
                // todo: translate invalid_message (find way to change the translation domain of invalid_message ?)
                'invalid_message' => 'The password fields must match',
                'first_options' => ['label' => 'rform.password', 'attr' => ['placeholder' => 'rform.password']],
                'second_options' => ['label' => 'rform.password.repeat', 'attr' => ['placeholder' => 'rform.password.repeat']],
            ])
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'rform.name',
                'attr' => [
                    'placeholder' => 'rform.name',
                ],
            ])
            ->add('surname', TextType::class, [
                'required' => true,
                'label' => 'rform.surname',
                'attr' => [
                    'placeholder' => 'rform.surname',
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'rform.register'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'my-de',
            'data_class' => User::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'register_user_token'
        ]);
    }
}
