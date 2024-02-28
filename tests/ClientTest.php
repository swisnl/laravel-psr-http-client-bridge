<?php

use GuzzleHttp\Psr7\Request as PsrRequest;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Swis\Laravel\Bridge\PsrHttpClient\Client;

it('uses the provided request factory', function () {
    Http::fake(['*' => Http::response()]);

    $client = new Client(fn () => Http::withHeaders(['X-Foo' => 'Bar']));
    $client->sendRequest(new PsrRequest('GET', 'https://example.com'));

    Http::assertSent(function (Request $request) {
        return $request->method() === 'GET'
            && $request->url() === 'https://example.com'
            && $request->hasHeader('X-Foo', 'Bar');
    });
});

it('implements sendRequest', function () {
    Http::fake(['*' => Http::response('foo=baz', 404, ['X-Foo' => 'Baz'])]);

    $client = new Client();
    $response = $client->sendRequest(new PsrRequest('POST', 'https://example.com', ['X-Foo' => 'Bar'], 'foo=bar'));

    Http::assertSent(function (Request $request) {
        return $request->method() === 'POST'
            && $request->url() === 'https://example.com'
            && $request->hasHeader('X-Foo', 'Bar')
            && $request->body() === 'foo=bar';
    });

    expect((string) $response->getBody())->toBe('foo=baz')
        ->and($response->getStatusCode())->toBe(404)
        ->and($response->getHeader('X-Foo'))->toBe(['Baz']);
});

it('implements send', function () {
    Http::fake(['*' => Http::response('foo=baz', 404, ['X-Foo' => 'Baz'])]);

    $client = new Client();
    $response = $client->send(new PsrRequest('POST', 'https://example.com', ['X-Foo' => 'Bar'], 'foo=bar'));

    Http::assertSent(function (Request $request) {
        return $request->method() === 'POST'
            && $request->url() === 'https://example.com'
            && $request->hasHeader('X-Foo', 'Bar')
            && $request->body() === 'foo=bar';
    });

    expect((string) $response->getBody())->toBe('foo=baz')
        ->and($response->getStatusCode())->toBe(404)
        ->and($response->getHeader('X-Foo'))->toBe(['Baz']);
});

it('implements sendAsync', function () {
    Http::fake(['*' => Http::response('foo=baz', 404, ['X-Foo' => 'Baz'])]);

    $client = new Client();
    $response = $client->sendAsync(new PsrRequest('POST', 'https://example.com', ['X-Foo' => 'Bar'], 'foo=bar'))->wait();

    Http::assertSent(function (Request $request) {
        return $request->method() === 'POST'
            && $request->url() === 'https://example.com'
            && $request->hasHeader('X-Foo', 'Bar')
            && $request->body() === 'foo=bar';
    });

    expect((string) $response->getBody())->toBe('foo=baz')
        ->and($response->getStatusCode())->toBe(404)
        ->and($response->getHeader('X-Foo'))->toBe(['Baz']);
});

it('implements request', function () {
    Http::fake(['*' => Http::response('foo=baz', 404, ['X-Foo' => 'Baz'])]);

    $client = new Client();
    $response = $client->request('POST', 'https://example.com', ['headers' => ['X-Foo' => 'Bar'], 'body' => 'foo=bar']);

    Http::assertSent(function (Request $request) {
        return $request->method() === 'POST'
            && $request->url() === 'https://example.com'
            && $request->hasHeader('X-Foo', 'Bar')
            && $request->body() === 'foo=bar';
    });

    expect((string) $response->getBody())->toBe('foo=baz')
        ->and($response->getStatusCode())->toBe(404)
        ->and($response->getHeader('X-Foo'))->toBe(['Baz']);
});

it('implements requestAsync', function () {
    Http::fake(['*' => Http::response('foo=baz', 404, ['X-Foo' => 'Baz'])]);

    $client = new Client();
    $response = $client->requestAsync('POST', 'https://example.com', ['headers' => ['X-Foo' => 'Bar'], 'body' => 'foo=bar'])->wait();

    Http::assertSent(function (Request $request) {
        return $request->method() === 'POST'
            && $request->url() === 'https://example.com'
            && $request->hasHeader('X-Foo', 'Bar')
            && $request->body() === 'foo=bar';
    });

    expect((string) $response->getBody())->toBe('foo=baz')
        ->and($response->getStatusCode())->toBe(404)
        ->and($response->getHeader('X-Foo'))->toBe(['Baz']);
});
