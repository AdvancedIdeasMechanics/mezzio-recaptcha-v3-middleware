<?php

declare(strict_types=1);

namespace AdvancedIdeasMechanics\MezzioReCaptchaV3\Services;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
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
        private float $scoreThreshold = 0.5,
        private string $defaultAction = 'login',
        private ?LoggerInterface $logger = null
    ) {}

    public function verify(string $token, ?string $expectedAction = null, ?string $userIp = null): bool
    {
        if (empty($token) || empty($this->projectId) || empty($this->apiKey)) {
            $this->logger?->warning('[reCAPTCHA] Missing token, project_id, or api_key configuration.');
            return false;
        }

        // Fall back to $this->defaultAction if expectedAction is null or empty
        $action = (!empty($expectedAction) && $expectedAction !== 'submit')
            ? $expectedAction
            : ($this->defaultAction ?: 'login');

        $url = sprintf(
            'https://recaptchaenterprise.googleapis.com/v1/projects/%s/assessments?key=%s',
            $this->projectId,
            $this->apiKey
        );

        $payload = [
            'event' => [
                'token'          => $token,
                'siteKey'        => $this->siteKey,
                'expectedAction' => $action,
            ],
        ];

        if ($userIp !== null) {
            $payload['event']['userIpAddress'] = $userIp;
        }

        try {
            $jsonPayload = json_encode($payload);

            // Log outgoing request
            $this->logger?->debug('[reCAPTCHA] Requesting Assessment', [
                'url'     => "https://recaptchaenterprise.googleapis.com/v1/projects/{$this->projectId}/assessments",
                'payload' => $payload,
            ]);

            $request = $this->requestFactory
                ->createRequest('POST', $url)
                ->withHeader('Content-Type', 'application/json')
                ->withBody($this->streamFactory->createStream($jsonPayload));

            $response = $this->httpClient->sendRequest($request);
            $responseBody = (string) $response->getBody();

            // Log incoming response
            $this->logger?->debug('[reCAPTCHA] Received Assessment Response', [
                'status_code' => $response->getStatusCode(),
                'response'    => json_decode($responseBody, true) ?? $responseBody,
            ]);

            if ($response->getStatusCode() !== 200) {
                $this->logger?->error('[reCAPTCHA] Enterprise API HTTP Error', [
                    'status_code' => $response->getStatusCode(),
                    'body'        => $responseBody,
                ]);
                return false;
            }

            $data = json_decode($responseBody, true);

            $isValidToken  = $data['tokenProperties']['valid'] ?? false;
            $invalidReason = $data['tokenProperties']['invalidReason'] ?? 'NONE';
            $actualAction  = $data['tokenProperties']['action'] ?? 'NONE';
            $actionMatch   = $actualAction === $action;
            $score         = (float) ($data['riskAnalysis']['score'] ?? 0.0);

            $passed = $isValidToken && $actionMatch && ($score >= $this->scoreThreshold);

            if (!$passed) {
                $this->logger?->warning('[reCAPTCHA] Verification Failed', [
                    'valid_token'    => $isValidToken,
                    'invalid_reason' => $invalidReason,
                    'expected_action'=> $action,
                    'actual_action'  => $actualAction,
                    'score'          => $score,
                    'threshold'      => $this->scoreThreshold,
                ]);
            } else {
                $this->logger?->info('[reCAPTCHA] Verification Passed', [
                    'score'  => $score,
                    'action' => $actualAction,
                ]);
            }

            return $passed;

        } catch (Throwable $e) {
            $this->logger?->error('[reCAPTCHA] Exception during verification', [
                'exception' => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);
            return false;
        }
    }
}