---
name: mapas-culturais-metadata-eav
description: Como criar e consumir campos customizados (metadados EAV) no MapasCulturais — os 5 canais de registro, serialização por tipo, privacidade, persistência na tabela *_meta e exposição na API
---

# Skill: Metadados EAV (campos customizados) no MapasCulturais

Use esta skill para adicionar/consumir campos customizados em entidades (Agent, Space, Event, Seal, Subsite, Registration...) e entender onde o valor persiste e como aparece na API.

## 1. Registrar (os 5 canais, todos convergem em `App::registerMetadata` — `src/core/App.php:4946-4993`)

| Caso | Canal | Como |
|---|---|---|
| Campo novo em Agent/Space/Event/Seal/Subsite (instância/tema) | **`*-types.php`** | crie `agent-types.php` no tema (sobrescreve `src/conf/agent-types.php` pela hierarquia de paths — `App.php:3658-3684`); chaves `metadata` (global da entidade) e `items.{tipo}.metadata` (por tipo) |
| Campo do tema/core em runtime | hook `app.register` | `$app->registerMetadata(new Definitions\Metadata($key, $config), Entidade::class)` (padrão `src/core/Theme.php:157-162`) |
| Campo de módulo/plugin | `register()`/`_init()` do módulo | idem |
| Campo de método de avaliação | `EvaluationMethod` | `src/core/EvaluationMethod.php:1948` |
| Campo de formulário de edital | **NÃO registre manualmente** | criado pelo form builder; `Opportunity::registerRegistrationMetadata()` registra em runtime os `RegistrationFieldConfiguration` como metadados de Registration (`src/core/Entities/Opportunity.php:1648-1767`) |

## 2. A definição (config)

```php
new \MapasCulturais\Definitions\Metadata('idade', [
    'label' => 'Idade',
    'type' => 'select',            // string|select|boolean|json|array|object|multiselect|location|bankFields|DateTime|entity...
    'private' => true,             // cortado de serialização/filtros sem viewPrivateData
    'validations' => [
        'required' => 'Idade obrigatória',
        'v::intVal()->min(18)' => 'Deve ser maior de 18',  // sintaxe Respect/Validation string
        // 'unique' => '...' também suportado (validação de unicidade global)
    ],
    'available_for_opportunities' => true,  // oferecido no form builder de editais
    'serialize' => fn($v) => ..., 'unserialize' => fn($v) => ...,  // opcionais; defaults por tipo em Definitions/Metadata.php:232-386
    'options' => [...], 'sensitive' => true, 'readonly' => true,
]);
```
Construtor completo: `src/core/Definitions/Metadata.php:167-225`.

## 3. Usar (magic property)

```php
$agent->idade = 30;        // __metadata__set → serialize → setMetadata (throw se não registrada: Traits/EntityMetadata.php:328-331)
echo $agent->idade;        // __metadata__get → unserialize
$entity->save();           // saveMetadata() persiste SÓ os alterados (Entity.php:1245-1247)
```

## 4. Onde persiste

Tabela `{entidade}_meta (object_id, key, value text)` — ex. `agent_meta` (`src/core/Entities/AgentMeta.php:12-22`, índices `(object_id, key)` e unique funcional). Entidades sem classe Meta dedicada usam a polimórfica `metadata` (usada na validação `unique` — `Definitions/Metadata.php:473-477`). Gravação é deduplicada por `(key, object_id)` (`src/core/EntityMetadata.php:38-61`).

## 5. Como aparece na API

- **Valor**: incluído automaticamente no `jsonSerialize` se registrado e não-privado (`src/core/Entity.php:1370-1381`); corte de privados na hidratação em lote (`src/core/ApiQuery.php:1772-1804`).
- **Filtro**: `?idade=GT(18)` — join automático `LEFT JOIN e.__metadata m WITH m.key='idade'` (`ApiQuery.php:527, 3936-3965`). Metadados `multiselect|array|json` convertem `IN(...)`→`JSON_IN(...)` automaticamente (3948-3955).
- **Ordenação**: `@order=idade` ordena como TEXTO; para numérico use `@order=idade AS INTEGER` (CAST com `NULLIF`, `ApiQuery.php:1558-1617`).
- **Schema**: `GET /api/agent/describe` expõe `isMetadata:true` + label/tipo (`src/core/Traits/ControllerAPI.php:518-554`).
- Filtro por metadado **não-registrado lança `PropertyDoesNotExists`** (`ApiQuery.php:3738-3739`).

## 6. Armadilhas

1. **Metadado não registrado = exceção no set** — registre antes (canal §1); registros no banco de chaves removidas ficam órfãos (leitura deduplica em runtime, `Traits/EntityMetadata.php:234-253`).
2. `value` é text: comparações/ordenações numéricas dependem de CAST (§5).
3. Multi-valorado por chave NÃO é suportado (unique `(object_id,key)`) — use `multiselect`/JSON.
4. `private` corta o VALOR da saída, mas o metadado ainda pode ser usado como filtro.
5. Campo de edital NÃO é metadado de Agent — é `RegistrationFieldConfiguration` que registra metadado de Registration em runtime; em testes, `RegistrationBuilder::reset` chama `registerRegistrationMetadata` antes de instanciar (`tests/src/Builders/RegistrationBuilder.php:19-27`).
6. Hooks por chave: `entity({X}).meta({key}).insert|update|remove:before/:after` existem **por classe `{X}Meta`** — classe Meta nova precisa copiar o bloco de callbacks (base NÃO define; `src/core/Entities/AgentMeta.php:40-61`).

Evidência-base: rastreios R1 §4, R3 §4, R6 §3; exemplo executável `tests/src/ApiTest.php:53-68`.
