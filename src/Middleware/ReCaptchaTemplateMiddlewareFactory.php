<?php

declare(strict_types=1);

namespace AdvancedIdeasMechanics\MezzioReCaptchaV3\Middleware;

use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

class ReCaptchaTemplateMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): ReCaptchaTemplateMiddleware
    {
        $config = $container->get('config')['recaptcha'] ?? [];

        // Safely retrieve template renderer if registered in Mezzio container
        $template = $container->has(TemplateRendererInterface::class)
            ? $container->get(TemplateRendererInterface::class)
            : null;

        return new ReCaptchaTemplateMiddleware($config, $template);
    }
}