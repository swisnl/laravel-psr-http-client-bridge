<?php

declare(strict_types=1);

namespace Swis\Laravel\Bridge\PsrHttpClient;

use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Psr\Http\Client\ClientInterface as PsrClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Client implements GuzzleClientInterface, PsrClientInterface
{
    /**
     * @var callable
     */
    protected $pendingRequestFactory;

    public function __construct(?callable $pendingRequestFactory = null)
    {
        $this->pendingRequestFactory = $pendingRequestFactory ?? static function (): PendingRequest {
            return Http::withOptions([]);
        };
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->newPendingRequest()
            ->withHeaders($request->getHeaders())
            ->send($request->getMethod(), (string) $request->getUri(), ['body' => $request->getBody()])
            ->toPsrResponse();
    }

    public function send(RequestInterface $request, array $options = []): ResponseInterface
    {
        return $this->newPendingRequest()
            ->withHeaders($request->getHeaders())
            ->send($request->getMethod(), (string) $request->getUri(), array_merge(['body' => $request->getBody()], $options))
            ->toPsrResponse();
    }

    public function sendAsync(RequestInterface $request, array $options = []): PromiseInterface
    {
        /** @var \GuzzleHttp\Promise\PromiseInterface */
        return $this->newPendingRequest()
            ->async()
            ->withHeaders($request->getHeaders())
            ->send($request->getMethod(), (string) $request->getUri(), array_merge(['body' => $request->getBody()], $options));
    }

    public function request(string $method, $uri, array $options = []): ResponseInterface
    {
        return $this->newPendingRequest()
            ->send($method, (string) $uri, $options)
            ->toPsrResponse();
    }

    public function requestAsync(string $method, $uri, array $options = []): PromiseInterface
    {
        /** @var \GuzzleHttp\Promise\PromiseInterface */
        return $this->newPendingRequest()
            ->async()
            ->send($method, (string) $uri, $options);
    }

    public function getConfig(?string $option = null)
    {
        $options = $this->newPendingRequest()->getOptions();

        return $option === null ? $options : ($options[$option] ?? null);
    }

    protected function newPendingRequest(): PendingRequest
    {
        return call_user_func($this->pendingRequestFactory);
    }
}
