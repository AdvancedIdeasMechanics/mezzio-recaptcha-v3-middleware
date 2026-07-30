<?php

declare(strict_types=1);

namespace AdvancedIdeasMechanics\MezzioReCaptchaV3\Services;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Throwable;

class ReCaptchaV3Validator
{
    public function __construct(
        private ClientInterface $httpClient,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
        private string $projectId,
        private string $apiKey,
        private string $siteKey,
        private float $scoreThreshold = 0.5
    ) {}

    public function verify(string $token, string $expectedAction = 'login', ?string $userIp = null): bool
    {
        if (empty($token) || empty($this->projectId) || empty($this->apiKey)) {
            return false;
        }

        // reCAPTCHA Enterprise Assessment URL
        $url = sprintf(
            'https://recaptchaenterprise.googleapis.com/v1/projects/%s/assessments?key=%s',
            $this->projectId,
            $this->apiKey
        );

        // Enterprise Assessment Request Body
        $payload = [
            'event' => [
                'token'   => $token,
                'siteKey' => $this->siteKey,
                'expectedAction' => $expectedAction,
            ],
        ];

        if ($userIp !== null) {
            $payload['event']['userIpAddress'] = $userIp;
        }

        try {
            $request = $this->requestFactory
                ->createRequest('POST', $url)
                ->withHeader('Content-Type', 'application/json');

            $body = $this->streamFactory->createStream(json_encode($payload));
            $request = $request->withBody($body);

            $response = $this->httpClient->sendRequest($request);

            if ($response->getStatusCode() !== 200) {
                return false;
            }

            $data = json_decode((string) $response->getBody(), true);

            // Enterprise Response Structure:
            // $data['tokenProperties']['valid']
            // $data['tokenProperties']['action']
            // $data['riskAnalysis']['score']
            $isValidToken = $data['tokenProperties']['valid'] ?? false;
            $actionMatch  = ($data['tokenProperties']['action'] ?? '') === $expectedAction;
            $score        = (float) ($data['riskAnalysis']['score'] ?? 0.0);

            return $isValidToken && $actionMatch && ($score >= $this->scoreThreshold);

        } catch (Throwable $e) {
            return false;
        }
    }
}