<?php

declare(strict_types=1);

namespace AdvancedIdeasMechanics\MezzioReCaptchaV3;

use AdvancedIdeasMechanics\MezzioReCaptchaV3\Middleware\ReCaptchaMiddleware;
use AdvancedIdeasMechanics\MezzioReCaptchaV3\Middleware\ReCaptchaMiddlewareFactory;
use AdvancedIdeasMechanics\MezzioReCaptchaV3\Services\ReCaptchaV3Validator;
use AdvancedIdeasMechanics\MezzioReCaptchaV3\Services\ReCaptchaV3ValidatorFactory;
use AdvancedIdeasMechanics\MezzioReCaptchaV3\View\Helper\ReCaptchaHelper;
use AdvancedIdeasMechanics\MezzioReCaptchaV3\View\Helper\ReCaptchaHelperFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'view_helper' => $this->getViewHelpers(),
            'recaptcha' => $this->getReCaptchaConfig(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'factories' => [
                ReCaptchaV3Validator::class => ReCaptchaV3ValidatorFactory::class,
                ReCaptchaMiddleware::class => ReCaptchaMiddlewareFactory::class,
                ReCaptchaHelper::class                => ReCaptchaHelperFactory::class,
            ],
        ];
    }

    public function getViewHelpers(): array
    {
        return [
            'factories' => [
                'recaptcha' => ReCaptchaHelperFactory::class,
            ],
        ];
    }

    public function getReCaptchaConfig(): array
    {
        return [
            'site_key' => '',
            'secret_key' => '',
            'score_threshold' => 0.5,
            'default_action' => 'submit',
        ];
    }
}