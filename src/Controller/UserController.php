<?php

declare(strict_types=1);

namespace MyDigitalEnvironment\MyDigitalEnvironmentBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use MyDigitalEnvironment\MyDigitalEnvironmentBundle\Entity\User;
use MyDigitalEnvironment\MyDigitalEnvironmentBundle\Form\RegisterUserType;
use MyDigitalEnvironment\MyDigitalEnvironmentBundle\Form\UserType;
use MyDigitalEnvironment\MyDigitalEnvironmentBundle\Service\RegistrationModifierInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends AbstractController
{
    public function __construct(
        private readonly array $registrationModifiers = [],
    )
    {
    }

    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('@MyDigitalEnvironment/user/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    public function logout(): void
    {
    }

    public function register(
        Request $request,
        Security $security,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
    ): Response
    {
        // options for registration :
        //      - make email verification (see or use https://github.com/symfonycasts/verify-email-bundle)
        //          * add new User::isVerified property
        //      - make it so that they can set it so that the admin verify/validate registration // We could set the roles also here

        $user = new User();

        $form = $this->createForm(RegisterUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));

            // todo : use hash password form option
            // todo: We can't set the default roles for a user here, add config option ?

            foreach ($this->registrationModifiers as $registrationModifier => $priority) {
                if (!is_a($registrationModifier, RegistrationModifierInterface::class, true)) {
                    continue;
                }
                $modifier = new $registrationModifier();
                $modifier->modify($user);
            }
            $entityManager->persist($user);
            $entityManager->flush();

            # todo: give way to configure $authenticatorName via env ? Or is there a config option we can change ?
            return $security->login(
                $user,
                'form_login',
                'main',
            );
        }

        return $this->render('@MyDigitalEnvironment/user/register.html.twig', [
            'form' => $form,
        ]);
    }

    #[IsGranted('IS_AUTHENTICATED')]
    public function info(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $newUser */
            $entityManager->flush();
        }

        return $this->render('@MyDigitalEnvironment/user/info.html.twig', [
            'form' => $form,
        ]);
    }
}
