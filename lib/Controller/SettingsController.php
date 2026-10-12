<?php
declare(strict_types=1);
namespace OCA\PdfTools\Controller;

use OCA\PdfTools\Service\StirlingClient;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IAppConfig;
use OCP\IRequest;

class SettingsController extends Controller {

    public function __construct(
        string $appName,
        IRequest $request,
        private IAppConfig $appConfig,
        private StirlingClient $stirlingClient,
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
        try {
            $this->stirlingClient->testConnection($stirlingUrl);
            return new DataResponse(['status' => 'ok', 'message' => 'Url Connection successful']);
        } catch (\InvalidArgumentException $e) {
            return new DataResponse(['status' => 'error', 'message' => $e->getMessage()], 400);
        } catch (\RuntimeException $e) {
            return new DataResponse(['status' => 'error', 'message' => $e->getMessage()], 401);
        } catch (\Exception $e) {
            return new DataResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function testApiKey(string $stirlingUrl, ?string $stirlingApiKey = null): DataResponse {
        try {
            $this->stirlingClient->testApiKey($stirlingUrl, $stirlingApiKey);
            return new DataResponse(['status' => 'ok', 'message' => 'API Key available']);
        } catch (\InvalidArgumentException $e) {
            return new DataResponse(['status' => 'error', 'message' => $e->getMessage()], 400);
        } catch (\RuntimeException $e) {
            return new DataResponse(['status' => 'error', 'message' => $e->getMessage()], 401);
        } catch (\Exception $e) {
            return new DataResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    public function deleteApiKey(): DataResponse {
        try {
            $this->appConfig->deleteKey($this->appName, 'stirling_api_key');
            return new DataResponse(['status' => 'ok']);
        } catch (\Exception $e) {
            return new DataResponse(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }
}
