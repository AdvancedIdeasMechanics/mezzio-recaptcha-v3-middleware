<?php

declare(strict_types=1);

namespace AdvancedIdeasMechanics\MezzioReCaptchaV3\Middleware;

use AdvancedIdeasMechanics\MezzioReCaptchaV3\Services\ReCaptchaV3Validator;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

class ReCaptchaMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): ReCaptchaMiddleware
    {
        $config = $container->get('config')['recaptcha'] ?? [];

        return new ReCaptchaMiddleware(
            $container->get(ReCaptchaV3Validator::class),
            $container->get(ResponseFactoryInterface::class),
            $config['default_action'] ?? null
        );
    }
}