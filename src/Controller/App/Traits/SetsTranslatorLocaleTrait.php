<?php

namespace App\Controller\App\Traits;

use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

trait SetsTranslatorLocaleTrait
{
    protected function setTranslatorLocale(TranslatorInterface $translator, string $locale): void
    {
        if ($translator instanceof LocaleAwareInterface) {
            $translator->setLocale($locale);
        }
    }
}
