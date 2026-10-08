<?php

namespace OCA\PdfTools\Controller;

use OCA\PdfTools\Service\StirlingClient;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IConfig;
use OCP\IRequest;

class SettingsController extends Controller
{
    public function __construct(
        string $appName,
        IRequest $request,
        private IConfig $config,
        private StirlingClient $stirlingClient,
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

    public function testConnection(): JSONResponse
    {
        try {
            if ($this->stirlingClient->testConnection()) {
                return new JSONResponse([
                    'success' => true,
                    'status' => 'UP',
                ]);
            }

            return new JSONResponse([
                'success' => false,
                'error' => 'Stirling-PDF did not report status UP.',
            ], 502);

        } catch (\Throwable $e) {
            return new JSONResponse([
                'success' => false,
                'error' => $e->getMessage(),
            ], 502);
        }
    }
}