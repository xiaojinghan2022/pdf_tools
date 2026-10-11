<?php
declare(strict_types=1);
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
            'stirlingUrl' => $this->appConfig->getValueString($this->appName, 'stirling_url', ''),
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

    public function testConnection(string $stirlingUrl): DataResponse {
        $stirlingUrl = trim($stirlingUrl);
        if ($stirlingUrl === '') {
            return new DataResponse(['status' => 'error', 'message' => 'Url is empty'], 400);
        }
        try {
            $client = $this->clientService->newClient();
            $response = $client->get(rtrim($stirlingUrl, '/') . '/api/v1/info/status', []);
            if ($response->getStatusCode() === 200) {
                return new DataResponse(['status' => 'ok', 'message' => 'Url Connection successful']);
            }
        } catch (\Exception $e) {
            return new DataResponse(
                ['status' => 'error', 'message' => $e->getMessage()],
                500
            );
        }
        return new DataResponse(['status' => 'error', 'message' => 'Unknown error'], 500);
    }

    public function testApiKey(string $stirlingUrl, ?string $stirlingApiKey = null): DataResponse {
        $stirlingUrl = trim($stirlingUrl);
        $stirlingApiKey = trim($stirlingApiKey);
        if ($stirlingUrl === '') {
            return new DataResponse(['status' => 'error', 'message' => 'Url is empty'], 400);
        }
        if ($stirlingApiKey === null || $stirlingApiKey === '') {
            $stirlingApiKey = $this->appConfig->getValueString($this->appName, 'stirling_api_key', '');
        }
        if ($stirlingApiKey === '') {
            return new DataResponse(['status' => 'error', 'message' => 'ApiKey is empty'], 400);
        }
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
