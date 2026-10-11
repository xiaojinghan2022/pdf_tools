<?php
declare(strict_types=1);
namespace OCA\PdfTools\AppInfo;

use OCA\Files\Event\LoadAdditionalScriptsEvent;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\Util;
use Override;

class Application extends App implements IBootstrap
{
    public const APP_ID = 'pdf_tools';

    #[Override]
    public function __construct(array $urlParams = [])
    {
        parent::__construct(self::APP_ID, $urlParams);
    }

    #[Override]
    public function register(IRegistrationContext $context): void
    {

    }

    #[Override]
    public function boot(IBootContext $context): void
    {
        $context->injectFn(function (IEventDispatcher $dispatcher) {
            $dispatcher->addListener(
                LoadAdditionalScriptsEvent::class,
                function () {
                    Util::addInitScript(self::APP_ID, 'tools');
                }
            );
        });
    }
}