<?php

namespace OCA\PdfTools\Service;

use OCA\PdfTools\AppInfo\Application;
use OCP\Http\Client\IClientService;
use OCP\IAppConfig;

class StirlingClient {
    /**
     * A mapping between frontend values ​​and backend values.
     * 
     * 'key' is the frontend value, value is the backend value,
     * sensitive indicates whether it is sensitive information,
     * allowEmpty indicates whether it can be empty,
     * valueType indicates the type of value and use filter_var to filter the value,
     * selectOptions indicates the available options for 'select' valueType.
     * 
     * sensitive, allowEmpty need boolean,
     * sensitive default true, allowEmpty default false
     * valueType can use [boolean, bool, int, float, url, email, ip, mac, domain](from FILTER_VALIDATE_*) or [string, select](custom),
     * when valueType is 'select', 'selectOptions' must be provided,
     * 'string' is the default valueType,
     * which means filter_var is not used.
     */
    public const CONFIG_MAP = [
            'stirlingUrl' => ['key' => 'stirling_url', 'sensitive' => false, 'allowEmpty' => false, 'valueType' => 'url'],
            'stirlingApiKey' => ['key' => 'stirling_api_key', 'sensitive' => true, 'allowEmpty' => true, 'valueType' => 'string'],
            'serverProxyAuthUser' => ['key' => 'server_proxy_auth_user', 'sensitive' => true, 'allowEmpty' => true, 'valueType' => 'string'],
            'serverProxyAuthPass' => ['key' => 'server_proxy_auth_pass', 'sensitive' => true, 'allowEmpty' => true, 'valueType' => 'string'],
            'serverProxyAuthToken' => ['key' => 'server_proxy_auth_token', 'sensitive' => true, 'allowEmpty' => true, 'valueType' => 'string'],
            'serverProxyAuthType' => ['key' => 'server_proxy_auth_type', 'sensitive' => false, 'allowEmpty' => false, 'valueType' => 'select', 'selectOptions' => ['none', 'basic', 'bearer']],
    ];

    public function __construct(
        private IAppConfig $appConfig,
        private IClientService $clientService,
    ) {}

    /**
     * Validate field compliance
     */
    public static function validateFields(array $fields = []): array {
        foreach ($fields as $name => $value) {
            if (!isset(self::CONFIG_MAP[$name])) {
                throw new \InvalidArgumentException("Unknown field $name");
            }
            $entry = self::CONFIG_MAP[$name];
            if (!($entry['allowEmpty'] ?? false) && ($value === '' || $value === null)) {
                throw new \InvalidArgumentException("Field $name cannot be empty");
            }
            $valueType = strtolower($entry['valueType'] ?? 'string');
            switch ($valueType) {
                case 'select':
                    if (!in_array($value, $entry['selectOptions'] ?? [], true)) {
                        throw new \InvalidArgumentException("Invalid $name type");
                    }
                    break;
                case 'string': break;
                default:
                    if (!is_scalar($value)) {
                        throw new \InvalidArgumentException("Field $name must be a scalar");
                    }
                    $constName = 'FILTER_VALIDATE_' . strtoupper($valueType);
                    if (!defined($constName)) {
                        throw new \Exception("Field $valueType is not a defined type");
                    }
                    $result = filter_var($value, constant($constName), FILTER_NULL_ON_FAILURE);
                    if ($result === null) {
                        throw new \InvalidArgumentException("Field $name is not a valid $valueType");
                    }
                    if ($valueType === 'url') {
                        $scheme = parse_url($value, PHP_URL_SCHEME);
                        if (!in_array(strtolower($scheme), ['http', 'https'], true)) {
                            throw new \InvalidArgumentException('Only HTTP(S) allowed');
                        }
                    }
                    $fields[$name] = ($valueType === 'boolean' || $valueType === 'bool')
                        ? ($result ? 'true' : 'false')
                        : $result;
                    break;
            }
        }
        return $fields;
    }

