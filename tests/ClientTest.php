<?php

declare(strict_types=1);

namespace Swis\Laravel\Bridge\PsrHttpClient\Tests;

use GrahamCampbell\TestBench\AbstractPackageTestCase;
use GuzzleHttp\Psr7\Request as PsrRequest;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Swis\Laravel\Bridge\PsrHttpClient\Client;

class ClientTest extends AbstractPackageTestCase
{
    /**
     * @test
     */
    public function itCanSendARequest(): void
    {
        Http::fake(['*' => Http::response('foo=baz', 404, ['X-Foo' => 'Baz'])]);

        $client = new Client();
        $response = $client->sendRequest(new PsrRequest('POST', 'https://example.com', ['X-Foo' => 'Bar'], 'foo=bar'));

        Http::assertSent(function (Request $request) {
            return $request->method() === 'POST' &&
                $request->url() === 'https://example.com' &&
                $request->hasHeader('X-Foo', 'Bar') &&
                $request->body() === 'foo=bar';
        });

        $this->assertEquals('foo=baz', (string) $response->getBody());
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals(['Baz'], $response->getHeader('X-Foo'));
    }

    /**
     * @test
     */
    public function itUsesTheProvidedRequestFactory(): void
    {
        Http::fake(['*' => Http::response()]);

        $client = new Client(fn () => Http::withHeaders(['X-Foo' => 'Bar']));
        $client->sendRequest(new PsrRequest('GET', 'https://example.com'));

        Http::assertSent(function (Request $request) {
            return $request->method() === 'GET' &&
                $request->url() === 'https://example.com' &&
                $request->hasHeader('X-Foo', 'Bar');
        });
    }
}
