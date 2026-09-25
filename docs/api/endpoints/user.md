# API de Usuários (`/api/user/`)

Controller: `src/core/Controllers/User.php`

## Propriedades da Entidade

| Propriedade | Tipo | Descrição |
|-------------|------|-----------|
| `id` | integer | ID único |
| `authProvider` | smallint | Provedor de autenticação |
| `authUid` | string(512) | UID no provedor |
| `email` | string | Email |
| `lastLoginTimestamp` | datetime | Último login |
| `createTimestamp` | datetime | Data de criação |
| `status` | smallint | Status (1=Ativo) |

## Relações

| Relação | Tipo | Entidade | Descrição |
|---------|------|----------|-----------|
| `profile` | ManyToOne | Agent | Agente perfil |
| `agents` | OneToMany | Agent | Todos os agentes |
| `roles` | OneToMany | Role | Papéis |

## Endpoints

### `GET /api/user/find` - Buscar Usuários

**Auth**: Requer autenticação. Implementação própria que filtra campos públicos.

```bash
GET /api/user/find?email=ILIKE(%@example.com%)
```

> **Nota**: Retorna apenas campos públicos (`getPublicApiFields()`). Não expõe senha, authUid, deleteAccountToken, etc.

### `GET /api/user/findOne` - Buscar Um Usuário

**Auth**: Requer autenticação. Implementação própria.

```bash
GET /api/user/findOne?id=EQ(1)
```

### `GET /api/user/getId` - Obter ID do Usuário Atual

Retorna o ID do usuário autenticado.

**Auth**: Requer autenticação

```bash
GET /api/user/getId
```

**Resposta:**
```json
1
```

## CRUD

> **Atenção**: Endpoints CRUD usam `POST_`/`DELETE_` e **não** possuem o prefixo `/api/`.

| Método | URL | Método Interno | Descrição | Auth |
|--------|-----|--------------|-----------|------|
| `POST` | `/user/` | `POST_index` | Criar usuário | Sim |
| `DELETE` | `/user/{id}` | `DELETE_single` | Deletar usuário | Sim (próprio ou admin) |

> **Nota**: `POST_index` e `DELETE_single` têm implementação própria no UserController.

## Campos Públicos via API

Os seguintes campos são retornados ao buscar usuários via API:

- `id`
- `email`
- `authProvider`
- `lastLoginTimestamp`
- `createTimestamp`
- `status`
- `profile` (agente perfil, com sub-campos filtrados)

## Procurações

O sistema de procurações permite delegar ações entre usuários. A entidade `Procuration` possui:
- `id` - Token da procuração (string 32)
- `action` - Ação delegada
- `createTimestamp` - Data de criação
- `validUntilTimestamp` - Válido até
- `user` - Usuário que delega
- `attorney` - Usuário que recebe a delegação
