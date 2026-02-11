# Mapas Culturais Helm Chart

This chart deploys Mapas Culturais, a cultural mapping platform, with PostgreSQL and Redis dependencies.

## Configuration Overview

The chart supports flexible Redis/Valkey configuration with three main modes:

1. **Internal Redis subcharts** (default) - Separate Redis instances for cache and sessions
2. **External Redis/Valkey** - Connect to existing Redis instances with authentication
3. **No Redis** - Use APCu/filesystem cache and filesystem sessions

### Key Configuration Options

#### Redis Cache Configuration (`mapas.redisCache`)
- `host`: Redis host (empty = use APCu/filesystem fallback)
- `port`: Redis port (default: 6379)
- `password`: Redis password (inline)
- `existingSecret`: Kubernetes secret containing password
- `existingSecretKey`: Key in secret (default: "password")

#### Redis Sessions Configuration (`mapas.redisSessions`)
- Same structure as `redisCache`
- If `useSameRedisForCacheAndSessions: true`, uses `redisCache` configuration

#### Shared Redis Instance
- `useSameRedisForCacheAndSessions`: Share same Redis instance for cache and sessions

#### Subchart Configuration
- `redis-cache.enabled`: Enable internal Redis cache subchart
- `redis-sessions.enabled`: Enable internal Redis sessions subchart
- Subchart authentication via `auth.enabled`, `auth.password`, `auth.existingSecret`

## Examples

See the [examples/](./examples/) directory for complete configuration files:

- [internal-redis.yaml](./examples/internal-redis.yaml) - Default internal Redis subcharts
- [external-redis-with-auth.yaml](./examples/external-redis-with-auth.yaml) - External Redis with authentication
- [valkey-operator.yaml](./examples/valkey-operator.yaml) - Valkey operator configuration
- [no-redis.yaml](./examples/no-redis.yaml) - No Redis (filesystem sessions + APCu cache)
- [same-redis-instance.yaml](./examples/same-redis-instance.yaml) - Single Redis instance for both cache and sessions

## Usage

### Internal Redis (Default)
```bash
helm install mapas ./helm/mapas
```

### External Redis with Authentication
```bash
helm install mapas ./helm/mapas -f examples/external-redis-with-auth.yaml
```

### Valkey Operator
```bash
helm install mapas ./helm/mapas -f examples/valkey-operator.yaml
```

### No Redis (Simplified Deployment)
```bash
helm install mapas ./helm/mapas -f examples/no-redis.yaml
```

### Same Redis Instance for Cache and Sessions
```bash
helm install mapas ./helm/mapas -f examples/same-redis-instance.yaml
```

## Authentication Security

For production deployments:
- Use `existingSecret` instead of inline passwords
- Enable authentication on Redis subcharts when using internal Redis
- Consider using Valkey operator for managed Redis-compatible instances

## Fallback Behavior

- **Cache**: Redis → APCu → Filesystem (automatic fallback when Redis unavailable)
- **Sessions**: Redis → Filesystem (configured via `SESSIONS_SAVE_PATH`)

## Notes

- When using internal Redis subcharts, `mapas.redisCache.host` and `mapas.redisSessions.host` are ignored
- The `SESSIONS_SAVE_PATH` environment variable supports Redis connection strings with authentication: `tcp://host:port?auth=password`
- PostgreSQL configuration is handled via the groundhog2k postgresql subchart (PostGIS image)