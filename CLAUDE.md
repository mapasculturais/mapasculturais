# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Mapas Culturais is a collaborative platform for cultural mapping, management and data sharing built in PHP. It provides a virtual environment for mapping, promoting and managing cultural assets including agents, spaces, events and projects.

### Key Architecture Components

- **Backend**: PHP-based application using Doctrine ORM with PostgreSQL/PostGIS
- **Frontend**: JavaScript with Vue.js components and SASS styling  
- **Framework**: Slim Framework 4.x for routing and middleware
- **Database**: PostgreSQL with PostGIS extension for geospatial data
- **Authentication**: Multi-provider OAuth support (LoginCidadao, OpenID, Authentik)
- **Caching**: Redis for application cache and sessions
- **Asset Management**: Laravel Mix for JS/CSS compilation

### Directory Structure

- `src/core/` - Core application classes and entities
- `src/modules/` - Feature modules (Search, Charts, Reports, etc.)
- `src/themes/` - UI themes (BaseV1, BaseV2, Funarte, etc.)
- `src/plugins/` - Optional plugins extending functionality
- `config/` - Application configuration files
- `dev/` - Development environment scripts and configs
- `scripts/` - Maintenance and deployment scripts

## Development Commands

### Docker Development Environment

Start development environment:
```bash
cd dev && ./start.sh
```

Start with build:
```bash
cd dev && ./start.sh --build
```

### Asset Building

Install dependencies and build assets:
```bash
cd dev && ./pnpm.sh install --recursive
cd dev && ./pnpm.sh run build
```

Watch for changes (auto-rebuild):
```bash
cd dev && ./watch.sh
```

Watch SASS only:
```bash
cd dev && ./watch-sass.sh
```

### Database Management

Run database updates:
```bash
cd scripts && ./db-update.sh [domain] [save_log] [config_file]
```

Generate Doctrine proxies:
```bash
cd scripts && ./generate-proxies.sh
```

Recreate permission cache:
```bash
cd scripts && ./recreate-pcache.sh
```

### Testing

Run tests in Docker:
```bash
cd scripts && ./run-tests-docker.sh
```

## Code Organization

### Entities and Controllers

- Entities in `src/core/Entities/` represent database models
- Controllers in `src/core/Controllers/` handle HTTP requests
- Repository classes provide data access methods

### Module System

Each module in `src/modules/` contains:
- `Module.php` - Module registration and configuration
- `Controller.php` - Module-specific request handling  
- Frontend assets in dedicated subdirectories

### Theme System

Themes extend BaseV1 or BaseV2 and can override:
- Templates and layouts
- CSS/SASS styling
- JavaScript functionality
- Configuration options

### Plugin Architecture  

Plugins extend core functionality by:
- Hooking into application events
- Adding new routes and controllers
- Modifying entity behavior
- Providing additional authentication methods

## Database Conventions

- Uses Doctrine ORM with annotations
- Entities follow namespace `MapasCulturais\Entities\`
- Database updates via `db-updates.php` files in modules
- PostGIS integration for geospatial queries

## Frontend Development

- Vue.js components in `src/modules/Components/`
- SASS compilation via Laravel Mix
- Asset fingerprinting for cache busting
- Responsive design principles

## Configuration

- Main config in `config/config.php`
- Environment-specific configs in `config/config.d/`
- Docker development configs in `dev/config.d/`

## Important Scripts

- `scripts/db-update.sh` - Apply database schema changes
- `scripts/compile-sass.sh` - Compile SASS to CSS
- `dev/start.sh` - Start development Docker environment
- `dev/watch.sh` - Auto-rebuild assets on changes