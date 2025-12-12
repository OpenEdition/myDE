<?php

namespace MyDigitalEnvironment\MyDigitalEnvironmentBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use MyDigitalEnvironment\MyDigitalEnvironmentBundle\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Contracts\Translation\LocaleAwareInterface;

readonly class LocaleListener
{
    // todo: fetch instead configured/allowed locales ?
    const languages = ['en', 'fr'];

    /** @param iterable<mixed, LocaleAwareInterface> $localeAwareServices */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private iterable $localeAwareServices,
        private Security $security,
    )
    {
    }


    private function isInvalidRequest(KernelEvent $event): bool
    {
        return !$event->isMainRequest() || str_starts_with($event->getRequest()->getPathInfo(), '/_wdt/');
    }

    // Set the locale based on whether a cookie or a language request is present
    // Required for templates and translations
    public function onKernelRequest(RequestEvent $event): void
    {
        $user = $this->security->getUser();
        if ($this->isInvalidRequest($event)) {
            return;
        }
        // todo: read more on this subject:
        //  https://symfony.com/doc/7.4/session.html#setting-the-locale-based-on-the-user-s-preferences
        //  https://symfony.com/doc/7.4/translation.html#handling-the-user-s-locale
        //  It works, but I'm not sure if what I'm doing is the right way of doing setting the locale, suspect it's not
        //  recommend and a bit hacky (priority set at -32, fetching localeAwareServices and overriding/doing twice what
        //  LocaleAwareListener is doing)


        $queryLang = in_array($queryLang = $event->getRequest()->query->get('lang'), self::languages) ? $queryLang : null;
        $cookieLang = in_array($cookieLang = $event->getRequest()->cookies->get('lang'), self::languages) ? $cookieLang : null;
        $userLang = $user instanceof User && ($userLang = $user->getLocale()) !== null
            && in_array($userLang, self::languages) ? $userLang : null;
        $lang = self::languages[0];

        if ($queryLang !== null) {
            $lang = $queryLang;
        } else if ($userLang !== null) {
            $lang = $userLang;
        } else if ($cookieLang !== null) {
            $lang = $cookieLang;
        }

        $this->setLocale($lang);
    }

    // Update or set language cookie
    public function onKernelResponse(ResponseEvent $event): void
    {
        $user = $this->security->getUser();
        if ($this->isInvalidRequest($event)) {
            return;
        }

        // todo: detect locale from web browser language or ip ?
        $queryLang = in_array($queryLang = $event->getRequest()->query->get('lang'), self::languages) ? $queryLang : null;
        $cookieLang = in_array($cookieLang = $event->getRequest()->cookies->get('lang'), self::languages) ? $cookieLang : null;
        $userLang = $user instanceof User && ($userLang = $user->getLocale()) !== null
            && in_array($userLang, self::languages) ? $userLang : null;
        $lang = self::languages[0];

        if ($queryLang !== null) {
            $event->getResponse()->headers->setCookie(new Cookie('lang', $queryLang));
        } else if ($user !== null && $userLang !== $cookieLang) {
            $event->getResponse()->headers->setCookie(new Cookie('lang', $userLang));
        } else if ($cookieLang === null) {
            $event->getResponse()->headers->setCookie(new Cookie('lang', $lang));
        }
    }

    // Update user entity locale
    public function onKernelTerminate(TerminateEvent $event): void
    {
        $user = $this->security->getUser();
        if ($this->isInvalidRequest($event) || !$user instanceof User) {
            return;
        }

        $queryLang = in_array($queryLang = $event->getRequest()->query->get('lang'), self::languages) ? $queryLang : null;
        $cookieLang = in_array($cookieLang = $event->getRequest()->cookies->get('lang'), self::languages) ? $cookieLang : null;
        $lang = self::languages[0];

        if ($queryLang !== null) {
            $user->setLocale($queryLang);
            $this->entityManager->persist($user);
        }

        if ($user->getLocale() === null){
            $user->setLocale($cookieLang ?? $lang);
            $this->entityManager->persist($user);
        }

        $this->entityManager->flush();
    }

    private function setLocale(string $locale): void
    {
        foreach ($this->localeAwareServices as $service) {
            try {
                $service->setLocale($locale);
            } catch (\InvalidArgumentException) {
                $service->setLocale(self::languages[0]);
            }
        }
    }
}