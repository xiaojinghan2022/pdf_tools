<?php

namespace OCA\PdfTools\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\Http\Client\IClientService;
use OCP\IConfig;
use OCP\IRequest;

class SettingsController extends Controller
{
    public function __construct(
        string $appName,
        IRequest $request,
        private IConfig $config,
        private IClientService $clientService,
    ) {
        parent::__construct($appName, $request);
    }

    public function save(
        string $stirlingUrl,
        string $stirlingApiKey = '',
    ): JSONResponse {
        $stirlingUrl = trim($stirlingUrl);
        $stirlingApiKey = trim($stirlingApiKey);

        if ($stirlingUrl === '') {
            return new JSONResponse([
                'success' => false,
                'error' => 'Stirling-PDF URL is required.',
            ], 400);
        }

        if (!filter_var($stirlingUrl, FILTER_VALIDATE_URL)) {
            return new JSONResponse([
                'success' => false,
                'error' => 'Invalid Stirling-PDF URL.',
            ], 400);
        }

        $scheme = strtolower(
            (string) parse_url($stirlingUrl, PHP_URL_SCHEME)
        );

        if (!in_array($scheme, ['http', 'https'], true)) {
            return new JSONResponse([
                'success' => false,
                'error' => 'Only HTTP and HTTPS URLs are supported.',
            ], 400);
        }

        $this->config->setAppValue(
            'pdf_tools',
            'stirling_url',
            rtrim($stirlingUrl, '/')
        );

        $this->config->setAppValue(
            'pdf_tools',
            'stirling_api_key',
            $stirlingApiKey
        );

        return new JSONResponse([
            'success' => true,
        ]);
    }

    public function testConnection(
        string $stirlingUrl = '',
        string $stirlingApiKey = '',
    ): JSONResponse {
        $stirlingUrl = trim($stirlingUrl);

        if ($stirlingUrl === '') {
            $stirlingUrl = $this->config->getAppValue(
                'pdf_tools',
                'stirling_url',
                ''
            );
        }

        if ($stirlingApiKey === '') {
            $stirlingApiKey = $this->config->getAppValue(
                'pdf_tools',
                'stirling_api_key',
                ''
            );
        }

        if ($stirlingUrl === '') {
            return new JSONResponse([
                'success' => false,
                'error' => 'Stirling-PDF URL is not configured.',
            ], 400);
        }

        $scheme = strtolower(
            (string) parse_url($stirlingUrl, PHP_URL_SCHEME)
        );

        if (!in_array($scheme, ['http', 'https'], true)) {
            return new JSONResponse([
                'success' => false,
                'error' => 'Only HTTP and HTTPS URLs are supported.',
            ], 400);
        }

        try {
            $client = $this->clientService->newClient();

            $headers = [];

            if ($stirlingApiKey !== '') {
                $headers['X-API-KEY'] = $stirlingApiKey;
            }

            $response = $client->get(
                rtrim($stirlingUrl, '/') . '/api/v1/info/status',
                [
                    'headers' => $headers,
                    'timeout' => 10,
                ]
            );

            $statusCode = $response->getStatusCode();
            $body = $response->getBody();

            if ($statusCode < 200 || $statusCode >= 300) {
                return new JSONResponse([
                    'success' => false,
                    'error' => 'Stirling-PDF returned HTTP ' . $statusCode . '.',
                ], 502);
            }

            $data = json_decode($body, true);

            if (
                is_array($data)
                && isset($data['status'])
                && strtoupper((string) $data['status']) === 'UP'
            ) {
                return new JSONResponse([
                    'success' => true,
                    'status' => 'UP',
                ]);
            }

            return new JSONResponse([
                'success' => false,
                'error' => 'Stirling-PDF responded, but its status was not UP.',
                'response' => $data,
            ], 502);

        } catch (\Throwable $e) {
            return new JSONResponse([
                'success' => false,
                'error' => $e->getMessage(),
            ], 502);
        }
    }
}