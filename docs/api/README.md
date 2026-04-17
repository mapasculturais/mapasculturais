# API Mapas Culturais 8.0

## Visão Geral

A API do Mapas Culturais fornece acesso programático a todos os dados da plataforma: agentes, espaços, eventos, projetos, oportunidades, inscrições, selos e mais.

**Base URL**: `{instalacao}/api/`

**Versão**: 8.0 (branch `develop-8.0`)

## Arquitetura

```
Requisição HTTP
    │
    ▼
RoutesManager (detecta prefixo /api/)
    │
    ▼
Controller::callAction('API', $action, $args)
    │
    ▼
API_{action}() no controller
    │
    ▼
ApiQuery (converte parâmetros → DQL Doctrine)
    │
    ▼
ApiOutput (formata resposta: json, html, excel, etc.)
```

### Como funciona o roteamento

1. Toda URL com prefixo `/api/` é tratada como chamada de API
2. O `RoutesManager` extrai o controller e a ação da URL
3. Para chamadas API, o método é sempre `API_`, então:
   - `/api/agent/find` chama `AgentController::API_find()`
   - `/api/agent/describe` chama `AgentController::API_describe()`
   - `/api/opportunity/findRegistrations` chama `OpportunityController::API_findRegistrations()`
4. **Apenas métodos `API_*` usam o prefixo `/api/`**. Métodos `POST_*`, `PUT_*`, `PATCH_*` e `DELETE_*` **não** possuem o prefixo:
   - `POST /agent/` chama `AgentController::POST_index()`
   - `POST /agent/1` chama `AgentController::POST_single()`
   - `PUT /space/1` chama `SpaceController::PUT_single()`
   - `DELETE /seal/1` chama `SealController::DELETE_single()`
5. Controllers que não implementam `usesAPI()` retornam 404 para chamadas `/api/`

### Herança de Controllers

```
Controller
├── SiteController
├── UserController
├── EvaluationMethodConfigurationController
└── EntityController (abstrato)
    ├── AgentController
    ├── SpaceController
    ├── EventController
    ├── ProjectController
    ├── OpportunityController
    ├── RegistrationController
    ├── SealController
    ├── SubsiteController
    ├── NotificationController
    ├── TermController
    └── ...
```

### Traits que fornecem endpoints

| Trait | Endpoints |
|-------|-----------|
| `ControllerAPI` | `API_find`, `API_findOne`, `API_distinct`, `API_countGrouped`, `API_describe`, `API_filters` |
| `ControllerEntityActions` | `POST_index`, `POST_single`, `PUT_single`, `PATCH_single`, `DELETE_single` |
| `ControllerAPINested` | `API_getChildrenIds` |
| `ControllerUploads` | `POST_upload` |
| `ControllerTypes` | `API_getTypes`, `API_getTypeGroups` |
| `ControllerAgentRelation` | `POST_createAgentRelation`, `POST_removeAgentRelation`, etc. |
| `ControllerSealRelation` | `POST_createSealRelation`, `POST_removeSealRelation`, etc. |
| `ControllerMetaLists` | `POST_metalist` |
| `ControllerChangeOwner` | `POST_changeOwner` |
| `ControllerLock` | `POST_renewLock` |
| `ControllerDraft` | `ALL_publish`, `ALL_unpublish` |
| `ControllerArchive` | Arquivar/desarquivar |
| `ControllerSoftDelete` | Lixeira |
| `ControllerPrivateEntity` | Entidades privadas |
| `ControllerSubSiteAdmin` | Gestão de admins de subsite |

## Índice da Documentação

### Conceitos
- [Autenticação](./authentication.md) - JWT via UserApp
- [Sintaxe de Consultas](./query-syntax.md) - @select, @order, operadores, paginação, distinct, countGrouped
- [Formatos de Saída](./output-formats.md) - json, html, excel, dump
- [Primeiros Passos](./getting-started.md) - Exemplos básicos

### Entidades Principais
- [Agentes](./endpoints/agent.md) - Pessoas físicas e coletivos
- [Espaços](./endpoints/space.md) - Locais culturais
- [Eventos](./endpoints/event.md) - Eventos e ocorrências
- [Projetos](./endpoints/project.md) - Projetos culturais
- [Oportunidades](./endpoints/opportunity.md) - Editais, concursos, inscrições
- [Inscrições](./endpoints/registration.md) - Gestão de inscrições
- [Selos](./endpoints/seal.md) - Certificações
- [Subsites](./endpoints/subsite.md) - Instalações
- [Usuários](./endpoints/user.md) - Contas de usuário

### Entidades Secundárias
- [Site](./endpoints/site.md) - Informações da instalação
- [Termos](./endpoints/term.md) - Taxonomias
- [Notificações](./endpoints/notification.md)
- [Ocorrências de Evento](./endpoints/event-occurrence.md)
- [Presença em Eventos](./endpoints/event-attendance.md)
- [Chat](./endpoints/chat-thread.md)
- [Arquivos](./endpoints/file.md)
- [Configuração de Avaliação](./endpoints/evaluation-method-configuration.md)

### Funcionalidades Compartilhadas
- [CRUD](./shared-features/crud.md) - Criar, ler, atualizar, deletar
- [Uploads](./shared-features/uploads.md) - Upload de arquivos
- [Relações de Agente](./shared-features/agent-relations.md)
- [Relações de Selo](./shared-features/seal-relations.md)
- [Meta Lists](./shared-features/metalists.md)
- [Tipos](./shared-features/types-api.md)
- [Entidades Aninhadas](./shared-features/nested-entities.md)
- [Mudança de Dono](./shared-features/change-owner.md)
- [Locks](./shared-features/locks.md)
- [Rascunho/Publicação](./shared-features/draft.md)
- [Arquivo](./shared-features/archive.md)
- [Lixeira](./shared-features/soft-delete.md)
- [Entidades Privadas](./shared-features/private-entity.md)

### Definições de Tipos
- [Tipos de Agente](./types/agent-types.md)
- [Tipos de Espaço](./types/space-types.md)
- [Tipos de Evento](./types/event-types.md)
- [Tipos de Projeto](./types/project-types.md)
- [Tipos de Oportunidade](./types/opportunity-types.md)
- [Tipos de Selo](./types/seal-types.md)
- [Taxonomias](./types/taxonomies.md)

### Modelo de Dados
- [Visão Geral das Entidades](./data-model/entities-overview.md)
- [Matriz de Traits](./data-model/trait-matrix.md)

### Exemplos
- [Consultas Básicas](./examples/basic-query.md)
- [Consultas Avançadas](./examples/advanced-query.md)
- [Paginação](./examples/pagination.md)
- [Criar Entidades](./examples/creating-entities.md)
- [Fluxo de Inscrição](./examples/registration-workflow.md)
- [Fluxo de Avaliação](./examples/evaluation-workflow.md)

## Resumo Estatístico

| Categoria | Total |
|-----------|-------|
| Controllers core | 20 |
| Controllers de módulos | 9 |
| Endpoints próprios | 76 |
| Endpoints via traits (herdados) | 31 |
| Endpoints de módulos | 17 |
| **Total de endpoints** | **124** |
| Entidades mapeadas | 30 |
| Taxonomias | 5 |
