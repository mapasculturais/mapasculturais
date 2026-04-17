# Autenticação na API

A API do Mapas Culturais suporta autenticação via **JWT (JSON Web Token)** usando o módulo **Apps**.

## Fluxo de Autenticação

### 1. Criar uma aplicação (UserApp)

Para usar a API autenticada, o usuário precisa criar uma `UserApp` através da interface web do Mapas Culturais (painel do usuário).

Cada `UserApp` possui:
- **`publicKey`** (32 chars) - Identificador público, usado como `pk` no JWT
- **`privateKey`** (64 chars) - Chave secreta para assinar/verificar o JWT
- **`userId`** - Usuário proprietário da aplicação

### 2. Obter o Token JWT

O token JWT deve ser gerado client-side usando a `privateKey` da `UserApp` como segredo HMAC.

**Payload do JWT:**
```json
{
  "pk": "publicKeyDaUserApp",
  "iat": 1700000000,
  "exp": 1700086400
}
```

### 3. Usar o Token

Inclua o token no header `Authorization` de todas as requisições:

```
Authorization: Bearer <token_jwt>
```

Também é aceito sem o prefixo `Bearer `:
```
Authorization: <token_jwt>
```

## Como funciona internamente

### Middleware: `JWTAuthMiddleware`

Localização: `src/modules/Apps/Middleware/JWTAuthMiddleware.php`

1. Lê o header `Authorization` (case-insensitive)
2. Remove o prefixo `Bearer ` se presente
3. Se token existe, substitui `$app->auth` por `JWTAuthProvider`

### Provider: `JWTAuthProvider`

Localização: `src/modules/Apps/JWTAuthProvider.php`

1. Decodifica o payload JWT, extrai `pk` (publicKey da UserApp)
2. Busca a `UserApp` no banco: `$app->repo(UserApp::class)->find($pk)`
3. Verifica a assinatura JWT usando `firebase/php-jwt` com `UserApp.privateKey` como segredo
4. Em caso de sucesso, define `$app->user = $userapp->user`
5. Em caso de falha, retorna HTTP 401

## Controle de Acesso por Endpoint

### Endpoints públicos (não requerem autenticação)

Todos os endpoints `API_find`, `API_findOne`, `API_describe`, `API_filters` retornam dados públicos por padrão. Entidades com `status > 0` são visíveis para qualquer um.

### Endpoints que requerem autenticação

- `POST_index` - Criar entidades
- `PUT_single` - Atualizar entidades
- `PATCH_single` - Atualizar parcialmente entidades
- `DELETE_single` - Deletar entidades
- `POST_upload` - Upload de arquivos
- `POST_createAgentRelation` / `POST_removeAgentRelation`
- `POST_createSealRelation` / `POST_removeSealRelation`
- `POST_changeOwner`
- `POST_send` (inscrições)
- `POST_saveEvaluation` (avaliações)
- `POST_setStatusTo` (mudar status de inscrição)
- Qualquer endpoint que modifique dados

### Permissões do Usuário

Cada entidade possui uma lista de permissões verificada pelo sistema de `PermissionCache`. As permissões são verificadas no método `can($action, $entity)` do usuário.

Para verificar permissões do usuário autenticado via API, use o parâmetro `@select`:
```
GET /api/agent/find?@select=id,name,currentUserPermissions
```

Retorna:
```json
{
  "id": 1,
  "name": "Agente",
  "currentUserPermissions": {
    "view": true,
    "modify": true,
    "remove": false,
    "@controll": true
  }
}
```

## Segurança

- A `privateKey` nunca é exposta via API (o módulo Apps filtra `user => 'EQ(@me)'`)
- Tokens JWT têm expiração configurável no payload (`exp`)
- Cada UserApp está vinculada a um único usuário
- O middleware é aplicado a cada requisição que contém header `Authorization`

## Autenticação por Sessão (Web)

Para requisições web (não-API), a autenticação é por sessão PHP. Controllers usam `$this->requireAuthentication()` que redireciona para a página de login se o usuário não estiver autenticado.

Isso **não se aplica** a chamadas `/api/`.
