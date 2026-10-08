<?php

namespace OCA\PdfTools\Service;

use OCP\Files\File;
use OCP\Http\Client\IClientService;
use OCP\IConfig;
use RuntimeException;

class StirlingClient
{
    private const APP_ID = 'pdf_tools';

    public function __construct(
        private IConfig $config,
        private IClientService $clientService,
    ) {
    }

    private function getBaseUrl(): string
    {
        $url = trim(
            $this->config->getAppValue(
                self::APP_ID,
                'stirling_url',
                ''
            )
        );

        if ($url === '') {
            throw new RuntimeException(
                'Stirling-PDF URL is not configured.'
            );
        }

        return rtrim($url, '/');
    }

    private function getApiKey(): string
    {
        return trim(
            $this->config->getAppValue(
                self::APP_ID,
                'stirling_api_key',
                ''
            )
        );
    }

    private function getClient()
    {
        return $this->clientService->newClient();
    }

    private function getHeaders(): array
    {
        $headers = [];

        $apiKey = $this->getApiKey();

        if ($apiKey !== '') {
            $headers['X-API-KEY'] = $apiKey;
        }

        return $headers;
    }

    public function testConnection(): bool
    {
        $response = $this->getClient()->get(
            $this->getBaseUrl() . '/api/v1/info/status',
            [
                'timeout' => 120,
                'headers' => $this->getHeaders(),
            ]
        );

        if (
            $response->getStatusCode() < 200
            || $response->getStatusCode() >= 300
        ) {
            return false;
        }

        $data = json_decode(
            $response->getBody(),
            true
        );

        return is_array($data)
            && isset($data['status'])
            && strtoupper((string) $data['status']) === 'UP';
    }

    /**
     * Compress a PDF file using Stirling-PDF.
     *
     * @return string Processed PDF contents
     */
    public function compress(
        File $file,
        int $optimizeLevel = 5,
        bool $grayscale = false,
        bool $linearize = false,
        bool $lineArt = false,
    ): string {
        if ($optimizeLevel < 1 || $optimizeLevel > 9) {
            throw new RuntimeException(
                'Compression level must be between 1 and 9.'
            );
        }

        $response = $this->getClient()->post(
            $this->getBaseUrl() . '/api/v1/misc/compress-pdf',
            [
                'timeout' => 120,

                'headers' => $this->getHeaders(),

                'multipart' => [
                    [
                        'name' => 'fileInput',
                        'contents' => $file->getContent(),
                        'filename' => $file->getName(),
                    ],
                    [
                        'name' => 'optimizeLevel',
                        'contents' => (string) $optimizeLevel,
                    ],
                    [
                        'name' => 'grayscale',
                        'contents' => $grayscale ? 'true' : 'false',
                    ],
                    [
                        'name' => 'linearize',
                        'contents' => $linearize ? 'true' : 'false',
                    ],
                    [
                        'name' => 'lineArt',
                        'contents' => $lineArt ? 'true' : 'false',
                    ],
                ],
            ]
        );

        $statusCode = $response->getStatusCode();

        if ($statusCode < 200 || $statusCode >= 300) {
            throw new RuntimeException(
                sprintf(
                    'Stirling-PDF returned HTTP %d.',
                    $statusCode
                )
            );
        }

        return $response->getBody();
    }
}