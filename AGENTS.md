# Repository Guidelines

This repository hosts Mapas Culturais, a PHP application with Slim 4, Doctrine ORM, PostgreSQL/PostGIS, and a modular/themeable frontend.

## Project Structure & Module Organization
- `src/core/`: Core entities, controllers, services, helpers.
- `src/modules/`: Feature modules (each with its own `Module.php`, controllers, assets).
- `src/themes/` and `src/plugins/`: UI themes and optional extensions.
- `config/`: Application configuration; see `config.d/` overlays in `dev/`.
- `public/`: Web root (entrypoint assets).
- `tests/`: PHPUnit tests and Docker test configs.
- `dev/`: Dockerized development environment and asset tooling.
- `scripts/`: Maintenance scripts (DB updates, proxies, tests).

## Build, Test, and Development Commands
- Start dev environment: `cd dev && ./start.sh` (add `--build` on first run).
- Install/build assets: `cd dev && ./pnpm.sh install --recursive && ./pnpm.sh run build`.
- Watch assets: `cd dev && ./watch.sh` (or `./watch-sass.sh`).
- Database updates: `cd scripts && ./db-update.sh [domain] [save_log] [config_file]`.
- Doctrine proxies: `cd scripts && ./generate-proxies.sh`.
- Run tests (Dockerized): `cd scripts && ./run-tests-docker.sh [RelativeTestPath]`.

## Coding Style & Naming Conventions
- PHP: PSR-12, 4-space indentation, PSR-4 autoload under `MapasCulturais\…` (see `composer.json`). Classes: PascalCase; methods/properties: camelCase. One class per file.
- Modules: `src/modules/ModuleName/*`; module entry is `Module.php`.
- Themes/Plugins: `src/themes/ThemeName`, `src/plugins/PluginName`.
- JS/SASS: Prefer 2-space indentation; keep filenames kebab-case; components PascalCase.

## Testing Guidelines
- Framework: PHPUnit. Tests live under `tests/src/*Test.php` and extend `PHPUnit\Framework\TestCase`.
- Run all tests: `cd scripts && ./run-tests-docker.sh`. Run a single test: `./run-tests-docker.sh src/Path/To/MyTest.php`.
- Add tests for new modules, services, and bug fixes. Favor fast, isolated unit tests; use fixtures over real services.

## Commit & Pull Request Guidelines
- Commits: Use clear, imperative messages. Conventional prefixes are welcome (e.g., `feat:`, `fix:`, `chore:`). Keep scope focused.
- PRs: Provide a concise description, linked issues, testing steps, and screenshots for UI changes. Call out DB migrations (`scripts/db-update.sh`) and config impacts.

## Security & Configuration
- Never commit secrets. For local dev, use `dev/.env`; for 12-factor Docker, see `.env.12factor.example` and `docker-compose-12factor*.yml`.
- Review `config/config.php` and `dev/config.d/` before running in new environments.
