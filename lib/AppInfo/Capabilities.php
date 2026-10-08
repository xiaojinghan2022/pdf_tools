<?php

namespace OCA\PdfTools\AppInfo;

use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Capabilities\ICapability;

class Capabilities implements ICapability
{
    public function __construct(
        private IL10N $l10n,
        private IURLGenerator $urlGenerator,
    ) {
    }

    public function getCapabilities(): array
    {
        return [
            'client_integration' => [
                Application::APP_ID => [
                    'version' => 0.1,

                    'context-menu' => [
                        [
                            'name' => $this->l10n->t('Compress PDF'),
                            'url' => '/ocs/v2.php/apps/pdf_tools/api/v1/pdf/compress/{fileId}',
                            'method' => 'POST',
                            'mimetype_filters' => 'application/pdf',
                            'icon' => $this->urlGenerator->imagePath(
                                Application::APP_ID,
                                'client_integration/compress.svg'
                            ),
                        ],
                    ],
                ],
            ],
        ];
    }
}