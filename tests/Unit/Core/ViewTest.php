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
        mkdir($this->tempDir, 0777, true);

        // Configura um diretório temporário como caminho padrão
        $this->defaultPath = $this->tempDir . '/app/Views/';
        mkdir($this->defaultPath, 0777, true);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        array_map('unlink', glob($this->tempDir . '/*'));
        rmdir($this->tempDir);
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

        unlink($filePath);
    }
}