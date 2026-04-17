# Matriz de Traits por Entidade

Indica quais traits cada entidade utiliza.

| Trait | Agent | Space | Event | Project | Opportunity | Registration | Seal | Subsite | User | EvalMethodConfig | Notification | EventOccurrence | RegEvaluation | RegFieldConfig | ChatMessage |
|-------|:-----:|:-----:|:-----:|:-------:|:-----------:|:------------:|:----:|:-------:|:----:|:----------------:|:------------:|:--------------:|:-------------:|:--------------:|:-----------:|
| **EntityTypes** | ✓ | ✓ | ✓ | ✓ | ✓ | | | | | ✓ | | | | | |
| **EntityMetadata** | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | | | | | |
| **EntityFiles** | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | | | | ✓ | | ✓ |
| **EntityAvatar** | ✓ | ✓ | ✓ | ✓ | ✓ | | ✓ | | | | | | | | |
| **EntityMetaLists** | ✓ | ✓ | ✓ | ✓ | ✓ | | ✓ | ✓ | | | | | | | |
| **EntityGeoLocation** | ✓ | ✓ | | | | | | | | | | | | | |
| **EntityTaxonomies** | ✓ | ✓ | ✓ | ✓ | ✓ | | | | | | | | | | |
| **EntityAgentRelation** | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | | | ✓ | | | | | |
| **EntitySealRelation** | ✓ | ✓ | ✓ | ✓ | ✓ | | | | | | | | | | |
| **EntityNested** | ✓ | ✓ | | ✓ | ✓ | | | | | | | | | | ✓ |
| **EntityOwnerAgent** | | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | | | | | | | |
| **EntitySoftDelete** | ✓ | ✓ | ✓ | ✓ | ✓ | | ✓ | ✓ | ✓ | | | | | | |
| **EntityDraft** | ✓ | ✓ | ✓ | ✓ | ✓ | | ✓ | ✓ | | | | | | | |
| **EntityPermissionCache** | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | | ✓ | ✓ | ✓ | | | | ✓ |
| **EntityOriginSubsite** | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | | | | | | | | | |
| **EntityArchive** | ✓ | ✓ | ✓ | ✓ | ✓ | | ✓ | ✓ | | | | | | | |
| **EntityRevision** | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | | ✓ | ✓ | | | ✓ | | |
| **EntityPrivate** | ✓ | ✓ | ✓ | ✓ | ✓ | | | | | | | | | | |
| **EntityLock** | ✓ | ✓ | ✓ | ✓ | ✓ | | | | | | | | | | |
| **EntityOpportunities** | ✓ | ✓ | ✓ | ✓ | | | | | | | | | | | |

### Legenda dos Traits

| Trait | Descrição |
|-------|-----------|
| **EntityTypes** | Suporte a tipos/subtipos de entidade |
| **EntityMetadata** | Metadados chave-valor dinâmicos (tabela `metadata`) |
| **EntityFiles** | Upload e gerenciamento de arquivos |
| **EntityAvatar** | Imagem de perfil/avatar específica |
| **EntityMetaLists** | Listas de metadados (links, vídeos, etc.) |
| **EntityGeoLocation** | Localização geográfica (latitude/longitude) |
| **EntityTaxonomies** | Categorização por taxonomias (termos) |
| **EntityAgentRelation** | Relacionamentos com agentes (grupos, controle) |
| **EntitySealRelation** | Relacionamento com selos/certificações |
| **EntityNested** | Suporte a entidades filhas (parent/child) |
| **EntityOwnerAgent** | Propriedade de um agente (campo `agent_id`) |
| **EntitySoftDelete** | Exclusão suave (status -10 = lixeira) |
| **EntityDraft** | Suporte a rascunho (status -1) |
| **EntityPermissionCache** | Cache de permissões por usuário |
| **EntityOriginSubsite** | Rastreamento de subsite de origem |
| **EntityArchive** | Suporte a arquivar/desarquivar |
| **EntityRevision** | Histórico de revisões (audit trail) |
| **EntityPrivate** | Suporte a entidades privadas |
| **EntityLock** | Bloqueio de edição por outros usuários |
| **EntityOpportunities** | Pode receber oportunidades vinculadas |
