<?php

namespace Test;

use MapasCulturais\App;
use MapasCulturais\Entities\Opportunity;
use Psr\Http\Message\ServerRequestInterface;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\Open;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RequestFactory;
use Tests\Traits\SealDirector;
use Tests\Traits\UserDirector;

/**
 * Testes para o endpoint GET /opportunity/findOpportunitiesModels
 * Cobre os ajustes feitos na branch fix/opportunity-models-n-plus-one:
 *  - leitura de registrationProponentTypes da coluna (não de opportunity_meta)
 *  - exclusão de modelos com status -10 (lixeira)
 *  - modelIsOfficial via seal_relation
 *  - numeroFases excluindo a última fase (isLastPhase)
 */
class OpportunityFindModelsTest extends TestCase
{
    use OpportunityBuilder,
        RequestFactory,
        UserDirector,
        SealDirector;

    // =================== HELPERS ===================

    private function makeRequest(): ServerRequestInterface
    {
        return $this->requestFactory->GET(
            controller_id: 'opportunity',
            action: 'findOpportunitiesModels',
            ajax: true
        );
    }

    private function callEndpoint(): array
    {
        $app = App::i();
        $app->reset();
        $app->run($this->makeRequest(), false);
        return json_decode((string) $app->response->getBody(), true) ?? [];
    }

    private function findInResult(array $result, int $id): ?array
    {
        foreach ($result as $item) {
            if ((int) $item['id'] === $id) {
                return $item;
            }
        }
        return null;
    }

    private function createModel(array $proponentTypes = []): Opportunity
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $builder = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile, status: Opportunity::STATUS_PHASE)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done();

        $model = $builder->save()->getInstance();

        if (!empty($proponentTypes)) {
            $model->registrationProponentTypes = $proponentTypes;
        }

        $model->setMetadata('isModel', 1);
        $model->save(true);

        return $model;
    }

    // =================== TESTES ===================

    /**
     * Endpoint deve responder 200.
     */
    function testEndpointRetornaStatus200(): void
    {
        $this->assertStatus200($this->makeRequest());
    }

    /**
     * Cada item do retorno deve ter os campos definidos pelo endpoint.
     */
    function testRetornaEstruturaCorreta(): void
    {
        $model = $this->createModel();

        $result = $this->callEndpoint();
        $item = $this->findInResult($result, $model->id);

        $this->assertNotNull($item, 'O modelo criado deve aparecer na listagem');
        $this->assertArrayHasKey('id', $item);
        $this->assertArrayHasKey('numeroFases', $item);
        $this->assertArrayHasKey('descricao', $item);
        $this->assertArrayHasKey('tempoEstimado', $item);
        $this->assertArrayHasKey('tipoAgente', $item);
        $this->assertArrayHasKey('modelIsOfficial', $item);
        $this->assertIsInt($item['id']);
        $this->assertIsInt($item['numeroFases']);
        $this->assertIsBool($item['modelIsOfficial']);
    }

    /**
     * tipoAgente deve ser lido de opportunity.registration_proponent_types (coluna),
     * não de opportunity_meta — esse era o bug corrigido no commit de revisão.
     */
    function testTipoAgenteVemDaColunaRegistrationProponentTypes(): void
    {
        $types = ['Pessoa Física', 'Pessoa Jurídica'];
        $model = $this->createModel($types);

        $result = $this->callEndpoint();
        $item = $this->findInResult($result, $model->id);
        
        $this->assertNotNull($item, 'O modelo deve aparecer na listagem');
        $this->assertNotEquals('N/A', $item['tipoAgente'], 'tipoAgente não deve ser N/A quando há tipos definidos');
        $this->assertStringContainsString('Pessoa Física', $item['tipoAgente']);
        $this->assertStringContainsString('Pessoa Jurídica', $item['tipoAgente']);
    }

    /**
     * Modelo sem tipos de proponente deve retornar N/A em tipoAgente.
     */
    function testTipoAgenteEhNAQuandoNaoDefinido(): void
    {
        $model = $this->createModel();

        $result = $this->callEndpoint();
        $item = $this->findInResult($result, $model->id);

        $this->assertNotNull($item);
        $this->assertEquals('N/A', $item['tipoAgente']);
    }

    /**
     * Modelo na lixeira (status -10) não deve aparecer no retorno.
     */
    function testModeloDescartadoNaoAparece(): void
    {
        $model = $this->createModel();
        $modelId = $model->id;

        $model->delete(true);

        $result = $this->callEndpoint();
        $item = $this->findInResult($result, $modelId);

        $this->assertNull($item, 'Modelo na lixeira não deve aparecer na listagem');
    }

    /**
     * Modelo sem nenhum selo verificado deve ter modelIsOfficial = false.
     */
    function testModeloSemSeloNaoEOficial(): void
    {
        $model = $this->createModel();

        $result = $this->callEndpoint();
        $item = $this->findInResult($result, $model->id);

        $this->assertNotNull($item);
        $this->assertFalse($item['modelIsOfficial'], 'Modelo sem selo verificado não deve ser oficial');
    }

    /**
     * Modelo com um selo cujo ID está em app.verifiedSealsIds deve ter modelIsOfficial = true.
     */
    function testModeloComSeloVerificadoEOficial(): void
    {
        $app = App::i();
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $seal = $this->sealDirector->createSeal($admin->profile, disable_access_control: true);

        $originalSealIds = $app->config['app.verifiedSealsIds'];
        $app->config['app.verifiedSealsIds'] = [$seal->id];

        $model = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile, status: Opportunity::STATUS_PHASE)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->getInstance();

        $model->setMetadata('isModel', 1);
        $model->save(true);
        $model->createSealRelation($seal, save: true, flush: true);

        $result = $this->callEndpoint();
        $item = $this->findInResult($result, $model->id);

        $app->config['app.verifiedSealsIds'] = $originalSealIds;

        $this->assertNotNull($item);
        $this->assertTrue($item['modelIsOfficial'], 'Modelo com selo verificado deve ter modelIsOfficial = true');
    }

    /**
     * numeroFases deve contar apenas as fases intermediárias —
     * a última fase (isLastPhase) não deve entrar na contagem.
     */
    function testNumeroDeFasesExcluiUltimaFase(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $model = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile, status: Opportunity::STATUS_PHASE)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->addDataCollectionPhase()
                ->done()
            ->addDataCollectionPhase()
                ->done()
            ->getInstance();

        $model->setMetadata('isModel', 1);
        $model->save(true);

        $result = $this->callEndpoint();
        $item = $this->findInResult($result, $model->id);

        $this->assertNotNull($item);

        $app = App::i();
        $conn = $app->em->getConnection();
        $totalFilhos = (int) $conn->fetchOne(
            'SELECT COUNT(*) FROM opportunity WHERE parent_id = ? AND status != -10',
            [$model->id]
        );
        $ultimasFases = (int) $conn->fetchOne(
            "SELECT COUNT(*) FROM opportunity o
             JOIN opportunity_meta om ON om.object_id = o.id AND om.key = 'isLastPhase' AND om.value = '1'
             WHERE o.parent_id = ? AND o.status != -10",
            [$model->id]
        );

        $esperado = $totalFilhos - $ultimasFases;
        $this->assertEquals($esperado, $item['numeroFases'], 'numeroFases deve excluir apenas as fases marcadas como isLastPhase');
    }
}
