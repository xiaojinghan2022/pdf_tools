<?php

namespace OCA\PdfTools\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\IConfig;
use OCP\IRequest;

class SettingsController extends Controller
{
    public function __construct(
        string $appName,
        IRequest $request,
        private IConfig $config,
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
            return new JSONResponse(
                [
                    'success' => false,
                    'error' => 'Stirling-PDF URL is required.',
                ],
                400
            );
        }

        if (!filter_var($stirlingUrl, FILTER_VALIDATE_URL)) {
            return new JSONResponse(
                [
                    'success' => false,
                    'error' => 'Invalid Stirling-PDF URL.',
                ],
                400
            );
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
}