<?php

namespace App\EventListener;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class AuthenticationFailureListener
{
    public function __construct
    (
        private readonly RequestStack $requestStack,
        private readonly TranslatorInterface $translator,
        // needed because of locale listener
        private readonly ParameterBagInterface $params
    )
    {
    }

    public function onAuthenticationFailure()
    {
        // set locale because dosent work for some reason in LocaleListener : needs to be fixed
        $request = $this->requestStack->getCurrentRequest();
        if ($request->headers->has('Accept-Language')) {
            $locale = $request->headers->get('Accept-Language');
            if (in_array($locale, $this->params->get('app.supported_locales'))) {
                $this->translator->setLocale($request->headers->get('Accept-Language'));
            }
        }

        throw new HttpException(
            Response::HTTP_UNAUTHORIZED,
            $this->translator->trans('message.security.login_error')
        );
    }
}