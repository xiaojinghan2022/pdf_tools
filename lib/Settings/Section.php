<?php

namespace OCA\PdfTools\Settings;

use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\IIconSection;

class Section implements IIconSection
{
    public function __construct(
        private IL10N $l,
        private IURLGenerator $urlGenerator,
    ) {
    }

    public function getID(): string
    {
        return 'pdf_tools';
    }

    public function getName(): string
    {
        return $this->l->t('PDF Tools');
    }

    public function getPriority(): int
    {
        return 50;
    }

    public function getIcon(): string
    {
        return $this->urlGenerator->imagePath(
            'core',
            'actions/settings-dark.svg'
        );
    }
}