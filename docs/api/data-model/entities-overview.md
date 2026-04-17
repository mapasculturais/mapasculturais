# Visão Geral das Entidades

Visão geral de todas as 30 entidades do sistema Mapas Culturais.

> **Regra de roteamento**: Métodos `API_*` usam `/api/`. Métodos `POST_`/`PUT_`/`PATCH_`/`DELETE_` não usam `/api/`.

## Entidades Principais

| # | Entidade | Tabela | Descrição | Propriedades Chave | Relações Chave |
|---|----------|--------|-----------|--------------------|----------------|
| 1 | **Agent** | `agent` | Pessoas e organizações | `name`, `shortDescription`, `type`, `_geoLocation` | `user`, `spaces`, `events`, `projects` |
| 2 | **Space** | `space` | Espaços culturais (venues) | `name`, `public`, `type`, `_geoLocation` | `owner` (Agent), `eventOccurrences` |
| 3 | **Event** | `event` | Eventos culturais | `name`, `shortDescription`, `rules`, `project` | `project`, `occurrences`, `owner` (Agent) |
| 4 | **Project** | `project` | Projetos culturais | `name`, `shortDescription`, `startsOn`, `endsOn` | `owner` (Agent), `events`, `opportunities` |
| 5 | **Opportunity** | `opportunity` | Oportunidades (editais, concursos). **Abstract**, SINGLE_TABLE via `object_type` | `name`, `registrationFrom`, `registrationTo`, `objectType`, `parent` | `ownerEntity`, `parent`, `evaluationMethodConfiguration` |
| 6 | **Registration** | `registration` | Inscrições em oportunidades | `number`, `category`, `status`, `consolidatedResult`, `score` | `opportunity`, `owner` (Agent) |
| 7 | **Seal** | `seal` | Selos / certificações | `name`, `validPeriod`, `certificateText` | `owner` (Agent) |

## Configuração e Gestão

| # | Entidade | Tabela | Descrição | Propriedades Chave | Relações Chave |
|---|----------|--------|-----------|--------------------|----------------|
| 8 | **Subsite** | `subsite` | Subsites (instâncias temáticas) | `name`, `url`, `aliasUrl` | `owner` (Agent) |
| 9 | **User** | `usr` | Contas de usuário | `email`, `authProvider`, `authUid`, `status` | `profile` (Agent), `roles` |
| 10 | **EvaluationMethodConfiguration** | `evaluation_method_configuration` | Configuração de método de avaliação | `type`, `opportunity` (FK), `evaluationFrom`, `evaluationTo` | `opportunity` |
| 11 | **Notification** | `notification` | Notificações do sistema | `message`, `status` | `user`, `request` |
| 12 | **Request** | `request` | Requisições internas. **Abstract**, SINGLE_TABLE via `type` | `requestType`, `originType`, `originId`, `destinationType`, `destinationId` | `requesterUser` |

## Entidades de Inscrição

| # | Entidade | Tabela | Descrição | Propriedades Chave | Relações Chave |
|---|----------|--------|-----------|--------------------|----------------|
| 13 | **EventOccurrence** | `event_occurrence` | Ocorrências/agendamento de eventos | `startsOn`, `endsOn`, `startsAt`, `endsAt`, `frequency`, `price` | `event`, `space` |
| 14 | **RegistrationEvaluation** | `registration_evaluation` | Avaliações de inscrições | `evaluationData`, `result`, `status`, `isTiebreaker`, `committee` | `registration`, `user` |
| 15 | **RegistrationFieldConfiguration** | `registration_field_configuration` | Campos de formulário de inscrição | `title`, `fieldType`, `required`, `config`, `fieldOptions`, `displayOrder` | `owner` (Opportunity), `step` |
| 16 | **RegistrationFileConfiguration** | `registration_file_configuration` | Arquivos exigidos em inscrições | `title`, `required`, `displayOrder`, `categories` | `owner` (Opportunity), `step` |
| 17 | **RegistrationStep** | `registration_step` | Etapas do formulário de inscrição | `name`, `displayOrder`, `metadata` | `opportunity` |

## Entidades Auxiliares

| # | Entidade | Tabela | Descrição | Propriedades Chave | Relações Chave |
|---|----------|--------|-----------|--------------------|----------------|
| 18 | **Metadata** | `metadata` | Metadados chave-valor (EAV) | `key`, `value`, `objectType`, `objectId` | `owner` (polimórfico) |
| 19 | **Role** | `role` | Papéis de usuário | `name` | `user`, `subsite` |
| 20 | **Term** | `term` | Termos de taxonomia | `taxonomy`, `term`, `description` | `relations` (TermRelation[]) |
| 21 | **TermRelation** | `term_relation` | Relação entre termos e entidades. **Abstract**, SINGLE_TABLE via `object_type` | `objectId`, `objectType` | `term` |
| 22 | **AgentRelation** | `agent_relation` | Relação entre agentes e entidades. **Abstract**, SINGLE_TABLE via `object_type` | `objectId`, `objectType`, `group`, `hasControl`, `status` | `agent` |
| 23 | **SealRelation** | `seal_relation` | Relação entre selos e entidades. **Abstract**, SINGLE_TABLE via `object_type` | `objectId`, `objectType`, `validateDate` | `seal`, `agent` |
| 24 | **EventAttendance** | `event_attendance` | Presenças/interesse em eventos | `type` (confirmation/interested), `startTimestamp`, `endTimestamp` | `user`, `event`, `_eventOccurrence` |
| 25 | **ChatThread** | `chat_thread` | Tópicos de conversa | `objectId`, `objectType`, `identifier`, `type`, `description` | `ownerEntity` (polimórfico) |
| 26 | **ChatMessage** | `chat_message` | Mensagens em tópicos | `payload`, `createTimestamp` | `thread`, `user` |
| 27 | **Procuration** | `procuration` | Procurações (delegação de ações) | `token`, `action`, `validUntilTimestamp` | `user`, `attorney` |
| 28 | **EntityRevision** | `entity_revision` | Histórico de revisões de entidades | `objectId`, `objectType`, `action`, `message`, `createTimestamp` | - |
| 29 | **MetaList** | `MetaList` | Listas de metadados (links, vídeos, etc.) | `group`, `title`, `value`, `description` | `owner` (polimórfico) |
| 30 | **DbUpdate** | `db_update` | Controle de atualizações do banco | `name` | - |

## Herança SINGLE_TABLE

Entidades marcadas como **abstract SINGLE_TABLE** usam uma coluna discriminadora para mapear subclasses na mesma tabela:

| Entidade | Coluna Discriminadora | Subclasses |
|----------|----------------------|------------|
| Opportunity | `object_type` | `ProjectOpportunity`, `SpaceOpportunity`, `EventOpportunity`, `AgentOpportunity`, `Opportunity` |
| Request | `type` | `RequestAgentRelation`, `RequestSealRelation`, `RequestSpaceRelation`, `RequestEventProject`, `RequestEventOccurrence`, etc. |
| AgentRelation | `object_type` | `AgentAgentRelation`, `SpaceAgentRelation`, `EventAgentRelation`, `ProjectAgentRelation`, etc. |
| SealRelation | `object_type` | `AgentSealRelation`, `SpaceSealRelation`, `EventSealRelation`, `ProjectSealRelation`, etc. |
| TermRelation | `object_type` | `AgentTermRelation`, `SpaceTermRelation`, `EventTermRelation`, `ProjectTermRelation`, etc. |
