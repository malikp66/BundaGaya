<?php

namespace Tests\Feature;

use App\Services\FileUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        Storage::fake('public');
    }

    public function test_valid_image_upload()
    {
        $fileUploadService = app(FileUploadService::class);
        
        $file = UploadedFile::fake()->image('test.jpg', 800, 600)->size(1024);
        
        $path = $fileUploadService->uploadImage($file, 'products');
        
        $this->assertNotEmpty($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_valid_png_upload()
    {
        $fileUploadService = app(FileUploadService::class);
        
        $file = UploadedFile::fake()->image('test.png', 800, 600)->size(1024);
        
        $path = $fileUploadService->uploadImage($file, 'products');
        
        $this->assertNotEmpty($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_valid_gif_upload()
    {
        $fileUploadService = app(FileUploadService::class);
        
        $file = UploadedFile::fake()->image('test.gif', 800, 600)->size(1024);
        
        $path = $fileUploadService->uploadImage($file, 'products');
        
        $this->assertNotEmpty($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_valid_webp_upload()
    {
        $fileUploadService = app(FileUploadService::class);
        
        $file = UploadedFile::fake()->image('test.webp', 800, 600)->size(1024);
        
        $path = $fileUploadService->uploadImage($file, 'products');
        
        $this->assertNotEmpty($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_invalid_file_type_rejected()
    {
        $fileUploadService = app(FileUploadService::class);
        
        $file = UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf');
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid file type');
        
        $fileUploadService->uploadImage($file, 'products');
    }

    public function test_executable_file_rejected()
    {
        $fileUploadService = app(FileUploadService::class);
        
        $file = UploadedFile::fake()->create('script.exe', 1024, 'application/x-msdownload');
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid file type');
        
        $fileUploadService->uploadImage($file, 'products');
    }

    public function test_file_exceeding_size_limit_rejected()
    {
        $fileUploadService = app(FileUploadService::class);
        
        $file = UploadedFile::fake()->image('large.jpg', 800, 600)->size(6000);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('File size exceeds maximum limit');
        
        $fileUploadService->uploadImage($file, 'products');
    }

    public function test_multiple_images_upload()
    {
        $fileUploadService = app(FileUploadService::class);
        
        $files = [
            UploadedFile::fake()->image('test1.jpg', 800, 600)->size(1024),
            UploadedFile::fake()->image('test2.png', 800, 600)->size(1024),
            UploadedFile::fake()->image('test3.gif', 800, 600)->size(1024),
        ];
        
        $paths = $fileUploadService->uploadMultipleImages($files, 'products');
        
        $this->assertCount(3, $paths);
        
        foreach ($paths as $path) {
            Storage::disk('public')->assertExists($path);
        }
    }

    public function test_file_deletion()
    {
        $fileUploadService = app(FileUploadService::class);
        
        $file = UploadedFile::fake()->image('test.jpg', 800, 600)->size(1024);
        $path = $fileUploadService->uploadImage($file, 'products');
        
        Storage::disk('public')->assertExists($path);
        
        $result = $fileUploadService->deleteFile($path);
        
        $this->assertTrue($result);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_delete_nonexistent_file_returns_false()
    {
        $fileUploadService = app(FileUploadService::class);
        
        $result = $fileUploadService->deleteFile('nonexistent/path.jpg');
        
        $this->assertFalse($result);
    }

    public function test_delete_multiple_files()
    {
        $fileUploadService = app(FileUploadService::class);
        
        $files = [
            UploadedFile::fake()->image('test1.jpg', 800, 600)->size(1024),
            UploadedFile::fake()->image('test2.png', 800, 600)->size(1024),
        ];
        
        $paths = $fileUploadService->uploadMultipleImages($files, 'products');
        
        $deleted = $fileUploadService->deleteMultipleFiles($paths);
        
        $this->assertEquals(2, $deleted);
        
        foreach ($paths as $path) {
            Storage::disk('public')->assertMissing($path);
        }
    }

    public function test_get_file_url()
    {
        $fileUploadService = app(FileUploadService::class);
        
        $file = UploadedFile::fake()->image('test.jpg', 800, 600)->size(1024);
        $path = $fileUploadService->uploadImage($file, 'products');
        
        $url = $fileUploadService->getFileUrl($path);
        
        $this->assertNotEmpty($url);
        $this->assertStringContainsString($path, $url);
    }

    public function test_file_exists_check()
    {
        $fileUploadService = app(FileUploadService::class);
        
        $file = UploadedFile::fake()->image('test.jpg', 800, 600)->size(1024);
        $path = $fileUploadService->uploadImage($file, 'products');
        
        $this->assertTrue($fileUploadService->fileExists($path));
        $this->assertFalse($fileUploadService->fileExists('nonexistent/path.jpg'));
    }

    public function test_filename_generation_includes_timestamp()
    {
        $fileUploadService = app(FileUploadService::class);
        
        $file = UploadedFile::fake()->image('test.jpg', 800, 600)->size(1024);
        $path = $fileUploadService->uploadImage($file, 'products');
        
        $filename = basename($path);
        
        $this->assertMatchesRegularExpression('/^\d{14}_\w{8}\.jpg$/', $filename);
    }

    public function test_upload_to_custom_directory()
    {
        $fileUploadService = app(FileUploadService::class);
        
        $file = UploadedFile::fake()->image('test.jpg', 800, 600)->size(1024);
        $path = $fileUploadService->uploadImage($file, 'custom/directory');
        
        $this->assertStringStartsWith('custom/directory/', $path);
        Storage::disk('public')->assertExists($path);
    }
}
