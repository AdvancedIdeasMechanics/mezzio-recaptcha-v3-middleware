<?php

declare(strict_types=1);

namespace AdvancedIdeasMechanics\MezzioReCaptchaV3\View\Helper;

use Psr\Container\ContainerInterface;

class ReCaptchaHelperFactory
{
    public function __invoke(ContainerInterface $container): ReCaptchaHelper
    {
        $config = $container->get('config')['recaptcha'] ?? [];
        return new ReCaptchaHelper($config['site_key'] ?? '');
    }
}