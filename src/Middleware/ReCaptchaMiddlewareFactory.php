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
        $validator = $container->get(ReCaptchaV3Validator::class);
        $responseFactory = $container->get(ResponseFactoryInterface::class);

        $config = $container->get('config')['recaptcha'] ?? [];
        $defaultAction = (string) ($config['default_action'] ?? 'login');

        return new ReCaptchaMiddleware($validator, $responseFactory, $defaultAction);
    }
}