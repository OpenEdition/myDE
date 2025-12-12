<?php

namespace MyDigitalEnvironment\MyDigitalEnvironmentBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use MyDigitalEnvironment\MyDigitalEnvironmentBundle\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

final readonly class VisitListener
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security,
    )
    {
    }


    public function onKernelTerminate(TerminateEvent $event): void
    {
        if (!$event->isMainRequest()
            || str_starts_with($event->getRequest()->getPathInfo(), '/_wdt/')
            || str_starts_with($event->getRequest()->getPathInfo(), '/_profiler/')){
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return;
        }
        $user->setVisitedAt(new \DateTimeImmutable());
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
