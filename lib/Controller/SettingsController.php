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
            'hasServerProxyAuthUser' => $this->appConfig->getValueString($this->appName, 'server_proxy_auth_user', '') !== '',
            'hasServerProxyAuthPass' => $this->appConfig->getValueString($this->appName, 'server_proxy_auth_pass', '') !== '',
            'hasServerProxyAuthToken' => $this->appConfig->getValueString($this->appName, 'server_proxy_auth_token', '') !== '',
            'serverProxyAuthType' => $this->appConfig->getValueString($this->appName, 'server_proxy_auth_type', 'none'),
        ]);
    }
    
    public function save(array $fields = []): DataResponse {
        try {
            $fields = StirlingClient::validateFields($fields);
            if (!isset($fields['stirlingUrl']) || $fields['stirlingUrl'] === '') {
                return new DataResponse(['status' => 'error', 'message' => 'Url is required'], 400);
            }
        } catch (\InvalidArgumentException $e) {
            return new DataResponse(['status' => 'error', 'message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return new DataResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
        if (isset($fields['serverProxyAuthType'])) {
            $newType = $fields['serverProxyAuthType'];
            $oldType = $this->appConfig->getValueString($this->appName, 'server_proxy_auth_type', 'none');
            if ($newType !== $oldType) {
                $this->appConfig->deleteKey($this->appName, 'server_proxy_auth_user');
                $this->appConfig->deleteKey($this->appName, 'server_proxy_auth_pass');
                $this->appConfig->deleteKey($this->appName, 'server_proxy_auth_token');
            }
        }
        foreach ($fields as $name => $value) {
            $entry = StirlingClient::CONFIG_MAP[$name];
            if ($value === '' || $value === null) continue;
            $this->appConfig->setValueString($this->appName, $entry['key'], (string) $value, sensitive: $entry['sensitive'] ?? true);
        }
        return new DataResponse(['status' => 'ok']);
    }

    public function testConnection(array $fields = []): DataResponse {
        try {
            $this->stirlingClient->testConnection($fields);
            return new DataResponse(['status' => 'ok', 'message' => 'Url Connection successful']);
        } catch (\InvalidArgumentException $e) {
            return new DataResponse(['status' => 'error', 'message' => $e->getMessage()], 400);
        } catch (\RuntimeException $e) {
            return new DataResponse(['status' => 'error', 'message' => $e->getMessage()], 401);
        } catch (\Exception $e) {
            return new DataResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function testApiKey(array $fields = []): DataResponse {
        try {
            $this->stirlingClient->testApiKey($fields);
            return new DataResponse(['status' => 'ok', 'message' => 'API Key available']);
        } catch (\InvalidArgumentException $e) {
            return new DataResponse(['status' => 'error', 'message' => $e->getMessage()], 400);
        } catch (\RuntimeException $e) {
            return new DataResponse(['status' => 'error', 'message' => $e->getMessage()], 401);
        } catch (\Exception $e) {
            return new DataResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    public function deleteConfigValue(array $clearValue = []): DataResponse {
        try {
            $this->stirlingClient->deleteConfigValue($clearValue);
            return new DataResponse(['status' => 'ok']);
        } catch (\InvalidArgumentException $e) {
            return new DataResponse(['status' => 'error', 'message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return new DataResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
