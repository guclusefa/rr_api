<?php

namespace App\EventListener;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\Translator;

class LocaleListener
{
    private RequestStack $requestStack;
    private Translator $translator;
    private ParameterBagInterface $params;

    public function __construct(RequestStack $requestStack, Translator $translator, ParameterBagInterface $params)
    {
        $this->requestStack = $requestStack;
        $this->translator = $translator;
        $this->params = $params;
    }

    public function onKernelRequest(): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request->headers->has('Accept-Language')) {
            $locale = $request->headers->get('Accept-Language');
            if (in_array($locale, $this->params->get('app.supported_locales'))) {
                $this->translator->setLocale($request->headers->get('Accept-Language'));
            }
        }
    }
}