# Convenção 07 — Commits, branches e releases

- **Branches** (CI em `.github/workflows/ci.yml:3-14`): `master`/`develop`/`saas` + tags `v*.*.*` buildam e publicam imagem no Docker Hub; PRs apenas para `develop` (só build); ramos de manutenção `v{X.Y}.X`; `dev/**` → homolog k8s (`develop.yml`); `release/*` → tag `*-RC` (`rc.yml`).
- **Commits**: híbrido conventional-com-escopo + PT-BR descritivo (`fix(dev):`, `feat(assets):`, `docs:` e mensagens longas em PT). Sem enforcement automático (não há linter/CI de estilo).
- **Release**: bump de `version.txt` (comparado no boot para recompilar sass/proxies — `entrypoint.sh:36-41`) + `CHANGELOG.md` mantido manualmente (commits "Atualiza CHANGELOG" recorrentes); publicação por `scripts/publish-version.sh` (branch `release/<v>`, merge para prod, tag).

**Regra prática**: PR para `develop`; mensagem no padrão do histórico do repo; release só com CHANGELOG + version.txt atualizados.
