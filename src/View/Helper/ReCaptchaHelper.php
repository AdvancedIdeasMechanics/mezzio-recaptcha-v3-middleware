<?php

declare(strict_types=1);

namespace AdvancedIdeasMechanics\MezzioReCaptchaV3\View\Helper;

class ReCaptchaHelper
{
    public function __construct(private string $siteKey) {}

    /**
     * Calling $this->recaptcha() in a template returns $this or the site key directly
     */
    public function __invoke(): self
    {
        return $this;
    }

    public function getSiteKey(): string
    {
        return $this->siteKey;
    }

    /**
     * Generates the Google API script tag automatically
     */
    public function renderScript(): string
    {
        return sprintf(
            '<script src="https://www.google.com/recaptcha/api.js?render=%s"></script>',
            htmlspecialchars($this->siteKey, ENT_QUOTES, 'UTF-8')
        );
    }
}