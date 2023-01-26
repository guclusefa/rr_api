<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Translation\Translator;

class LocaleListener
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var Translator
     */
    private $translator;

    public function __construct(RequestStack $requestStack, Translator $translator)
    {
        $this->requestStack = $requestStack;
        $this->translator = $translator;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request->headers->has('Accept-Language')) {
            $this->translator->setLocale($request->headers->get('Accept-Language'));
        }
    }
}