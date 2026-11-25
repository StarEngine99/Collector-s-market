<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TrustedProxySchemeTest extends TestCase
{
    public function test_asset_urls_follow_the_forwarded_https_scheme(): void
    {
        Route::get('/_test/proxy-asset-url', fn () => asset('product-images/41'));

        $this->withHeaders([
            'X-Forwarded-Proto' => 'https',
            'X-Forwarded-Host' => 'shop.example',
        ])->get('/_test/proxy-asset-url')
            ->assertOk()
            ->assertSeeText('https://shop.example/product-images/41');
    }
}
