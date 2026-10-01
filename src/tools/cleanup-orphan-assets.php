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

/**
 * Extrai nomes de arquivos publicados de um valor de cache Redis.
 * Inclui html (templates Angular do embedTools/form-builder) e map (source maps).
 */
function cleanup_orphan_assets_extract_filenames(string $value): array {
    if (!preg_match_all(
        '/[\w][\w.\-]*\.(?:js|css|html?|png|jpe?g|gif|ico|svg|woff2?|ttf|eot|map)\b/i',
        $value,
        $matches
    )) {
        return [];
    }

    return $matches[0];
}

/**
 * Templates Angular do BaseV1 (edit-box, find-entity, etc.). São poucos/pequenos
 * e o custo de apagá-los com ASSET_URL ainda vivo é 404 permanente no form-builder.
 */
function cleanup_orphan_assets_is_angular_html_template(string $pathname, string $filename): bool {
    if (preg_match('/\.directives\.[a-z0-9]+\.html$/i', $filename)) {
        return true;
    }

    // qualquer .html sob .../html/ (publishAsset publica templates Angular aí)
    $normalized = str_replace('\\', '/', $pathname);
    return str_contains($normalized, '/html/') && str_ends_with(strtolower($filename), '.html');
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

// 0. Índice do que realmente existe no disco (basename → true).
//    Usado para reconciliar cache zumbi (ASSET_URL apontando para arquivo já apagado).
$on_disk = [];
$disk_iterator = new \RecursiveIteratorIterator(
    new \RecursiveDirectoryIterator($assets_path, \FilesystemIterator::SKIP_DOTS)
);
foreach ($disk_iterator as $disk_file) {
    if ($disk_file->isFile()) {
        $on_disk[$disk_file->getFilename()] = true;
    }
}

$redis = new \Redis();
$redis->connect($redis_host);
$redis->setOption(\Redis::OPT_SCAN, \Redis::SCAN_RETRY);

// 1. Descobre, a partir do que ainda está vivo no cache, quais nomes ainda podem
//    estar em uso + quais chaves apontam para arquivo inexistente (cache zumbi).
//    Cobre printScripts/printStyles (ASSETS_*), assetUrl() (ASSET_URL) e publishAsset().
$protected = [];
$stale_keys = [];
$patterns = [
    '*ASSETS_SCRIPTS*',
    '*ASSETS_STYLES*',
    '*ASSET_URL*',
    '*publishAsset*',
];
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

                // valor serializado do Symfony pode ser string ou blob; normaliza para string
                if (!is_string($value)) {
                    $value = (string) $value;
                }

                $filenames = cleanup_orphan_assets_extract_filenames($value);
                $missing = false;

                foreach ($filenames as $filename) {
                    $protected[$filename] = true;
                    if (!isset($on_disk[$filename])) {
                        $missing = true;
                    }
                }

                // Só invalida ASSET_URL/publishAsset: são URLs individuais.
                // ASSETS_SCRIPTS/STYLES misturam vários arquivos; um ausente não invalida o grupo inteiro.
                if ($missing && (str_contains($key, 'ASSET_URL') || str_contains($key, 'publishAsset'))) {
                    $stale_keys[$key] = true;
                }
            }
        }
    } while ($cursor !== 0 && $cursor !== null);
}

// os .js.map nunca aparecem no HTML cacheado (são referenciados de dentro do
// próprio .js via sourceMappingURL), então protegemos o par pelo nome do .js
foreach (array_keys($protected) as $name) {
    if (str_ends_with($name, '.js')) {
        $protected["{$name}.map"] = true;
    }
}

// 1b. Reconciliação: apaga cache que aponta para arquivo inexistente.
//     Na próxima request o AssetManager republica (cache miss → publishAsset).
//     Sem isso, o fix de "proteger .html" não cura 404 já instalado.
$stale_cleared = 0;
foreach (array_keys($stale_keys) as $key) {
    $stale_cleared++;
    if ($dry_run) {
        echo "[cleanup-orphan-assets] (dry-run) invalidaria cache zumbi: {$key}\n";
    } else {
        $redis->del($key);
        echo "[cleanup-orphan-assets] cache zumbi invalidado: {$key}\n";
    }
}

$redis->close();

if ($app->config['app.log.assetManager'] ?? false) {
    $app->log->debug(sprintf(
        '[cleanup-orphan-assets] %d nomes protegidos, %d caches zumbis',
        count($protected),
        $stale_cleared
    ));
}

// 2. Percorre o disco e remove o que não está protegido e já passou da janela
//    de segurança (evita mexer em algo publicado há poucos instantes, que ainda
//    pode não ter sido gravado no cache).
$now = time();
$scanned = 0;
$deleted = 0;
$skipped_html = 0;
$bytes_freed = 0;

$iterator = new \RecursiveIteratorIterator(
    new \RecursiveDirectoryIterator($assets_path, \FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if (!$file->isFile()) {
        continue;
    }

    $scanned++;
    $filename = $file->getFilename();
    $pathname = $file->getPathname();

    if (isset($protected[$filename])) {
        continue;
    }

    // Cinto de segurança: nunca apagar templates Angular em html/, mesmo se o
    // regex/cache falhar de novo. Ocupam pouco espaço; 404 no form-builder é grave.
    if (cleanup_orphan_assets_is_angular_html_template($pathname, $filename)) {
        $skipped_html++;
        continue;
    }

    if (($now - $file->getMTime()) < $min_age_seconds) {
        continue;
    }

    $bytes_freed += $file->getSize();
    $deleted++;

    if ($dry_run) {
        echo "[cleanup-orphan-assets] (dry-run) removeria: {$pathname}\n";
    } else {
        @unlink($pathname);
    }
}

$mode = $dry_run ? 'dry-run' : 'aplicado';
echo sprintf(
    "[cleanup-orphan-assets] modo=%s pasta=%s arquivos_verificados=%d protegidos=%d removidos=%d html_preservados=%d caches_zumbis=%d espaco_liberado=%s\n",
    $mode,
    $assets_path,
    $scanned,
    count($protected),
    $deleted,
    $skipped_html,
    $stale_cleared,
    cleanup_orphan_assets_format_bytes($bytes_freed)
);

$app->em->getConnection()->close();
