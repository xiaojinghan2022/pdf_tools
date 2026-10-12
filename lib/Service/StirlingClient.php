<?php

namespace OCA\PdfTools\Service;

use OCA\PdfTools\AppInfo\Application;
use OCP\Http\Client\IClientService;
use OCP\IAppConfig;

class StirlingClient {
    public function __construct(
        private IAppConfig $appConfig,
        private IClientService $clientService,
    ) {}

    /**
     * Test Url Connect
     */
    public function testConnection(string $stirlingUrl): void {
        $stirlingUrl = trim($stirlingUrl);
        if ($stirlingUrl === '') {
            throw new \InvalidArgumentException('Url is empty');
        }
        $client = $this->clientService->newClient();
        $response = $client->get(rtrim($stirlingUrl, '/') . '/api/v1/info/status', [
            'throw' => false,
        ]);
        $status = $response->getStatusCode();
        if ($status === 200) { return; };

        $body = json_decode((string) $response->getBody(), true);
        $error = $body['error'] ?? "HTTP $status";
        throw new \RuntimeException($error);
    }
    /**
     * Test API Key availability
     */
    public function testApiKey(string $stirlingUrl, ?string $stirlingApiKey = null): void {
        $stirlingUrl = trim($stirlingUrl);
        if ($stirlingUrl === '') { throw new \InvalidArgumentException('Url is empty'); }
        if ($stirlingApiKey === null || $stirlingApiKey === '') {
            $stirlingApiKey = $this->appConfig->getValueString(Application::APP_ID, 'stirling_api_key', '');
        }
        if ($stirlingApiKey === '') { throw new \InvalidArgumentException('API Key is empty'); }

        $client = $this->clientService->newClient();
        $response = $client->get(rtrim($stirlingUrl, '/') . '/api/v1/info/health', [
            'headers' => ['X-API-KEY' => $stirlingApiKey],
            'throw' => false,
        ]);
        $status = $response->getStatusCode();
        if ($status === 200) { return; };
        $body = json_decode((string) $response->getBody(), true);
        $error = $body['error'] ?? "HTTP $status";
        throw new \RuntimeException($error);
    }
}