<?php

namespace Tests\Unit\Core;

use Core\View;
use Core\Exceptions\HttpException;
use PHPUnit\Framework\TestCase;

class ViewTest extends TestCase
{
    private string $tempDir;
    private string $defaultPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = sys_get_temp_dir() . '/view-tests';
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0777, true);
        }

        // Configura um diretório temporário como caminho padrão para views
        $this->defaultPath = $this->tempDir . '/app/Views/';
        if (!is_dir($this->defaultPath)) {
            mkdir($this->defaultPath, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->rrmdir($this->tempDir);
    }

    private function rrmdir(string $dir): void
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object !== "." && $object !== "..") {
                    $path = $dir . "/" . $object;
                    if (is_dir($path)) {
                        $this->rrmdir($path);
                    } else {
                        unlink($path);
                    }
                }
            }
            rmdir($dir);
        }
    }

    public function testRenderExistingFile(): void
    {
        $filePath = $this->tempDir . '/welcome.html';
        file_put_contents($filePath, '<h1>Olá, mundo!</h1>');
        $content = View::render('welcome', $this->tempDir . '/');
        $this->assertEquals('<h1>Olá, mundo!</h1>', $content);
    }

    public function testRenderNonExistingFile(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionCode(404);
        View::render('404', $this->tempDir . '/');
    }

    public function testDefaultPath(): void
    {
        $filePath = $this->defaultPath . 'home.html';
        file_put_contents($filePath, 'Conteúdo da página inicial');

        $content = View::render('home', $this->defaultPath);
        $this->assertEquals('Conteúdo da página inicial', $content);
    }
}
