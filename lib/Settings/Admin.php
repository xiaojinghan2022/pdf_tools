<?php

namespace OCA\PdfTools\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\Settings\ISettings;
use OCP\Util;

class Admin implements ISettings
{
    public function __construct(
        private IConfig $config,
    ) {}

    public function getForm(): TemplateResponse
    {
        Util::addScript('pdf_tools', 'settings');
        return new TemplateResponse(
            'pdf_tools',
            'settings/admin',
            [
                'stirlingUrl' => $this->config->getAppValue('pdf_tools', 'stirling_url', ''),
                'stirlingApiKey' => $this->config->getAppValue('pdf_tools', 'stirling_api_key', ''),
            ]
        );
    }
    public function getSection(): string
    {
        return 'pdf_tools';
    }
    public function getPriority(): int
    {
        return 5;
    }
}

