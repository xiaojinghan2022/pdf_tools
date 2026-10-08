<?php

namespace OCA\PdfTools\AppInfo;

use OCP\AppFramework\App;

class Application extends App
{
    public const APP_ID = 'pdf_tools';

    public function __construct(array $urlParams = [])
    {
        parent::__construct(self::APP_ID, $urlParams);
    }
}