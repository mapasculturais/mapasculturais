<?php

namespace OpportunityAppealPhase\Controllers;

use DateTime;
use MapasCulturais\App;
use MapasCulturais\Controllers\EntityController;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\RegistrationEvaluation;
use MapasCulturais\Entities\User;
use MapasCulturais\i;
use MapasCulturais\Traits;
use OpportunityAppealPhase\AppealReview\Notifier;
use OpportunityAppealPhase\Entities\RegistrationAppealReview as ReviewEntity;

/**
 * Controller da entidade RegistrationAppealReview: API de designação de
 * corretores por slot (PR6 / issue #40).
 *
 * Contrato consumido pelo modal e pelo painel de acompanhamento do F2 (#18):
 * - POST   /registrationappealreview                           → POST_index (designação pelo gestor)
 * - GET    /api/registrationappealreview/find?registration=…   → API_find (listagem por inscrição, CA-13)
 * - GET    /api/registrationappealreview/findOne?id=…          → API_findOne
 * - PUT    /registrationappealreview/single/{id}               → PUT_single (substituir corretor/prazo)
 * - PATCH  /registrationappealreview/single/{id}               → PATCH_single
 * - DELETE /registrationappealreview/single/{id}               → DELETE_single (cancelar designação)
 * - POST   /registrationappealreview/reopen/{id}               → POST_reopen (reabrir correção enviada)
 * - POST   /registrationappealreview/deadline/{id}             → POST_deadline (alterar prazo de designação ativa)
 *
 * Segurança: a entidade não usa owner nem permission cache, então o pseudo-owner
 * das checagens genéricas é o próprio usuário logado e as ações canônicas herdadas
 * de EntityController não restringem por si. Todos os pontos de leitura/escrita
 * deste controller exigem autenticação e `@control` na oportunidade principal
 * (fase avaliativa) da inscrição do slot — via checkPermission, nunca ad-hoc.
 */
class RegistrationAppealReview extends EntityController
{
    use Traits\ControllerAPI;

    /**
     * Campos da designação derivados do slot no servidor: nunca aceitos do payload.
     */
    private const DERIVED_FIELDS = ['registration', 'appealPhase', 'slotOwnerUser'];

    /**
     * Cria uma designação de correção para um slot de avaliação (CA-4).
     *
     * Somente gestores com `@control` na oportunidade principal. O corretor
     * informado precisa estar entre `eligibleCorrectors()` do slot (CA-3).
     * Os campos de vinculação (registration, appealPhase, slotOwnerUser) são
     * derivados do slot no servidor; valores divergentes no payload são
     * rejeitados. O status inicial é sempre STATUS_DESIGNATED.
     */
    function POST_index($data = null)
    {
        $this->requireAuthentication();
        $this->assertFeatureEnabled();

        $app = App::i();

        if (is_null($data)) {
            $data = $this->postData;
        }

        if (!is_array($data)) {
            $this->errorJson(i::__('Corpo da requisição inválido: esperado objeto com os campos da designação.'), 400);
        }

        $app->applyHookBoundTo($this, "POST({$this->id}.index):data", ['data' => &$data]);

        $slot_id = $this->resolveIdParam($data['originalEvaluation'] ?? null);
        $corrector_id = $this->resolveIdParam($data['correctorUser'] ?? null);

        if (!$slot_id) {
            $this->errorJson(i::__('O ID da avaliação original (originalEvaluation) é obrigatório.'), 400);
        }

        if (!$corrector_id) {
            $this->errorJson(i::__('O ID do corretor (correctorUser) é obrigatório.'), 400);
        }

        /** @var RegistrationEvaluation|null $slot */
        $slot = $app->repo('RegistrationEvaluation')->find($slot_id);
        if (!$slot) {
            $this->errorJson(sprintf(i::__('Não existe avaliação com o ID %s.'), $slot_id), 404);
        }

        /** @var User|null $corrector */
        $corrector = $app->repo('User')->find($corrector_id);
        if (!$corrector) {
            $this->errorJson(sprintf(i::__('Não existe usuário corretor com o ID %s.'), $corrector_id), 404);
        }

        $registration = $slot->registration;
        $opportunity = $registration->opportunity;

        // Permissão antes de qualquer validação de domínio: não-gestor recebe 403
        // sem vencer informação sobre a inscrição, o slot ou a fase de recurso.
        $opportunity->checkPermission('@control');

        $appeal_phase = $opportunity->appealPhase;

        if (!$appeal_phase) {
            $this->errorJson(i::__('Não existe fase de recurso para esta oportunidade.'), 400);
        }

        // Campos derivados no servidor: divergência no payload é erro de contrato.
        $derived = [
            'registration' => [$registration->id, 'inscrição'],
            'appealPhase' => [$appeal_phase->id, 'fase de recurso'],
            'slotOwnerUser' => [$slot->user->id, 'dono do slot'],
        ];

        foreach ($derived as $key => [$expected_id, $label]) {
            if (isset($data[$key]) && $this->resolveIdParam($data[$key]) !== (int) $expected_id) {
                $this->errorJson(sprintf(i::__('O campo %s não corresponde ao valor derivado da avaliação original (%s).'), $key, $label), 400);
            }
        }

        $review = new ReviewEntity();
        $review->originalEvaluation = $slot;
        $review->registration = $registration;
        $review->appealPhase = $appeal_phase;
        $review->slotOwnerUser = $slot->user;
        $review->correctorUser = $corrector;

        $this->assertCorrectorEligibility($review, $slot, $corrector);

        // O índice único de designações ativas é a regra definitiva; aqui o erro fica claro.
        $active_reviews = $app->repo(ReviewEntity::class)->findBy([
            'originalEvaluation' => $slot->id,
            'status' => ReviewEntity::getActiveStatuses(),
        ]);

        if ($active_reviews) {
            $this->errorJson(i::__('Já existe uma designação ativa para esta avaliação. Substitua ou conclua a designação existente antes de criar outra.'), 400);
        }

        $correction_type = (string) ($data['correctionType'] ?? ReviewEntity::CORRECTION_TYPE_OFFICIAL);
        if (!in_array($correction_type, ReviewEntity::getCorrectionTypes(), true)) {
            $this->errorJson(sprintf(i::__('Tipo de correção inválido (%s). Use "official" ou "record".'), $correction_type), 400);
        }
        $review->correctionType = $correction_type;

        $review->releasedScope = $this->parseReleasedScope($data['releasedScope'] ?? null);
        $review->startsAt = $this->parseDateParam($data['startsAt'] ?? null);
        $review->endsAt = $this->parseDateParam($data['endsAt'] ?? null);
        $this->assertValidWindow($review);
        $review->status = ReviewEntity::STATUS_DESIGNATED;

        $this->_finishRequest($review, true);
    }

