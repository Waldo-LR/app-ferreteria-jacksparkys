<?php

namespace Tests\Feature\Http\Middleware;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ForceUtf8ResponseTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_textual_response_repairs_common_mojibake_and_normalizes_charset(): void
    {
        Route::get('/test-utf8-text', fn () => response(
            'Ã³, Ã¡, Ã±, Ã©, Ã­, Ãº y Ã¼',
            200,
            ['Content-Type' => 'text/html; charset=ISO-8859-1'],
        ));

        $response = $this->get('/test-utf8-text');

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/html; charset=UTF-8')
            ->assertSee('ó, á, ñ, é, í, ú y ü');
    }

    public function test_textual_response_preserves_valid_utf8_text(): void
    {
        Route::get('/test-valid-utf8', fn () => response(
            'Español válido: áéíóúñ¿¡€',
            200,
            ['Content-Type' => 'text/html; charset=UTF-8'],
        ));

        $response = $this->get('/test-valid-utf8');

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/html; charset=UTF-8')
            ->assertSee('Español válido: áéíóúñ¿¡€');
    }

    public function test_binary_response_remains_unchanged(): void
    {
        $binaryContent = "%PDF-1.4\n\xC3\x83\xC2\xB3";

        Route::get('/test-utf8-binary', fn () => response(
            $binaryContent,
            200,
            ['Content-Type' => 'application/pdf'],
        ));

        $response = $this->get('/test-utf8-binary');

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertContent($binaryContent);
    }
}
