<?php

namespace OCA\PdfTools\Settings;

use OCA\PdfTools\AppInfo\Application;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\Settings\ISettings;
use OCP\Util;
use Override;

class Admin implements ISettings {

    #[Override]
    public function getForm(): TemplateResponse
    {
        Util::addScript(Application::APP_ID, 'admin-settings');
        return new TemplateResponse(Application::APP_ID, 'settings/admin-settings', [], 'blank');
    }

    #[Override]
    public function getSection(): string
    {
        return Application::APP_ID;
    }

    #[Override]
    public function getPriority(): int
    {
        return 10;
    }
}
