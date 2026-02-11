# AGENTS.md - MapaCultural Development Guide

## Project Overview
PHP 8.3 app using Doctrine ORM, Slim 4, PostgreSQL/PostGIS. Frontend: pnpm workspaces.

## Development Environments
```bash
# Docker Compose (Recommended)
cd dev/
./start.sh              # Start containers (app, db, mailhog)
./bash.sh               # Shell into container
./psql.sh               # Connect to PostgreSQL
./shell.sh              # PHP interactive shell (psysh)
./pnpm.sh <cmd>         # Run pnpm commands

# Skaffold + K8s (Infrastructure)
skaffold dev            # Hot-reload dev mode
skaffold run            # Deploy to cluster
```

**Access:** http://localhost (port 80)

## Build Commands
```bash
composer install                    # PHP dependencies
composer dump-autoload              # Regenerate autoloader

cd src/
pnpm install                        # Install workspace deps
pnpm run build                      # Build all assets
pnpm run dev                        # Dev build with source maps
pnpm run watch                      # Watch mode
```

## Testing
```bash
# Run all tests
vendor/bin/phpunit tests/

# Single test file
vendor/bin/phpunit tests/src/EntitiesTest.php

# Single test method
vendor/bin/phpunit tests/src/EntitiesTest.php --filter testAgentCreation

# Inside container
./scripts/run-tests-docker.sh
```

**Test Structure:** Tests extend `Tests\Abstract\TestCase`. Each test runs in DB transaction (auto-rollback). Builders in `tests/src/Builders/`, Directors in `tests/src/Directors/`.

## Code Style Guidelines

### PHP Conventions
- **PHP 8.3+** with `declare(strict_types=1);`
- **Namespaces:** PSR-4 autoloading
  - `MapasCulturais\` → `src/core/`
  - `MapasCulturais\Modules\` → `src/modules/`
  - `MapasCulturais\Themes\` → `src/themes/`
  - `Tests\` → `tests/`

### Naming
- **Classes:** PascalCase (`AgentController`, `UserBuilder`)
- **Methods:** camelCase (`getAgentById`, `createUser`)
- **Properties:** camelCase (`$entityManager`, `$validationErrors`)
- **Constants:** UPPER_SNAKE_CASE (`STATUS_ENABLED`, `STATUS_DRAFT`)
- **Files:** Match class name

### Entity Status Constants
```php
Entity::STATUS_ENABLED  = 1
Entity::STATUS_DRAFT    = 0
Entity::STATUS_DISABLED = -9
Entity::STATUS_TRASH    = -10
Entity::STATUS_ARCHIVED = -2
```

### Doctrine Annotations
```php
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'agent')]
class Agent extends Entity { }
```

### Error Handling
Use typed exceptions from `MapasCulturais\Exceptions\`:
- `PermissionDenied` - access control
- `NotFound` - missing entities
- `WorkflowRequest` - workflow state issues

### Hooks System
```php
$app->hook('entity(Agent).save:before', function() { });
$app->hook('entity.insert:after', function() { });
```

### Imports Order
1. PHP built-in classes
2. Doctrine classes
3. Slim/PSR classes
4. Symfony components
5. MapasCulturais core classes
6. Application-specific classes

## Database
```bash
# Apply DB updates
./scripts/db-update.sh

# Restore from dump
./scripts/restore-dump.sh -db=mapas -u=mapas -f=dump.sql
```

Migrations in `db-updates.php` (auto-applied on container start via entrypoint.sh).

## Key Directories
```
src/
  core/           # Core framework (App, Entity, Controller)
  modules/        # Feature modules
  themes/         # UI themes (BaseV1, BaseV2)
  conf/           # Configuration loader
  tools/          # CLI tools (apply-updates.php)
config/           # Config files
public/           # Web root
scripts/          # Shell scripts
tests/            # PHPUnit tests
helm/             # Helm chart
dev/              # Docker Compose env
docker/           # Docker config
```

## Configuration
- Main: `config/` (merged at runtime)
- Dev overrides: `dev/config.d/`
- Environment via `env()` function
- Key vars: `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `REDIS_CACHE`

## Debug
- Set `APP_DEBUG=true`
- Logs: `var/logs/app.log`
- Use `dump()` or `dd()` for debugging
- PHP shell: `dev/shell.sh` (psysh)
