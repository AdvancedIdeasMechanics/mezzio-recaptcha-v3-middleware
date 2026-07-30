<?php

declare(strict_types=1);

namespace AdvancedIdeasMechanics\MezzioReCaptchaV3\Middleware;

use AdvancedIdeasMechanics\MezzioReCaptchaV3\Services\ReCaptchaV3Validator;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ReCaptchaMiddleware implements MiddlewareInterface
{
    public function __construct(
        private ReCaptchaV3Validator $validator,
        private ResponseFactoryInterface $responseFactory,
        private ?string $expectedAction = null
    ) {}

    /**
     * Named constructor if users want to override the action per-route
     */
    public function withAction(string $action): self
    {
        $new = clone $this;
        $new->expectedAction = $action;
        return $new;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getMethod() === 'POST') {
            $parsedBody = $request->getParsedBody() ?? [];
            $token = $parsedBody['g-recaptcha-response'] ?? '';

            $serverParams = $request->getServerParams();
            $userIp = $serverParams['REMOTE_ADDR'] ?? null;

            $isValid = $this->validator->verify($token, $this->expectedAction, $userIp);

            if (!$isValid) {
                $response = $this->responseFactory->createResponse(400);
                $response->getBody()->write(json_encode([
                    'error' => 'recaptcha_failed',
                    'message' => 'reCAPTCHA verification failed or score was below threshold.',
                ]));

                return $response->withHeader('Content-Type', 'application/json');
            }
        }

        return $handler->handle($request);
    }
}