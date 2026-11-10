# Componente `<opportunity-appeal-correction-assignment>`

Modal de designação de corretores por slot (F2 / issue #18, épica #7 — RF-A30).
Lista as avaliações da fase principal de uma inscrição com recurso deferido,
uma por avaliador, e permite ao gestor marcar quais notas serão corrigidas e
designar **1 corretor por slot** — restrito ao dono do slot e aos membros da
Comissão de Recursos (mesma composição de `RegistrationAppealReview::eligibleCorrectors()`).

Também exibe o acompanhamento por slot (designado / rascunho / enviado /
reaberto, prazo e data de envio), somente leitura. Substituição de corretor e
reabertura não fazem parte do componente.

## Uso

Não recebe props: é aberto programaticamente por evento global (interface
pública consumida pelo F1). A view só precisa importá-lo e renderizá-lo uma
única vez:

```php
<?php $this->import('opportunity-appeal-correction-assignment'); ?>
<opportunity-appeal-correction-assignment></opportunity-appeal-correction-assignment>
```

Abertura (assinatura documentada também no `init.php`):

```js
window.dispatchEvent(new CustomEvent(
    'opportunity-appeal-correction-assignment:open',
    {
        detail: {
            opportunity: Entity|{id},   // fase principal (avaliativa) da inscrição
            registration: Entity|{id, number?}, // inscrição da fase principal (não a da fase de recurso)
        }
    }
));
```

## Eventos emitidos

| Evento   | Payload | Quando |
|----------|---------|--------|
| `saved`  | `{registrationId}` | Após criar todas as designações marcadas com sucesso |

## Integração F1 (#17) — botão na lista de inscritos da fase de recurso

Override 2026-09-08 (épica #7): a coluna "Designar correção" vive na lista de
inscritos **da fase de recurso** (`opportunity-registrations-table`), não na da
fase principal. Cada linha é um recurso; o botão aparece **somente nas linhas
com status Deferido (10)** — valor nativo da lista, sem fetch extra.

O `init.php` popula o config quando a oportunidade em contexto **é a própria
fase de recurso** (ativa, com EMC, fase pai técnica, `@control` na fase pai):

- `appealContexts[appealPhaseId] = {mainPhaseId}` — gate da coluna na tabela;
- `appealPhases[mainPhaseId]`, `committees[mainPhaseId]` e
  `evaluators[mainPhaseId]` — consumo do modal, que recebe a fase principal
  no evento de abertura e a usa como chave.

No clique, o F1 deriva `{opportunity: fase principal, registration: inscrição
da fase principal}` a partir da linha do recurso: a inscrição de recurso herda
o `number` da inscrição principal (`createAppealPhaseRegistration`,
`OpportunityAppealPhase/Module.php:201`) e não há meta/relation armazenada
ligando as duas — o casamento por `number` na fase pai é o mecanismo canônico
do backend (`Module.php:240-243`), replicado aqui em 1 chamada cacheada por
inscrição.

## Fontes de dados (somente endpoints existentes)

| Dado | Fonte | Observação |
|------|-------|------------|
| Slots da inscrição | `GET /api/registrationevaluation/find` (`registration=EQ(id)`) | API filtra por permissão de visão; leitura **raw** obrigatória (`raw: true` — `rawProcessor` sozinho não ativa o modo raw e o `populate` do SDK descarta relações escalares); a relação `user` volta sempre ESCALAR (não expande) |
| Comissão de Recursos (`committees`) + avaliadores da fase principal (`evaluators`, mapa `{userId: name}`) + fase de recurso (`appealPhases`) + contexto da coluna (`appealContexts`) | Injeção do `init.php` (`$MAPAS.config.appealCorrectionAssignment`) | Espelha os gates de `eligibleCorrectors()`; exige `@control` na fase pai; `evaluators` é a fonte dos nomes dos donos de slot (a API de avaliação não expõe nome) |
| Criação/leitura de designações | API canônica de `registrationappealreview` | **Disponível somente quando `endpointAvailable`** — requer controller registrado para a entidade no backend |

### Estado bloqueado (`endpointAvailable = false`)

Enquanto a entidade `RegistrationAppealReview` não tiver controller registrado
no backend (criação é backend de out of scope da F2), salvar e acompanhar
ficam desabilitados: o modal exibe aviso explícito e o botão de salvar fica
inativo. O restante da interface (lista de slots e opções de corretor) opera
normalmente. Quando o controller existir, os fluxos ativam-se sem alteração
neste componente.

## Estilo

A estrutura do diálogo vem do `mc-modal` (`.modal-content`/`.modal__*` + botões
`.button--primary`/`.button--text`, responsivo). O conteúdo interno usa classes
utilitárias do tema (`.semibold`, `.{primary|success|warning|danger}__color`,
`.warning__background`) e o `style.css` local do componente (auto-enfileirado,
sem passo de build — mesmo padrão do `mc-modal`), com tokens `--mc-*`.

## Gestão de designação ativa (F5 / #45, CA-13)

No painel de acompanhamento, slot com designação **ativa** (status
designado/rascunho/reaberto — `activeStatuses` do config) exibe o corretor
atual e duas ações:

- **Substituir corretor** — select inline com os elegíveis daquele slot
  (mesma fonte do fluxo de designação) → `PATCH
  /registrationappealreview/single/{id}` com `{correctorUser}`; o backend
  revalida CA-3 (inelegível → 400 com mensagem exibida via
  `backendErrorMessage`). Sucesso → toast + `fetchReviews()` reflete o novo
  corretor sem reload.
- **Cancelar designação** — confirmação com `mc-confirm-button` → `DELETE
  /registrationappealreview/single/{id}`; o slot volta ao estado designável
  (checkbox destravado, "sem designação").

Slot com status **enviado** (2) não exibe ações.

## Escopo de critérios (F6 / #49, Variante 3)

Elegibilidade aberta a **todos** os métodos de avaliação (gate técnico
removido de `eligibleCorrectors()` e do `init.php`). O seletor de escopo
(`criteriaCatalogs` do config, injetado por `init.php`) existe somente para:

| Método | Seletor | Fonte dos itens |
|--------|---------|-----------------|
| Técnica | Sim — seções/critérios do EMC principal | `EvaluationMethodTechnical` (chave do evaluationData = id do critério) |
| Qualificação documental | Sim — campos e documentos das fases | `EvaluationMethodDocumentary` (chave = `fieldName`/`fileGroupName`) |
| Demais (simples etc.) | Não — nada renderiza | `releasedScope` nulo ("abre tudo") |

**Semântica persistida em `releasedScope`**: TODOS os critérios marcados →
`null` (= sem restrição, compatível com `getReleasedCriteriaIds()` do
`AppealReview\Service`); subconjunto → `{criteria: [...]}`; seleção VAZIA é
rejeitada — no cliente (salvar bloqueado com aviso) e no backend
(`parseReleasedScope` → 400 "O escopo de critérios não pode ser vazio…").
Na substituição (F5) a checklist vem pré-preenchida com o escopo vigente e o
PATCH envia `releasedScope` junto (`null` LIMPA a restrição — o controller
usa `array_key_exists`). O painel de acompanhamento exibe "X de Y critérios"
(restrito) ou "todos liberados" (nulo).

## Defaults de criação

Por linha marcada cria-se: `status=DESIGNATED`, `correctionType=official`
(correção oficial), prazo e escopo de critérios livres (`null`). A
parametrização completa (prazo, escopo, `record`, visibilidade do parecer) é
tratada em outra onda da épica (CA-4).
