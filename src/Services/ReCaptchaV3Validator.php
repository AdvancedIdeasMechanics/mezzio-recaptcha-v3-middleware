<?php

declare(strict_types=1);

namespace AdvancedIdeasMechanics\MezzioReCaptchaV3\Services;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class ReCaptchaV3Validator
{
    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    public function __construct(
        private ClientInterface $httpClient,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
        private string $secretKey,
        private float $scoreThreshold = 0.5
    ) {}

    public function verify(string $token, ?string $expectedAction = null, ?string $userIp = null): bool
    {
        if (empty($token)) {
            return false;
        }

        $postData = http_build_query([
            'secret'   => $this->secretKey,
            'response' => $token,
            'remoteip' => $userIp,
        ]);

        $request = $this->requestFactory->createRequest('POST', self::VERIFY_URL)
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withBody($this->streamFactory->createStream($postData));

        try {
            $response = $this->httpClient->sendRequest($request);
            $data = json_decode((string) $response->getBody(), true);

            if (!isset($data['success']) || $data['success'] !== true) {
                return false;
            }

            // Verify action if provided
            if ($expectedAction !== null && ($data['action'] ?? null) !== $expectedAction) {
                return false;
            }

            // Check if human score passes the defined threshold
            return ($data['score'] ?? 0.0) >= $this->scoreThreshold;
        } catch (\Throwable $e) {
            return false;
        }
    }
}