# RB6 — Assets: página sem CSS/JS, asset "morto" ou desatualizado

**Gatilho**: página renderiza sem estilo/script após mudança no frontend; asset publicado antigo; 500 por dependência.

## O pipeline duplo (resumo)
- **Build (imagem/dev)**: workspace pnpm (`src/pnpm-workspace.yaml`) + laravel-mix — **só** `assets-src/` de 2 pacotes (`theme-BaseV2.css`, `vue-init.js`/`media-query.js`; `src/node_scripts/webpack.mix.js:36-44`). Todo o resto (309 `script.js` de componentes, `components-base`, vendor v1, CSS de node_modules) é servido **cru** e publicado em RUNTIME.
- **Runtime**: `Theme::enqueueScript/Style(group,...)` → ordenação por dependências (dependência ausente = **exceção, página 500** — `AssetManager.php:93`) → publicação em `public/assets/` com hash de mtime; produção mergeia o grupo e roda **terser/uglifycss na primeira exibição do grupo** (`src/core/AssetManagers/FileSystem.php:154-212`).

## Passos
1. **Grupo certo?** `app`/`vendor` só são impressos sob **BaseV1**; `vendor-v2`/`app-v2`/`components` só sob **BaseV2** (pontos de impressão: `BaseV1/Theme.php:1625-1641` vs `BaseV2/layouts/parts/header.php:19-23` + `Components/Module.php:106-111`). Enfileirar no grupo da outra geração = asset morto, sem erro.
2. **Dependente existe?** renomear/remover asset do qual outro depende quebra a página inteira.
3. **Build**: `cd src && pnpm install && pnpm run build` (ou `dev`/`watch`); BaseV1 depende de `sass` standalone (`Dockerfile:96`, `scripts/compile-sass.sh`).
4. **Cache de HTML de assets**: `ASSETS_SCRIPTS:$group:nomes` — trocar o CONTEÚDO mantendo os nomes serve HTML com URL antiga até o lifetime (`AssetManager.php:144-152`); o arquivo antigo sobrevive ~3 dias no disco (cleanup). Ajustar `CACHE_ASSETS_URL`/lifetimes em `config/cache.php`.
5. **Disco cheio**: `public/assets/` cresce a cada mtime; limpeza = `src/tools/cleanup-orphan-assets.php` (**aborta sem `REDIS_CACHE`** — em instalações sem Redis o diretório cresce para sempre); `--dry-run` primeiro; `ASSET_CLEANUP_MIN_AGE` default 3 dias.
6. `chmod 0666` nos arquivos gravados (`src/core/Storage/FileSystem.php:77`) — anotado para endurecimento.

**Evidências**: prefixo único por instalação invalida URLs em flush de cache (`AssetManager.php:245-259`).
