<?php

namespace MyDigitalEnvironment\MyDigitalEnvironmentBundle\EventListener;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class LocaleListener
{
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest() || str_starts_with($event->getRequest()->getPathInfo(), '/_wdt/')){
            return;
        }

        // todo: detect locale from web browser language or ip if cookie is not set ?
        $lang = $event->getRequest()->cookies->get('lang', 'en');
        $lang = $lang !== 'en' && $lang !== 'fr' ? 'en' : $lang;

        $queryLang = $event->getRequest()->query->get('lang');
        $lang = $queryLang === 'fr' || $queryLang === 'en' ? $queryLang : $lang;

        $event->getRequest()->setLocale($lang);
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest() || str_starts_with($event->getRequest()->getPathInfo(), '/_wdt/')){
            return;
        }

        // todo: detect locale from web browser language or ip ?
        $queryLang = $event->getRequest()->query->get('lang');
        if (!$event->getRequest()->cookies->has('lang') || $queryLang === 'fr' || $queryLang === 'en') {
            $event->getResponse()->headers->setCookie(new Cookie('lang',$queryLang ?? 'en'));
        } else {
            $lang = $event->getRequest()->cookies->get('lang');
            if ($lang !== 'fr' && $lang !== 'en') {
                $event->getResponse()->headers->setCookie(new Cookie('lang','en'));
            }
        }
    }
}