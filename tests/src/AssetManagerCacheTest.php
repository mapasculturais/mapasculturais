<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\AssetManagers\FileSystem;
use MapasCulturais\Cache;
use MapasCulturais\Hooks;
use MapasCulturais\Theme;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

/**
 * Regressão do 404 nos templates Angular após remover assets ainda em cache.
 * Execução sem banco: vendor/bin/phpunit --bootstrap src/bootstrap.php tests/src/AssetManagerCacheTest.php
 */
class AssetManagerCacheTest extends TestCase
{
    private const ASSET = 'js/directives/edit-box.html';
    private const TEMPLATE = '<div class="edit-box" ng-transclude></div>';

    private string $directory;
    private string $source;
    private array $cacheEntries = [];
    private array $previousInstances;
    private App $app;
    private FileSystem $manager;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/mapas-assets-' . bin2hex(random_bytes(8));
        mkdir($this->directory . '/source/directives', 0777, true);
        $this->source = $this->directory . '/source/directives/edit-box.html';
        file_put_contents($this->source, self::TEMPLATE);

        // Isola o singleton e o cache; a publicação usa o driver e arquivos reais.
        $instances = new ReflectionProperty(App::class, '_instances');
        $this->previousInstances = $instances->getValue();
        $this->app = (new ReflectionClass(App::class))->newInstanceWithoutConstructor();
        $this->app->hooks = new Hooks($this->app);
        $this->app->config = [
            'base.assetUrl' => 'https://mapas.test/assets/',
            'app.useAssetsUrlCache' => true,
            'app.assetsUrlCache.lifetime' => 3600,
            'app.log.hook' => false,
        ];

        $cache = $this->getMockBuilder(Cache::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['contains', 'fetch', 'save'])
            ->getMock();
        $cache->method('contains')->willReturnCallback(fn($key) => array_key_exists($key, $this->cacheEntries));
        $cache->method('fetch')->willReturnCallback(fn($key) => $this->cacheEntries[$key] ?? null);
        $cache->method('save')->willReturnCallback(function ($key, $value) {
            $this->cacheEntries[$key] = $value;
        });
        $this->app->cache = $cache;

        $theme = $this->getMockBuilder(Theme::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['_init', 'register', 'getVersion', 'getAssetFilename'])
            ->getMock();
        $theme->method('getAssetFilename')->willReturnCallback(function ($asset) {
            $this->assertSame(self::ASSET, $asset);
            return $this->source;
        });
        $this->app->view = $theme;
        $instances->setValue(null, ['web' => $this->app]);

        // Evita depender do bootstrap completo da aplicação e do banco de dados.
        $this->manager = (new ReflectionClass(FileSystem::class))->newInstanceWithoutConstructor();
        $this->manager->config = ['publishPath' => $this->directory . '/published/'];
    }

    protected function tearDown(): void
    {
        (new ReflectionProperty(App::class, '_instances'))->setValue(null, $this->previousInstances);

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }
        rmdir($this->directory);
    }

    private function publishedPath(string $url): string
    {
        return $this->manager->config['publishPath'] . substr($url, strlen($this->app->assetUrl));
    }

    private function publish(string $method, bool $includeHash = true): string
    {
        return $method === 'assetUrl'
            ? $this->manager->assetUrl(self::ASSET, $includeHash)
            : $this->manager->publishAsset(self::ASSET, null, $includeHash);
    }

    public static function cacheLayers(): array
    {
        return [
            'both caches, hashed filename' => ['assetUrl', true],
            'both caches, unhashed filename' => ['assetUrl', false],
            'publication cache, hashed filename' => ['publishAsset', true],
            'publication cache, unhashed filename' => ['publishAsset', false],
        ];
    }

    #[DataProvider('cacheLayers')]
    public function testRepublishesMissingTemplateWithWarmCache(string $method, bool $includeHash): void
    {
        $url = $this->publish($method, $includeHash);
        $path = $this->publishedPath($url);
        $this->assertFileExists($path);

        unlink($path);
        clearstatcache(true, $path);

        $this->assertSame($url, $this->publish($method, $includeHash));
        $this->assertFileExists($path, 'O cache não pode retornar uma URL cujo template foi removido.');
        $this->assertSame(self::TEMPLATE, file_get_contents($path));
    }

    public function testRepublishesWhenOnlyPublicationCacheRemains(): void
    {
        $url = $this->manager->assetUrl(self::ASSET);
        $path = $this->publishedPath($url);
        unset($this->cacheEntries['ASSET_URL:' . self::ASSET . ':1']);
        unlink($path);
        clearstatcache(true, $path);

        $this->assertSame($url, $this->manager->assetUrl(self::ASSET));
        $this->assertFileExists($path);
    }

    public function testExistingCachedTemplateIsNotRepublished(): void
    {
        $url = $this->manager->assetUrl(self::ASSET);
        file_put_contents($this->source, 'source changed after publication');

        $this->assertSame($url, $this->manager->assetUrl(self::ASSET));
        $this->assertSame($url, $this->manager->publishAsset(self::ASSET));
        $this->assertSame(self::TEMPLATE, file_get_contents($this->publishedPath($url)));
    }

    public function testRepublishesCustomDestination(): void
    {
        $destination = 'templates/nested/edit-box.html';
        $url = $this->manager->publishAsset(self::ASSET, $destination);
        $path = $this->publishedPath($url);
        unlink($path);
        clearstatcache(true, $path);

        $this->assertSame($url, $this->manager->publishAsset(self::ASSET, $destination));
        $this->assertFileExists($path);
    }

    public function testRepublishesLocalFileWithCdnAssetUrl(): void
    {
        $this->app->config['base.assetUrl'] = 'https://cdn.mapas.test/subsite/assets/';
        $url = $this->manager->assetUrl(self::ASSET);
        $path = $this->publishedPath($url);
        unlink($path);
        clearstatcache(true, $path);

        $this->assertSame($url, $this->manager->assetUrl(self::ASSET));
        $this->assertFileExists($path);
    }

    public function testExternalUrlsRemainUnchanged(): void
    {
        foreach (['https://external.test/template.html', 'http://external.test/template.html', '//external.test/template.html'] as $url) {
            $this->assertSame($url, $this->manager->assetUrl($url));
            $this->assertSame($url, $this->manager->assetUrl($url));
            $this->assertSame($url, $this->manager->publishAsset($url));
        }
        $this->assertDirectoryDoesNotExist($this->manager->config['publishPath']);
    }

    public function testPublicationStillWorksWithCacheDisabled(): void
    {
        $this->app->config['app.useAssetsUrlCache'] = false;
        $url = $this->manager->assetUrl(self::ASSET);
        $path = $this->publishedPath($url);
        unlink($path);
        clearstatcache(true, $path);

        $this->assertSame($url, $this->manager->assetUrl(self::ASSET));
        $this->assertFileExists($path);
    }
}
