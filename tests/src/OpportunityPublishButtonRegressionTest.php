<?php

namespace Tests;

use MapasCulturais\ApiQuery;
use MapasCulturais\App;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Request as MapasRequest;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;

/**
 * Regressão: botão "Salvar e publicar" some em oportunidade rascunho COM EMC.
 *
 * Causa: Theme::addRequestedEntityToJs carrega
 * evaluationMethodConfiguration.{opportunity.*,*} e a opportunity aninhada
 * (mesmo id) traz currentUserPermissions.publish=false via pcache (que não
 * persiste "publish"). O Vue cacheia por opportunity:id e sobrescreve a
 * permissão correta da raiz.
 *
 * Correção: unset da opportunity aninhada quando o id é o mesmo da página.
 *
 * Histórico: o select com opportunity.* foi introduzido em 2ec0c925c
 * ("adiciona informações da opportunity relacionada a
 * evaluationMethodConfiguration no addRequestedEntityToJS", 2025-09-22).
 * Não havia correção prévia para este conflito de permissão.
 */
class OpportunityPublishButtonRegressionTest extends TestCase
{
    use OpportunityBuilder;
    use RequestFactory;
    use UserDirector;

    private function createDraftOpportunityWithEmc(): array
    {
        $owner = $this->userDirector->createUser();
        $this->login($owner);

        $opportunity = $this->opportunityBuilder
            ->reset(
                owner: $owner->profile,
                owner_entity: $owner->profile,
                status: Opportunity::STATUS_DRAFT
            )
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->save()
                ->done()
            ->getInstance();

        $this->processPCache();
        $this->login($owner);

        return [$owner, $opportunity->refreshed()];
    }

    private function buildRequestedEntity(Opportunity $opportunity): array
    {
        $app = App::i();

        $psr = $this->requestFactory->GET('opportunity', 'edit', [$opportunity->id]);
        $app->request = new MapasRequest($psr, 'opportunity', 'edit', ['id' => $opportunity->id]);

        $controller = $app->controller('opportunity');
        $controller->setRequestData(['id' => $opportunity->id]);
        $app->view->controller = $controller;

        $app->view->jsObject['requestedEntity'] = null;
        $app->view->addRequestedEntityToJs(Opportunity::class, $opportunity->id);

        $entity = $app->view->jsObject['requestedEntity'] ?? null;
        $this->assertIsArray($entity, 'requestedEntity deve ser um array após addRequestedEntityToJs');

        return $entity;
    }

    private function projectSrcPath(string $relative): string
    {
        $candidates = [
            dirname(__DIR__, 2) . '/src/' . $relative,
            dirname(__DIR__) . '/src/' . $relative,
            '/var/www/src/' . $relative,
        ];
        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }
        $this->fail("Arquivo de fonte não encontrado: {$relative}");
    }

    public function testOwnerCanPublishDraftOpportunityWithEmcViaCanUser(): void
    {
        [$owner, $opportunity] = $this->createDraftOpportunityWithEmc();

        $this->assertSame(Opportunity::STATUS_DRAFT, $opportunity->status);
        $this->assertNotNull($opportunity->evaluationMethodConfiguration);
        $this->assertTrue(
            $opportunity->canUser('publish', $owner),
            'Dono deve poder publicar no backend (canUser)'
        );
        $this->assertTrue(
            $opportunity->getUserPermissions($owner)['publish'] ?? false,
            'getUserPermissions deve incluir publish=true para o dono'
        );
    }

    public function testApiQueryNestedOpportunityPublishIsFalseFromPcache(): void
    {
        [$owner, $opportunity] = $this->createDraftOpportunityWithEmc();

        $query = new ApiQuery(Opportunity::class, [
            '@select' => '*,evaluationMethodConfiguration.{opportunity.*,*}',
            'id' => "EQ({$opportunity->id})",
            'status' => 'GTE(-20)',
            '@permissions' => 'view',
        ]);
        $query->__useDQLCache = false;
        $payload = $query->findOne();

        $this->assertNotNull($payload['evaluationMethodConfiguration']['opportunity'] ?? null);
        $this->assertSame(
            $opportunity->id,
            (int) ($payload['evaluationMethodConfiguration']['opportunity']['id'] ?? 0)
        );

        // Documenta a assimetria que causa o bug no Vue:
        // raiz corrigida por getUserPermissions vs nested via pcache sem "publish".
        $root_live = $opportunity->getUserPermissions($owner)['publish'] ?? false;
        $nested = $payload['evaluationMethodConfiguration']['opportunity']['currentUserPermissions']['publish'] ?? null;

        $this->assertTrue($root_live, 'Permissão ao vivo do dono deve ser true');
        $this->assertFalse(
            (bool) $nested,
            'Cópia aninhada via ApiQuery/pcache deve vir com publish=false (pcache não guarda publish)'
        );
    }

    public function testRequestedEntityKeepsPublishTrueAndStripsNestedSameOpportunity(): void
    {
        [$owner, $opportunity] = $this->createDraftOpportunityWithEmc();

        $entity = $this->buildRequestedEntity($opportunity);

        $this->assertSame($opportunity->id, (int) ($entity['id'] ?? 0));
        $this->assertTrue(
            (bool) ($entity['currentUserPermissions']['publish'] ?? false),
            'requestedEntity.currentUserPermissions.publish deve permanecer true para o dono'
        );
        $this->assertArrayHasKey(
            'evaluationMethodConfiguration',
            $entity,
            'EMC deve continuar presente no payload'
        );
        $this->assertArrayNotHasKey(
            'opportunity',
            $entity['evaluationMethodConfiguration'] ?? [],
            'Não deve haver opportunity aninhada com o mesmo id (evita colisão no cache Vue)'
        );
    }

    public function testRequestedEntityWithoutEmcStillExposesPublish(): void
    {
        $owner = $this->userDirector->createUser();
        $this->login($owner);

        $opportunity = $this->opportunityBuilder
            ->reset(
                owner: $owner->profile,
                owner_entity: $owner->profile,
                status: Opportunity::STATUS_DRAFT
            )
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->getInstance();

        $this->processPCache();
        $this->login($owner);

        $this->assertNull($opportunity->evaluationMethodConfiguration);

        $entity = $this->buildRequestedEntity($opportunity->refreshed());

        $this->assertTrue(
            (bool) ($entity['currentUserPermissions']['publish'] ?? false),
            'Rascunho sem EMC também deve expor publish=true ao dono'
        );
    }

    public function testSourceGuardPreventsNestedOpportunitySelfReferenceRegression(): void
    {
        $theme = file_get_contents($this->projectSrcPath('core/Theme.php'));

        $this->assertStringContainsString(
            "evaluationMethodConfiguration.{opportunity.*,*}",
            $theme,
            'Select do EMC ainda inclui opportunity.* (contexto histórico do bug)'
        );

        $this->assertMatchesRegularExpression(
            '/unset\(\s*\$e\[[\'"]evaluationMethodConfiguration[\'"]\]\[[\'"]opportunity[\'"]\]\s*\)/',
            $theme,
            'Theme::addRequestedEntityToJs deve remover opportunity aninhada do mesmo id'
        );

        $this->assertMatchesRegularExpression(
            '/\$entity_class_name\s*===\s*Opportunity::class/',
            $theme,
            'Unset deve ser restrito a Opportunity'
        );
    }
}
