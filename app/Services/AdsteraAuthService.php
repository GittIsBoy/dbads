<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Arr;

class AdsteraAuthService
{
    protected Client $http;
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('adstera.base_url');
        $base = rtrim($this->baseUrl, '/') . '/';
        $this->http = new Client(['base_uri' => $base, 'timeout' => 10]);
    }

    /**
     * Generic request helper using X-API-Key header as required by Adstera.
     */
    public function request(string $method, string $path, array $options = []): array
    {
        $headers = ['Accept' => 'application/json', 'X-API-Key' => config('adstera.api_key')];
        $options['headers'] = isset($options['headers']) ? array_merge($options['headers'], $headers) : $headers;

        // ensure we don't use a leading slash which would override base_uri path
        $path = ltrim($path, '/');

        $response = $this->http->request($method, $path, $options);
        return json_decode((string)$response->getBody(), true) ?? [];
    }

    /**
     * Verify that configured API key works by calling a simple endpoint.
     * Returns response array on success or throws exception.
     */
    public function verifyKey(): array
    {
        // Try a lightweight public endpoint; the OpenAPI shows /placements.{format}
        $path = '/placements.json';
        return $this->request('GET', $path);
    }

    public function refreshToken(string $refreshToken): array
    {
        // Not applicable for API key flow; keep for compatibility but try to call refresh endpoint if configured
        $refreshPath = config('adstera.endpoints.refresh');
        if (empty($refreshPath)) {
            throw new \RuntimeException('Refresh endpoint not configured for Adstera API key flow.');
        }

        $response = $this->http->post($refreshPath, [
            'json' => ['refresh_token' => $refreshToken],
            'headers' => ['Accept' => 'application/json', 'X-API-Key' => config('adstera.api_key')],
        ]);

        return json_decode((string)$response->getBody(), true) ?? [];
    }
}
