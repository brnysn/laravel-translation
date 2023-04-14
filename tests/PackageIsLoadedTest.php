<?php

namespace Brnysn\Translation\Tests;

use Brnysn\Translation\TranslationBindingsServiceProvider;
use Brnysn\Translation\TranslationServiceProvider;
use Orchestra\Testbench\TestCase;

class PackageIsLoadedTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            TranslationServiceProvider::class,
            TranslationBindingsServiceProvider::class,
        ];
    }

    /** @test */
    public function the_translation_pacakage_is_loaded()
    {
        $this->assertArrayHasKey(TranslationServiceProvider::class, app()->getLoadedProviders());
        $this->assertArrayHasKey(TranslationBindingsServiceProvider::class, app()->getLoadedProviders());
    }
}
