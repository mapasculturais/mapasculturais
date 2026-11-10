# RB1 — Deploy / upgrade de instância

**Gatilho**: publicar nova versão da imagem (CI gera tags por branch/semver em `.github/workflows/ci.yml:3-36`) ou subir container existente com código novo.

## Sequência de boot (= deploy) — `docker/entrypoint.sh`
1. **Aguarda Postgres** (loop PDO; `entrypoint.sh:4-22`) — falha aqui = container reiniciando sem log de app.
2. Cria dirs de `var/` e chown `www-data` (24-31).
3. **`scripts/db-update.sh`** — 2 passadas: 1ª `DISABLE_PLUGINS=1` (core), 2ª com plugins (`db-update.sh:25-28`). **Exceção em um update NÃO aborta o boot** — é ecoada e o update re-executa no próximo deploy (`App.php:5475-5477`); o que bloqueia é DDL quebrada em update JÁ gravado no ledger.
4. **`scripts/mc-db-updates.sh`** (entrypoint:34; updates de dados, multicore).
5. Gate de versão: `version.txt` ≠ `var/private-files/deployment-version` → recompila sass + `doctrine orm:generate-proxies` (36-41).
6. `BUILD_ASSETS=1` → `pnpm install --recursive && pnpm run dev` (44-49; default `0`).
7. Sobe 3 crons em background (58-60): jobs, pcache, cleanup de assets.
8. `touch /mapas-ready` (63) — **marcador sem consumidor no repo** (não usar como probe sem criar consumidor).

## Primeiros passos quando um deploy "não sobe"
1. Verificar se passou do wait do DB (log "aguardando o banco").
2. Rodar manualmente `./scripts/db-update.sh` fora do container e ler o output — o nome do update em falha aparece com `ERROR`.
3. Conferir permissões de `var/` (logs em `var/logs/app.log`).
4. Imagem: base única web+workers — escalar réplicas escala TUDO (jobs inclusive; ver RB2).

**Evidências**: `docker/Dockerfile:118-123` (ENTRYPOINT/CMD); `scripts/deploy-ref.sh` é legado MINC (derruba `docker ps -q` inteiro — `:100-101`; evitar).
