# 0014. Pipeline duplo de assets: webpack/laravel-mix no build da imagem + publicação/merge/minify pelo AssetManager em runtime

**Status:** aceito

**Data da decisão (derivada do histórico git):** 2022-05-05 (migração do monorepo para pnpm, commit `45d6518c0`, "Migrar monorepo para pnpm (#1982)") — formaliza a separação que acompanha o BaseV2 desde 2022.

## Contexto histórico

A geração 1 publicava todos os assets em runtime (copy + hash de mtime) sem passo de build — editar JS era recarregar. A chegada do Vue 3 (BaseV2, 2022) exigiu compilação: Sass estruturado (ITCSS) e um bundle Vue (`vue.esm-bundler` com flags `__VUE_OPTIONS_API__`). Ao mesmo tempo, o mecanismo WordPress-like de enqueue por grupos com dependências, merge e minify server-side (terser/uglifycss) já resolvia publicação, cache-busting e ordenação para 50+ instâncias que não rodam Node em produção. A solução foi **não escolher**: dois pipelines coexistindo com papéis distintos, unificados pelo monorepo pnpm (2022-05-05).

## Decisão

**Build-time (imagem Docker/dev)** — o que webpack/laravel-mix builda é mínimo, apenas entrypoints de `assets-src/` dos pacotes com `package.json`:
- Workspace pnpm: `modules/*`, `plugins/*`, `themes/*`, `node_scripts` (`src/pnpm-workspace.yaml`).
- Config compartilhada `@mapas/scripts` (`src/node_scripts/webpack.mix.js`): globs `assets-src/js/*.js` → `assets/js/`, `assets-src/sass/*.scss` → `assets/css/` (linhas 36-44); renomeação de saída por `pkg.mapas.assets` (ex.: BaseV2 `main` → `theme-BaseV2.css`, `src/themes/BaseV2/package.json:10-16`); alias `vue$` para o build full + `DefinePlugin` das flags Vue (23-34).
- Produtos: **apenas** `BaseV2/assets/css/theme-BaseV2.css` e `Components/assets/js/{vue-init,media-query}.js` (gitignored — `.gitignore` dos pacotes). O CSS do BaseV1 fica fora do workspace: compilado por `sass` standalone (`docker/Dockerfile:96`; `scripts/compile-sass.sh`).

**Runtime (primeira exibição da página)** — o `AssetManager` publica e imprime **tudo** (incluindo os produtos do webpack, os ~309 `script.js` de componentes crus, os `components-base`, CSS direto de `node_modules` — `Components/Module.php:36-42` — e todo o vendor v1):
- Registro: `Theme::enqueueScript/Style($group, $nome, $arquivo, $deps)` (`src/core/Theme.php:647-663`) → `AssetManager` (`src/core/AssetManager.php:50-72`); nome é chave única no grupo.
- Ordenação: `_addAssetToArray` insere dependências recursivamente antes do dependente (DFS pré-ordem; `AssetManager.php:83-114`); **dependência ausente lança exceção** (93) — página 500, não silêncio.
- Publicação individual: nome `{arquivo}.{pasta}.{crc32(arquivo . mtime . prefixo)}.{ext}` (`AssetManager.php:268-280`) — cache-busting por mtime; resolução do arquivo-fonte pela hierarquia de paths tema→plugins→módulos (`Theme.php:756-761`), o mesmo mecanismo das views (ADR 0013).
- Merge + minify em produção (`mergeScripts/mergeStyles` default quando `APP_MODE=production`, `config/assets-manager.php:9-10`): o grupo inteiro concatenado em `{group}.{crc32}.{ext}` e processado por comando shell — `terser {IN} --source-map --output {OUT}` / `uglifycss` (`config/assets-manager.php:12-18`; `_exec` em `src/core/AssetManagers/FileSystem.php:62-91`) — **em runtime, na primeira exibição do grupo**, gravado em `public/assets/`.
- Grupos = pontos de impressão + granularidade de merge: v1 `vendor`/`app` (`BaseV1/Theme.php:1625-1641`); v2 `vendor-v2`/`app-v2` no `<head>` (`BaseV2/layouts/parts/header.php:19-23`) + `components` após o jsObject (`Components/Module.php:106-111`).
- Cache do HTML de tags por `ASSETS_SCRIPTS/STYLES:$group:nomes` e de URL individual por `ASSET_URL:{file}` (`AssetManager.php:139-232`).
- Higiene: `src/tools/cleanup-orphan-assets.php` — "em uso" = chaves Redis `ASSETS_SCRIPTS*/ASSETS_STYLES*/ASSET_URL*` (52-79); remove não-protegidos com mtime > 3 dias; **aborta sem `REDIS_CACHE`** (38-41).

