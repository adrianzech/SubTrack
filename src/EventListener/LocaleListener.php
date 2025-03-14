<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::REQUEST, priority: 20)]
class LocaleListener
{
    public function __construct(
        private readonly string $defaultLocale = 'en'
    ) {}

    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$request->hasPreviousSession()) {
            return;
        }

        // Try to see if the locale has been set as a _locale routing parameter
        if ($locale = $request->getSession()->get('_locale')) {
            $request->setLocale($locale);
        } else {
            // If no explicit locale has been set on this request, use the default locale
            $request->setLocale($this->defaultLocale);
        }
    }
}