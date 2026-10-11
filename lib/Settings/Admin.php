<?php

namespace OCA\PdfTools\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\Settings\ISettings;
use OCP\Util;
use Override;

class Admin implements ISettings {

    #[Override]
    public function getForm(): TemplateResponse
    {
        Util::addScript('pdf_tools', 'admin-settings');
        return new TemplateResponse('pdf_tools', 'settings/admin-settings', [], 'blank');
    }

    #[Override]
    public function getSection(): string
    {
        return 'pdf_tools';
    }

    #[Override]
    public function getPriority(): int
    {
        return 10;
    }
}
