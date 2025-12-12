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
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\Email;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User $user */
        // todo: more detailed error message
        // todo: success / popup message
        // todo: make it so that page is properly reloaded when the form change, ie: when user create password when it was null before
        //  or maybe push user to another page to force reload ?
        //  try redirection ?
        $user = $builder->getData();
        $builder
            ->add('name', TextType::class, [
                'required' => false,
                'label' => 'rform.name',
                'attr' => [
                    'placeholder' => 'rform.name',
                ],
            ])
            ->add('surname', TextType::class, [
                'required' => false,
                'label' => 'rform.surname',
                'attr' => [
                    'placeholder' => 'rform.surname',
                ],
            ])
            ->add('email', EmailType::class, [
                'disabled' => true,
                'required' => false,
                'label' => 'rform.email',
                'constraints' => new Email(),
                'attr' => [
                    'placeholder' => 'rform.email.example',
                    'readonly' => true, // todo: readonly for as long as we can't test email
                ],
            ])
            ->add('new_password', RepeatedType::class, [
                'required' => false,
                'mapped' => false,
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match',
                'first_options' => ['hash_property_path' => 'password', 'label' => 'uform.password', 'attr' => ['placeholder' => 'uform.password']],
                'second_options' => ['label' => 'uform.password.repeat', 'attr' => ['placeholder' => 'uform.password.repeat']],
            ]);
        if ($user->getPassword() !== null) { // todo: conditional or hidden type ? should we instead change the value of required ?
            $builder->add('current_password', PasswordType::class, [
                'required' => true,
                'mapped' => false,
                'label' => 'rform.password',
                'constraints' => new UserPassword(),
                'attr' => [
                    'placeholder' => 'uform.password.validate',
                ],
            ]);
        }
        $builder->add(
            'save',
            SubmitType::class, [
                'label' => 'uform.save'
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'my-de',
            'data_class' => User::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'user_info_type_token'
        ]);
    }
}