    /**
     * Listagem para o painel de acompanhamento do F2 (CA-13).
     *
     * Exige `registration` no query string e `@control` na oportunidade
     * principal da inscrição; o resultado é sempre escopado à inscrição
     * autorizada, independentemente dos filtros enviados.
     */
    function API_find()
    {
        $this->requireAuthentication();
        $this->assertFeatureEnabled();

        $registration = $this->getAuthorizedRegistrationFromQuery();

        $this->getData['registration'] = 'EQ(' . $registration->id . ')';
        $this->applyStatusDomainDefaults();

        $data = $this->apiQuery($this->getData);
        $this->apiResponse($data);
    }

    /**
     * Busca unitária restrita: `@control` na oportunidade principal da
     * inscrição da designação.
     */
    function API_findOne()
    {
        $this->requireAuthentication();
        $this->assertFeatureEnabled();

        $review = $this->getRequestedReviewFromQuery();
        $review->registration->opportunity->checkPermission('@control');

        $this->applyStatusDomainDefaults();

        $data = $this->apiQuery($this->getData, ['findOne' => true]);
        $this->apiItemResponse($data);
    }

    /**
     * Escrita canônica (substituir corretor, novo prazo): restrita a gestores
     * com `@control` na oportunidade principal. Substituição de corretor
     * continua sujeita à elegibilidade do CA-3.
     */
    function PUT_single($data = null)
    {
        $this->requireAuthentication();
        $this->assertFeatureEnabled();

        $review = $this->getRequestedReview();
        $review->registration->opportunity->checkPermission('@control');

        if (is_null($data)) {
            $data = $this->postData;
        }

        $this->guardMutableFields($review, $data);

        parent::PUT_single($data);
    }

    function PATCH_single($data = null)
    {
        $this->requireAuthentication();
        $this->assertFeatureEnabled();

        $review = $this->getRequestedReview();
        $review->registration->opportunity->checkPermission('@control');

        if (is_null($data)) {
            $data = $this->patchData;
        }

        $this->guardMutableFields($review, $data);

        parent::PATCH_single($data);
    }

