<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class VersioningService
{
    private string $defaultVersion;

    public function __construct(
        private readonly ParameterBagInterface $params,
        private readonly RequestStack $requestStack
    )
    {
        $this->defaultVersion = $this->params->get('default_api_version');
    }

    public function getVersion(): string
    {
        $version = $this->defaultVersion;
        $request = $this->requestStack->getCurrentRequest();
        $acceptHeader = $request->headers->get('Accept');
        $acceptHeader = explode(';', $acceptHeader);
        foreach ($acceptHeader as $header) {
            if (strpos($header, 'version') !== false) {
                $version = explode('=', $header)[1];
                break;
            }
        }
        return $version;
    }
}