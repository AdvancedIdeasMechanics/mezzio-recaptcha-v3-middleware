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
use Psr\Log\LoggerInterface;
use RuntimeException;

class ReCaptchaV3ValidatorFactory
{
    public function __invoke(ContainerInterface $container): ReCaptchaV3Validator
    {
        $config = $container->get('config')['recaptcha'] ?? [];

        $httpClient = match (true) {
            $container->has(ClientInterface::class) => $container->get(ClientInterface::class),
            class_exists(GuzzleClient::class)       => new GuzzleClient(),
            default => throw new RuntimeException('No PSR-18 HTTP client found.'),
        };

        $requestFactory = match (true) {
            $container->has(RequestFactoryInterface::class) => $container->get(RequestFactoryInterface::class),
            class_exists(DiactorosRequestFactory::class)     => new DiactorosRequestFactory(),
            default => throw new RuntimeException('No PSR-17 RequestFactory found.'),
        };

        $streamFactory = match (true) {
            $container->has(StreamFactoryInterface::class) => $container->get(StreamFactoryInterface::class),
            class_exists(DiactorosStreamFactory::class)     => new DiactorosStreamFactory(),
            default => throw new RuntimeException('No PSR-17 StreamFactory found.'),
        };

        // Fetch PSR-3 logger if available in container
        $logger = $container->has(LoggerInterface::class)
            ? $container->get(LoggerInterface::class)
            : null;

        return new ReCaptchaV3Validator(
            $httpClient,
            $requestFactory,
            $streamFactory,
            (string) ($config['project_id'] ?? ''),
            (string) ($config['api_key'] ?? ''),
            (string) ($config['site_key'] ?? ''),
            (float) ($config['score_threshold'] ?? 0.5),
            (string) ($config['default_action'] ?? 'login'),
            $logger
        );
    }
}