<?php

declare(strict_types=1);

namespace AdvancedIdeasMechanics\MezzioReCaptchaV3\Services;

use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class ReCaptchaV3ValidatorFactory
{
    public function __invoke(ContainerInterface $container): ReCaptchaV3Validator
    {
        $config = $container->get('config')['recaptcha'] ?? [];

        return new ReCaptchaV3Validator(
            $container->get(ClientInterface::class),
            $container->get(RequestFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $config['secret_key'] ?? '',
            (float) ($config['score_threshold'] ?? 0.5)
        );
    }
}