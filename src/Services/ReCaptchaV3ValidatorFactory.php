<?php

declare(strict_types=1);

namespace AdvancedIdeasMechanics\MezzioReCaptchaV3\Services;

use GuzzleHttp\Client as GuzzleClient;
use Laminas\Diactoros\RequestFactory as DiactorosRequestFactory;
use Laminas\Diactoros\StreamFactory as DiactorosStreamFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use RuntimeException;

class ReCaptchaV3ValidatorFactory
{
    public function __invoke(ContainerInterface $container): ReCaptchaV3Validator
    {
        $config = $container->get('config')['recaptcha'] ?? [];

        // 1. Resolve HTTP Client (Container -> Guzzle Fallback)
        $httpClient = match (true) {
            $container->has(ClientInterface::class) => $container->get(ClientInterface::class),
            class_exists(GuzzleClient::class)       => new GuzzleClient(),
            default => throw new RuntimeException(
                'No PSR-18 HTTP client found. Please register Psr\Http\Client\ClientInterface in your container dependencies.'
            ),
        };

        // 2. Resolve Request Factory (Container -> Diactoros Fallback)
        $requestFactory = match (true) {
            $container->has(RequestFactoryInterface::class) => $container->get(RequestFactoryInterface::class),
            class_exists(DiactorosRequestFactory::class)     => new DiactorosRequestFactory(),
            default => throw new RuntimeException(
                'No PSR-17 RequestFactory found. Please register Psr\Http\Message\RequestFactoryInterface in your container dependencies.'
            ),
        };

        // 3. Resolve Stream Factory (Container -> Diactoros Fallback)
        $streamFactory = match (true) {
            $container->has(StreamFactoryInterface::class) => $container->get(StreamFactoryInterface::class),
            class_exists(DiactorosStreamFactory::class)     => new DiactorosStreamFactory(),
            default => throw new RuntimeException(
                'No PSR-17 StreamFactory found. Please register Psr\Http\Message\StreamFactoryInterface in your container dependencies.'
            ),
        };

        return new ReCaptchaV3Validator(
            $httpClient,
            $requestFactory,
            $streamFactory,
            $config['secret_key'] ?? '',
            (float) ($config['score_threshold'] ?? 0.5)
        );
    }
}