## Alternativas consideradas

- **Build total (SPA/bundler para tudo)**: descartada — quebraria o modelo de temas de instância que editam JS/CSS sem Node e a paridade template PHP/render server-side; componentes Vue dependem de `$TEMPLATES` renderizado pelo PHP.
- **Publicação pura em runtime sem build**: insuficiente para Sass/ITCSS e para o bundle Vue bundler.
- **Minify no build**: descartado na prática — só 2 pacotes têm build; a longa cauda de assets (componentes, vendor v1) só é alcançável pelo runtime.

## Consequências

**Positivas:**
- Componente novo **não precisa de build**: criar `components/<nome>/script.js` e recarregar basta (o "hot path" de desenvolvimento da plataforma).
- Cache-busting automático por mtime + prefixo único por instalação (`getFilenamePrefix`, `AssetManager.php:245-259`).
- Grupos dão escopo por geração e merge reduz requisições em produção sem tocar o código dos módulos.

**Negativas (armadilhas evidenciadas):**
- **Cache do HTML de tags ignora mtime**: a chave é só `$group` + nomes ordenados (`AssetManager.php:144-147`) — trocar o conteúdo de um arquivo mantendo os nomes serve HTML cacheado com URL antiga; o arquivo antigo sobrevive ~3 dias no disco. Agrava o env duplo `CACHE_ASSETS_URL` (controla flag E lifetime — `config/cache.php:33,45`).
- **Grupo errado = asset morto** (consequência partilhada com ADR 0013): enfileirar em `app`/`vendor` sob v2 nunca chega ao HTML.
- Dois lugares para entender "como o JS chega ao browser"; produção depende do build da imagem (`Dockerfile:92-96`) e o modo dev `BUILD_ASSETS=1` reconstrói no boot (`docker/entrypoint.sh:44-49`).
- `public/assets/` acumula versões hashadas (daí o cron de limpeza de 6h — `docker/cleanup-orphan-assets-cron.sh`); sem Redis, cresce para sempre.
- Bugs latentes no driver: `FileSystem.php:202` grava merge sem `publishPath` quando não há `process.*`; `die(var_dump())` de debug em `_exec` (79-80).

## Evidência

- Rastreio completo do pipeline (workspace → mix → enqueue → publicação → ordem no HTML, com diagrama): `r1-frontend-architect.md` §2.
- AssetManager ao nível de método (censo de grupos, ordenação, merge/minify runtime, caches, cleanup): `r5-frontend-architect.md` §2.
- Grupo morto sob v2: `r2-frontend-architect.md` §2.2.
- Cross-verification da ordenação por dependências: cadeia de deps de `components` (`Module.php:28-35`) + cadeia v1 (`BaseV1/Theme.php:1755-1826`) — ambas só funcionam pelo DFS de `_addAssetToArray`.

## Relacionados

- ADR 0013 (coexistência de gerações) — os grupos por geração são consequência direta.
- ADR 0017 (deploy com convergência no boot) — cobre as **etapas de boot** que recompilam artefatos derivados no destino (gate de versão `entrypoint.sh:36-41` e `BUILD_ASSETS=1` `entrypoint.sh:44-49`) como parte da cadeia de deploy; este ADR é o dono do **mecanismo de assets** que essas etapas acionam. Âncoras parcialmente coincidentes, propósitos distintos.
