<?php

namespace OCA\PdfTools\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\Http\Client\IClientService;
use OCP\IAppConfig;
use OCP\IRequest;
use Override;

class SettingsController extends Controller {

    #[Override]
    public function __construct(
        string $appName,
        IRequest $request,
        private IAppConfig $appConfig,
        private IClientService $clientService,
    ) {
        parent::__construct($appName, $request);
    }
    
    public function save(string $url, string $apiKey): DataResponse {
        $this->appConfig->setValueString($this->appName, 'stirling_url', $url);
        $this->appConfig->setValueString($this->appName, 'stirling_api_key', $apiKey, sensitive: true);

        return new DataResponse(['status' => 'success']);
    }

    public function testConnection(string $url, string $apiKey): DataResponse {
        try {
            $client = $this->clientService->newClient();
            $response = $client->get(rtrim($url, '/') . '/api/v1/info/status', [
                'headers' => ['X-API-KEY' => $apiKey] // [reference:3]
            ]);
            if ($response->getStatusCode() === 200) {
                return new DataResponse(['status' => 'success', 'message' => 'Connection successful']);
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