<?php

declare(strict_types=1);

namespace AdvancedIdeasMechanics\MezzioReCaptchaV3\Middleware;

use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ReCaptchaTemplateMiddleware implements MiddlewareInterface
{
    public function __construct(
        private array $recaptchaConfig,
        private ?TemplateRendererInterface $template = null
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $siteKey = $this->recaptchaConfig['site_key'] ?? null;

        // Only inject if both siteKey and a template engine exist
        if ($siteKey !== null && $this->template !== null) {
            $this->template->addDefaultParam(
                TemplateRendererInterface::TEMPLATE_ALL,
                'recaptcha_site_key',
                $siteKey
            );
        }

        return $handler->handle($request);
    }
}