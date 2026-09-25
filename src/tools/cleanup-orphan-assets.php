<?php
set_time_limit(0);
ini_set('memory_limit', '512M');

if (function_exists('proc_nice')) {
    @proc_nice(19);
}

require __DIR__ . '/../../public/bootstrap.php';

use MapasCulturais\App;

function cleanup_orphan_assets_format_bytes(int $bytes): string {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . $units[$i];
}

$app = App::i();

$dry_run = in_array('--dry-run', $argv, true) || (bool) env('ASSET_CLEANUP_DRY_RUN', false);
$min_age_seconds = (int) env('ASSET_CLEANUP_MIN_AGE', 3 * DAY_IN_SECONDS);

$assets_path = rtrim($app->config['themes.assetManager']['publishPath'] ?? (BASE_PATH . 'assets/'), '/');

// nunca deixar rodar num caminho que não seja claramente a pasta de assets publicados
if (strlen($assets_path) < 10 || !str_contains($assets_path, 'assets') || !is_dir($assets_path)) {
    fwrite(STDERR, "[cleanup-orphan-assets] caminho de assets inválido ou inexistente: {$assets_path}\n");
    exit(1);
}

$redis_host = env('REDIS_CACHE');

if (!$redis_host) {
    fwrite(STDERR, "[cleanup-orphan-assets] REDIS_CACHE não configurado - sem fonte confiável do que está em uso, abortando.\n");
    exit(1);
}

// 1. Descobre, a partir do que ainda está vivo no cache (Redis já expira sozinho
//    o que passou do TTL), quais nomes de arquivo publicados ainda podem estar em uso.
//    Isso cobre ASSETS_SCRIPTS/ASSETS_STYLES (grupos mergeados - printScripts/printStyles)
//    e ASSET_URL (assets individuais, ex.: imagens - assetUrl()).
$redis = new \Redis();
$redis->connect($redis_host);
$redis->setOption(\Redis::OPT_SCAN, \Redis::SCAN_RETRY);

$protected = [];
$patterns = ['*ASSETS_SCRIPTS*', '*ASSETS_STYLES*', '*ASSET_URL*'];
$seen_keys = [];

foreach ($patterns as $pattern) {
    $cursor = null;
    do {
        $keys = $redis->scan($cursor, $pattern, 500);
        if ($keys) {
            foreach ($keys as $key) {
                if (isset($seen_keys[$key])) {
                    continue;
                }
                $seen_keys[$key] = true;

                $value = $redis->get($key);
                if ($value === false) {
                    continue;
                }

                if (preg_match_all('/[\w][\w.\-]*\.(?:js|css|png|jpe?g|gif|ico|svg|woff2?|ttf|eot)\b/i', $value, $matches)) {
                    foreach ($matches[0] as $filename) {
                        $protected[$filename] = true;
                    }
                }
            }
        }
    } while ($cursor !== 0 && $cursor !== null);
}

$redis->close();

// os .js.map nunca aparecem no HTML cacheado (são referenciados de dentro do
// próprio .js via sourceMappingURL), então protegemos o par pelo nome do .js
foreach (array_keys($protected) as $name) {
    if (str_ends_with($name, '.js')) {
        $protected["{$name}.map"] = true;
    }
}

if ($app->config['app.log.assetManager'] ?? false) {
    $app->log->debug(sprintf('[cleanup-orphan-assets] %d nomes de arquivo protegidos encontrados no cache', count($protected)));
}

// 2. Percorre o disco e remove o que não está protegido e já passou da janela
//    de segurança (evita mexer em algo publicado há poucos instantes, que ainda
//    pode não ter sido gravado no cache).
$now = time();
$scanned = 0;
$deleted = 0;
$bytes_freed = 0;

$iterator = new \RecursiveIteratorIterator(
    new \RecursiveDirectoryIterator($assets_path, \FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if (!$file->isFile()) {
        continue;
    }

    $scanned++;

    if (isset($protected[$file->getFilename()])) {
        continue;
    }

    if (($now - $file->getMTime()) < $min_age_seconds) {
        continue;
    }

    $bytes_freed += $file->getSize();
    $deleted++;

    if ($dry_run) {
        echo "[cleanup-orphan-assets] (dry-run) removeria: {$file->getPathname()}\n";
    } else {
        @unlink($file->getPathname());
    }
}

$mode = $dry_run ? 'dry-run' : 'aplicado';
echo sprintf(
    "[cleanup-orphan-assets] modo=%s pasta=%s arquivos_verificados=%d protegidos=%d removidos=%d espaco_liberado=%s\n",
    $mode,
    $assets_path,
    $scanned,
    count($protected),
    $deleted,
    cleanup_orphan_assets_format_bytes($bytes_freed)
);

$app->em->getConnection()->close();