    /**
     * Build Http Headers
     */
    private function buildHeaders(array $options = []): array {
        $type = ($options['serverProxyAuthType'] ?? '') === '' ? $this->appConfig->getValueString(Application::APP_ID, 'server_proxy_auth_type', 'none') : $options['serverProxyAuthType'];
        $headers = [];

        switch ($type) {
            case 'basic':
                $user = ($options['serverProxyAuthUser'] ?? '') === '' ? $this->appConfig->getValueString(Application::APP_ID, 'server_proxy_auth_user', '') : $options['serverProxyAuthUser'];
                $pass = ($options['serverProxyAuthPass'] ?? '') === '' ? $this->appConfig->getValueString(Application::APP_ID, 'server_proxy_auth_pass', '') : $options['serverProxyAuthPass'];
                if ($user !== '' && $pass !== '') {
                    $headers['Authorization'] = 'Basic ' . base64_encode("$user:$pass");
                }
                break;
            case 'bearer':
                $token = ($options['serverProxyAuthToken'] ?? '') === '' ? $this->appConfig->getValueString(Application::APP_ID, 'server_proxy_auth_token', '') : $options['serverProxyAuthToken'];
                if ($token !== '') {
                    $headers['Authorization'] = "Bearer $token";
                }
                break;
            case 'none':
            default:
                break;
        }
        $apiKey = ($options['stirlingApiKey'] ?? '') === '' ? $this->appConfig->getValueString(Application::APP_ID, 'stirling_api_key', '') : $options['stirlingApiKey'];
        if ($apiKey !== '') {
            $headers['X-API-KEY'] = $apiKey;
        }
        return $headers;
    }

    /**
     * Test Url Connect
     */
    public function testConnection(array $fields = []): void {
        $fields = self::validateFields($fields);
        if (!isset($fields['stirlingUrl']) || $fields['stirlingUrl'] === '') {
            throw new \InvalidArgumentException('Url is required');
        }
        $client = $this->clientService->newClient();
        $response = $client->get(rtrim($fields['stirlingUrl'], '/') . '/api/v1/info/status', [
            'headers' => $this->buildHeaders($fields),
            'throw' => false,
        ]);
        $status = $response->getStatusCode();
        if ($status === 200) { return; }

        $body = json_decode((string) $response->getBody(), true);
        $error = $body['error'] ?? "HTTP $status";
        throw new \RuntimeException($error);
    }
    /**
     * Test API Key availability
     */
    public function testApiKey(array $fields = []): void {
        $fields = self::validateFields($fields);
        if (!isset($fields['stirlingUrl']) || $fields['stirlingUrl'] === '') {
            throw new \InvalidArgumentException('Url is required');
        }
        if (($fields['stirlingApiKey'] ?? '') === '') {
            $fields['stirlingApiKey'] = $this->appConfig->getValueString(Application::APP_ID, 'stirling_api_key', '');
        }
        if ($fields['stirlingApiKey'] === '') { throw new \InvalidArgumentException('API Key is empty'); }
        $client = $this->clientService->newClient();
        $response = $client->get(rtrim($fields['stirlingUrl'], '/') . '/api/v1/info/health', [
            'headers' => $this->buildHeaders($fields),
            'throw' => false,
        ]);
        $status = $response->getStatusCode();
        if ($status === 200) { return; }

        $body = json_decode((string) $response->getBody(), true);
        $error = $body['error'] ?? "HTTP $status";
        throw new \RuntimeException($error);
    }
    /**
     * Delete Config Value
     */
    public function deleteConfigValue(array $clearValue = []): void {
        if (empty($clearValue)) {
            throw new \InvalidArgumentException('Invalid Value');
        }
        foreach ($clearValue as $name) {
            if (!isset(self::CONFIG_MAP[$name])) {
                throw new \InvalidArgumentException("Unknown field $name");
            }
            $this->appConfig->deleteKey(Application::APP_ID, self::CONFIG_MAP[$name]['key']);
        }
    }
}