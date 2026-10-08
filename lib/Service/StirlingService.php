<?php

namespace OCA\PdfTools\Service;

use OCP\Http\Client\IClientService;
use OCP\Files\File;

class StirlingService
{
    public function __construct(
        private IClientService $clientService,
    ) {
    }

    public function compress(
        File $file,
        string $stirlingUrl,
        ?string $apiKey = null,
    ): string {
        $client = $this->clientService->newClient();

        $headers = [];

        if ($apiKey) {
            $headers['X-API-KEY'] = $apiKey;
        }

        $response = $client->post(
            rtrim($stirlingUrl, '/') . '/api/v1/misc/compress-pdf',
            [
                'headers' => $headers,
                'multipart' => [
                    [
                        'name' => 'fileInput',
                        'contents' => $file->fopen('r'),
                        'filename' => $file->getName(),
                    ],
                ],
                'timeout' => 300,
            ]
        );

        return $response->getBody();
    }
}