    /**
     * Cancelamento de designação: restrito a gestores com `@control`
     * (a entidade usa hard delete — sem mudança de status).
     *
     * Designação que já teve envio aplicou a correção no slot e guarda a nota
     * original (originalValue/originalScore): não pode ser apagada.
     */
    function DELETE_single()
    {
        $this->requireAuthentication();
        $this->assertFeatureEnabled();

        $review = $this->getRequestedReview();
        $review->registration->opportunity->checkPermission('@control');

        if ($review->sentTimestamp) {
            $this->errorJson(i::__('Esta correção já foi aplicada e não pode ser cancelada: o registro guarda a nota original do avaliador.'), 400);
        }

        parent::DELETE_single();
    }

    /**
     * Reabre uma designação ENVIADA para o mesmo corretor revisar a correção
     * do slot (POST /registrationappealreview/reopen/{id}).
     *
     * A correção aplicada continua vigente até o novo envio; `sentTimestamp`
     * é mantido como marca de correção já aplicada (colunas de nota original
     * e bloqueio de cancelamento dependem dele). `endsAt` opcional redefine o
     * prazo (null remove); com prazo vencido e sem novo prazo a reabertura é
     * rejeitada.
     */
    function POST_reopen()
    {
        $this->requireAuthentication();
        $this->assertFeatureEnabled();

        $app = App::i();

        $review = $this->getRequestedReview();
        $review->registration->opportunity->checkPermission('@control');

        if ((int) $review->status !== ReviewEntity::STATUS_SENT) {
            $this->errorJson(i::__('Somente correções enviadas podem ser reabertas.'), 400);
        }

        $other_active = $app->repo(ReviewEntity::class)->findBy([
            'originalEvaluation' => $review->originalEvaluation->id,
            'status' => ReviewEntity::getActiveStatuses(),
        ]);

        if ($other_active) {
            $this->errorJson(i::__('Já existe uma designação ativa para esta avaliação. Conclua ou cancele essa designação antes de reabrir a correção enviada.'), 400);
        }

        $data = is_array($this->postData) ? $this->postData : [];

        // endsAt ausente mantém o prazo atual; null/'' remove o prazo.
        if (array_key_exists('endsAt', $data)) {
            $review->endsAt = $this->parseDateParam($data['endsAt']);
        }

        if ($review->endsAt && $review->endsAt < new DateTime()) {
            $this->errorJson(i::__('O prazo desta designação já terminou. Informe um novo prazo futuro ou reabra sem prazo.'), 400);
        }

        $review->status = ReviewEntity::STATUS_REOPENED;
        $review->save(true);

        Notifier::notifyReopened($review);

        $this->json($review);
    }

    /**
     * Altera o prazo final de uma designação ATIVA
     * (POST /registrationappealreview/deadline/{id}); `endsAt` null remove o
     * prazo. O corretor é notificado do novo prazo.
     */
    function POST_deadline()
    {
        $this->requireAuthentication();
        $this->assertFeatureEnabled();

        $review = $this->getRequestedReview();
        $review->registration->opportunity->checkPermission('@control');

        if (!$review->isActive()) {
            $this->errorJson(i::__('O prazo só pode ser alterado em designações ativas. Para correções enviadas, use a reabertura.'), 400);
        }

        $data = is_array($this->postData) ? $this->postData : [];

        if (!array_key_exists('endsAt', $data)) {
            $this->errorJson(i::__('Informe o novo prazo (endsAt) ou null para remover o prazo.'), 400);
        }

        $review->endsAt = $this->parseDateParam($data['endsAt']);
        $this->assertValidWindow($review);
        $review->save(true);

        Notifier::notifyDeadlineChanged($review);

        $this->json($review);
    }

    // ============================================================ //
    // Helpers
    // ============================================================ //

    private function assertFeatureEnabled(): void
    {
        if (!(bool) env('APPEAL_SCORE_CORRECTION', false)) {
            $this->errorJson(i::__('Correção de notas por recurso desabilitada'), 404);
        }
    }

    /**
     * O domínio de status da designação (0=designada, 1=rascunho, 2=enviada,
     * 3=reaberta) colide com a suposição do ApiQuery de que status<=0 é
     * rascunho a esconder (filtro default `e.status > 0`), o que sumiria com
     * as designações recém-criadas do painel do F2.
     *
     * Sem `status` informado, filtramos pelo domínio completo (comportamento
     * equivalente a "sem filtro"); e sem `@permissions`, usamos `view`, que
     * para esta entidade é um no-op (ela não usa permission cache) e serve
     * apenas para desativar o filtro default de status do core. Em conjunto,
     * os dois parâmetros fazem o core pular o `e.status > 0`.
     */
    private function applyStatusDomainDefaults(): void
    {
        if (!isset($this->getData['status'])) {
            $this->getData['status'] = 'IN(' . implode(',', [
                ReviewEntity::STATUS_DESIGNATED,
                ReviewEntity::STATUS_DRAFT,
                ReviewEntity::STATUS_SENT,
                ReviewEntity::STATUS_REOPENED,
            ]) . ')';
        }

        if (!isset($this->getData['@permissions'])) {
            $this->getData['@permissions'] = 'view';
        }
    }

