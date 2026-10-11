<?php

namespace OCA\PdfTools\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\Http\Client\IClientService;
use OCP\IAppConfig;
use OCP\IRequest;

class SettingsController extends Controller {

    public function __construct(
        string $appName,
        IRequest $request,
        private IAppConfig $appConfig,
        private IClientService $clientService,
    ) {
        parent::__construct($appName, $request);
    }

    public function get(): DataResponse {
        return new DataResponse([
            'url' => $this->appConfig->getValueString($this->appName, 'stirling_url', ''),
            'hasApiKey' => $this->appConfig->getValueString($this->appName, 'stirling_api_key', '') !== '',
        ]);
    }
    
    public function save(string $stirlingUrl, ?string $stirlingApiKey = null): DataResponse {
        $this->appConfig->setValueString($this->appName, 'stirling_url', $stirlingUrl);
        if ($stirlingApiKey !== null && $stirlingApiKey !== '') {
            $this->appConfig->setValueString($this->appName, 'stirling_api_key', $stirlingApiKey, sensitive: true);
        }
        return new DataResponse(['status' => 'ok']);
    }

    public function testConnection(string $stirlingUrl, ?string $stirlingApiKey = null): DataResponse {
        try {
            $client = $this->clientService->newClient();
            $response = $client->get(rtrim($stirlingUrl, '/') . '/api/v1/info/status', [
                'headers' => ['X-API-KEY' => $stirlingApiKey] // [reference:3]
            ]);
            if ($response->getStatusCode() === 200) {
                return new DataResponse(['status' => 'ok', 'message' => 'Connection successful']);
            }
        } catch (\Exception $e) {
            return new DataResponse(
                ['status' => 'error', 'message' => $e->getMessage()],
                500
            );
        }
        return new DataResponse(['status' => 'error', 'message' => 'Unknown error'], 500);
    }
}