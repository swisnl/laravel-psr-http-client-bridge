<?php

declare(strict_types=1);

namespace Swis\Laravel\Bridge\PsrHttpClient;

use Http\Client\HttpClient as ClientInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Client implements ClientInterface
{
    /**
     * @var callable
     */
    protected $pendingRequestFactory;

    public function __construct(callable $pendingRequestFactory = null)
    {
        $this->pendingRequestFactory = $pendingRequestFactory ?? static function (): PendingRequest {
            return Http::withOptions([]);
        };
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return call_user_func($this->pendingRequestFactory)
            ->withHeaders($request->getHeaders())
            ->send($request->getMethod(), (string) $request->getUri(), ['body' => $request->getBody()])
            ->toPsrResponse();
    }
}