    /**
     * CA-3: por slot, o gestor só pode designar o dono do slot ou membro da
     * Comissão de Recursos da fase de recurso. Avaliadores de outros slots da
     * mesma inscrição são rejeitados.
     */
    private function assertCorrectorEligibility(ReviewEntity $review, RegistrationEvaluation $slot, User $corrector): void
    {
        $eligible_ids = array_map(
            static fn (User $user): int => (int) $user->id,
            $review->eligibleCorrectors($slot)
        );

        if (!$eligible_ids) {
            $this->errorJson(i::__('Esta avaliação não admite designação de correção: exige fase principal com método técnico e fase de recurso ativa com Comissão de Recursos.'), 400);
        }

        if (!in_array((int) $corrector->id, $eligible_ids, true)) {
            $this->errorJson(i::__('Corretor inelegível para esta avaliação (CA-3): apenas o dono da avaliação original ou membros da Comissão de Recursos podem ser designados.'), 400);
        }
    }

    /**
     * Valida os campos mutáveis via PUT/PATCH antes de delegar ao core:
     * - campos derivados do slot são imutáveis;
     * - substituição de corretor exige elegibilidade (CA-3) e é atribuída
     *   como entidade resolvida (não como ID cru);
     * - correctionType válidos;
     * - status restrito ao domínio da designação e aplicado diretamente, para
     *   não colidir com o changeStatusMap do ciclo padrão de Entity.
     */
    private function guardMutableFields(ReviewEntity $review, array &$data): void
    {
        $app = App::i();

        foreach (self::DERIVED_FIELDS as $field) {
            if (array_key_exists($field, $data) && $this->resolveIdParam($data[$field]) !== (int) $review->$field->id) {
                $this->errorJson(sprintf(i::__('O campo %s é derivado da avaliação original e não pode ser alterado.'), $field), 400);
            }

            unset($data[$field]);
        }

        if (array_key_exists('originalEvaluation', $data)) {
            if ($this->resolveIdParam($data['originalEvaluation']) !== (int) $review->originalEvaluation->id) {
                $this->errorJson(i::__('O campo originalEvaluation identifica a avaliação original e não pode ser alterado. Crie uma nova designação.'), 400);
            }

            unset($data['originalEvaluation']);
        }

        if (array_key_exists('correctorUser', $data)) {
            $corrector_id = $this->resolveIdParam($data['correctorUser']);

            if ($corrector_id !== (int) $review->correctorUser->id) {
                /** @var User|null $corrector */
                $corrector = $app->repo('User')->find($corrector_id);

                if (!$corrector) {
                    $this->errorJson(sprintf(i::__('Não existe usuário corretor com o ID %s.'), $corrector_id), 404);
                }

                $this->assertCorrectorEligibility($review, $review->originalEvaluation, $corrector);
                $review->correctorUser = $corrector;
            }

            unset($data['correctorUser']);
        }

        if (isset($data['correctionType'])) {
            if (!in_array((string) $data['correctionType'], ReviewEntity::getCorrectionTypes(), true)) {
                $this->errorJson(sprintf(i::__('Tipo de correção inválido (%s). Use "official" ou "record".'), (string) $data['correctionType']), 400);
            }
        }

        // F6: array_key_exists (não isset) para que releasedScope null LIMPE
        // a restrição na substituição (todos os critérios liberados).
        if (array_key_exists('releasedScope', $data)) {
            $review->releasedScope = $this->parseReleasedScope($data['releasedScope']);
            unset($data['releasedScope']);
        }

        $window_changed = isset($data['startsAt']) || isset($data['endsAt']);

        if (isset($data['startsAt'])) {
            $review->startsAt = $this->parseDateParam($data['startsAt']);
            unset($data['startsAt']);
        }

        if (isset($data['endsAt'])) {
            $review->endsAt = $this->parseDateParam($data['endsAt']);
            unset($data['endsAt']);
        }

        if ($window_changed) {
            $this->assertValidWindow($review);
        }

        if (array_key_exists('status', $data)) {
            $status = (int) $data['status'];

            $valid_statuses = [
                ReviewEntity::STATUS_DESIGNATED,
                ReviewEntity::STATUS_DRAFT,
                ReviewEntity::STATUS_SENT,
                ReviewEntity::STATUS_REOPENED,
            ];

            if (!in_array($status, $valid_statuses, true)) {
                $this->errorJson(i::__('Status inválido para designação de correção.'), 400);
            }

            $review->status = $status;
            unset($data['status']);
        }
    }

