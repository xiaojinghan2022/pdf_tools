<?php

namespace OCA\PdfTools\Settings;

use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\IIconSection;
use Override;

class AdminSection implements IIconSection {

    public function __construct(
        private IURLGenerator $urlGenerator,
        private IL10N $l,
    ) {}

    #[Override]
    public function getID(): string
    {
        return 'pdf_tools';
    }

    #[Override]
    public function getName(): string
    {
        return $this->l->t('PDF Tools');
    }

    #[Override]
    public function getPriority(): int
    {
        return 75;
    }

    #[Override]
    public function getIcon(): string
    {
        return $this->urlGenerator->imagePath('pdf_tools', 'app.svg');
    }
}