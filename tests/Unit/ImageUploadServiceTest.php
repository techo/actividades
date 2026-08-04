<?php

namespace Tests\Unit;

use App\Services\ImageUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Regresión de C-4: subida de archivos arbitrarios → RCE / stored XSS.
 * ImageUploadService::store() es el backstop central: debe rechazar tipos peligrosos
 * y archivos demasiado grandes para TODOS los consumidores.
 *
 * No usa base de datos (solo el filesystem fake).
 */
class ImageUploadServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
    }

    /** @test */
    public function rechaza_archivos_php()
    {
        $this->expectException(ValidationException::class);
        ImageUploadService::store(
            UploadedFile::fake()->create('shell.php', 10, 'application/x-php'),
            'public/test'
        );
    }

    /** @test */
    public function rechaza_svg_por_riesgo_de_xss()
    {
        $this->expectException(ValidationException::class);
        ImageUploadService::store(
            UploadedFile::fake()->create('x.svg', 10, 'image/svg+xml'),
            'public/test'
        );
    }

    /** @test */
    public function rechaza_html()
    {
        $this->expectException(ValidationException::class);
        ImageUploadService::store(
            UploadedFile::fake()->create('x.html', 10, 'text/html'),
            'public/test'
        );
    }

    /** @test */
    public function rechaza_archivos_demasiado_grandes()
    {
        $this->expectException(ValidationException::class);
        // 11 MB > límite de 10 MB.
        ImageUploadService::store(
            UploadedFile::fake()->create('grande.pdf', 11 * 1024, 'application/pdf'),
            'public/test'
        );
    }

    /** @test */
    public function acepta_una_imagen_valida()
    {
        $path = ImageUploadService::store(
            UploadedFile::fake()->image('foto.jpg', 100, 100),
            'public/test'
        );

        $this->assertNotEmpty($path);
        $this->assertStringEndsWith('.jpg', $path);
        Storage::assertExists($path);
    }

    /** @test */
    public function acepta_un_pdf_valido()
    {
        $path = ImageUploadService::store(
            UploadedFile::fake()->create('doc.pdf', 50, 'application/pdf'),
            'public/test'
        );

        $this->assertNotEmpty($path);
        Storage::assertExists($path);
    }
}