    /**
     * Extrai e valida a inscrição do parâmetro `registration` (aceita `EQ(n)`
     * ou inteiro) e exige `@control` na oportunidade principal.
     */
    private function getAuthorizedRegistrationFromQuery(): Registration
    {
        $app = App::i();

        $raw_value = trim((string) ($this->getData['registration'] ?? ''));

        if (!preg_match('/\d+/', $raw_value, $matches)) {
            $this->errorJson(i::__('O parâmetro registration (ID da inscrição) é obrigatório.'), 400);
        }

        /** @var Registration|null $registration */
        $registration = $app->repo('Registration')->find((int) $matches[0]);

        if (!$registration) {
            $this->errorJson(sprintf(i::__('Não existe inscrição com o ID %s.'), $matches[0]), 404);
        }

        $registration->opportunity->checkPermission('@control');

        return $registration;
    }

    /**
     * Entidade da requisição para ações single (PUT/PATCH/DELETE).
     */
    private function getRequestedReview(): ReviewEntity
    {
        $app = App::i();

        $review = $this->requestedEntity;

        if (!$review) {
            $app->pass();
        }

        return $review;
    }

    /**
     * Carrega a designação referenciada por `id` (formato API `EQ(n)`).
     */
    private function getRequestedReviewFromQuery(): ReviewEntity
    {
        $app = App::i();

        $raw_value = trim((string) ($this->getData['id'] ?? ''));

        if (!preg_match('/\d+/', $raw_value, $matches)) {
            $this->errorJson(i::__('O parâmetro id é obrigatório.'), 400);
        }

        /** @var ReviewEntity|null $review */
        $review = $app->repo(ReviewEntity::class)->find((int) $matches[0]);

        if (!$review) {
            $app->pass();
        }

        return $review;
    }

    /**
     * Aceita int, string numérica ou objeto/array com chave `id`.
     */
    private function resolveIdParam(mixed $value): int
    {
        if (is_array($value) || is_object($value)) {
            $value = ((array) $value)['id'] ?? null;
        }

        return (int) $value;
    }

    /**
     * @param mixed $value array/objeto já decodificado ou string JSON.
     *
     * F6 (#49): escopo com lista de critérios explícita e vazia é rejeitado
     * (null = sem restrição; ao menos um critério quando houver restrição).
     */
    private function parseReleasedScope(mixed $value): ?object
    {
        if ($value === null || $value === '') {
            return null;
        }

        $scope = null;

        if (is_string($value)) {
            $decoded = json_decode($value);

            if (!is_object($decoded)) {
                $this->errorJson(i::__('releasedScope inválido: esperado objeto JSON.'), 400);
            }

            $scope = $decoded;
        } else {
            $scope = (object) (array) $value;
        }

        $criteria = $scope->criteria ?? $scope->fields ?? null;

        if ($criteria !== null && count((array) $criteria) === 0) {
            $this->errorJson(i::__('O escopo de critérios não pode ser vazio: selecione ao menos um critério ou envie a designação sem restrição (releasedScope nulo libera todos).'), 400);
        }

        if (property_exists($scope, 'formFields')
            && $scope->formFields !== null
            && count((array) $scope->formFields) === 0
        ) {
            $this->errorJson(i::__('O escopo de campos do formulário não pode ser vazio: selecione ao menos um campo ou omita formFields para liberar todos.'), 400);
        }

        return $scope;
    }

    /**
     * Janela de correção (CA-4): prazo final no futuro e início não posterior a ele.
     */
    private function assertValidWindow(ReviewEntity $review): void
    {
        if ($review->endsAt && $review->endsAt < new DateTime()) {
            $this->errorJson(i::__('O prazo final da correção não pode estar no passado.'), 400);
        }

        if ($review->startsAt && $review->endsAt && $review->startsAt > $review->endsAt) {
            $this->errorJson(i::__('O início da correção não pode ser posterior ao prazo final.'), 400);
        }
    }

    private function parseDateParam(mixed $value): ?DateTime
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return new DateTime((string) $value);
        } catch (\Exception) {
            $this->errorJson(i::__('Data inválida: use o formato AAAA-MM-DD HH:MM.'), 400);

            return null;
        }
    }
}
