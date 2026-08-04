<?php

namespace Tests;

use MapasCulturais\ApiQuery;
use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\AgentRelation;
use Tests\Abstract\TestCase;
use Tests\Traits\UserDirector;

/**
 * Regressão: grupo de agentes relacionados com pendente + ativo,
 * visto por usuário sem permissão de ver pendentes.
 *
 * Sem array_values após array_filter, a API serializa o grupo como objeto
 * JSON ({"1":{...}}) em vez de lista ([{...}]), e o frontend Vue itera
 * propriedades string (ex.: newGroupName) como se fossem relations.
 */
class AgentRelatedAgentsApiRegressionTest extends TestCase
{
    use UserDirector;

    private const GROUP = 'Parceiras';

    public function testPublicRelatedAgentsGroupRemainsJsonListWhenPendingFiltered(): void
    {
        $app = App::i();

        $owner_user = $this->userDirector->createUser();
        $pending_user = $this->userDirector->createUser();
        $active_user = $this->userDirector->createUser();

        $owner = $owner_user->profile;
        $pending_agent = $pending_user->profile;
        $active_agent = $active_user->profile;

        $app->disableAccessControl();
        try {
            // Pendente primeiro: após o filter, sobra chave esparsa (ex.: 1) se não houver array_values.
            $pending_relation = $owner->createAgentRelation($pending_agent, self::GROUP, false, true, true);
            $pending_relation->status = AgentRelation::STATUS_PENDING;
            $pending_relation->save(true);

            $owner->createAgentRelation($active_agent, self::GROUP, false, true, true);
        } finally {
            $app->enableAccessControl();
        }

        $this->logout();

        $query = new ApiQuery(Agent::class, [
            '@select' => 'id,relatedAgents,agentRelations,currentUserPermissions',
            'id' => "EQ({$owner->id})",
        ]);
        $result = $query->find();
        $this->assertNotEmpty($result, 'ApiQuery deve encontrar o agente dono');
        $entity = $result[0];

        $this->assertArrayHasKey(
            self::GROUP,
            $entity['relatedAgents'] ?? [],
            'Grupo com relação ativa deve permanecer após filtrar pendentes'
        );
        $this->assertArrayHasKey(
            self::GROUP,
            $entity['agentRelations'] ?? [],
            'Grupo com relação ativa deve permanecer em agentRelations após filtrar pendentes'
        );

        $related = $entity['relatedAgents'][self::GROUP];
        $relations = $entity['agentRelations'][self::GROUP];

        $this->assertIsList(
            $related,
            'relatedAgents do grupo deve ser lista PHP (array_is_list), não mapa esparso'
        );
        $this->assertIsList(
            $relations,
            'agentRelations do grupo deve ser lista PHP (array_is_list), não mapa esparso'
        );

        $this->assertSame(
            'array',
            $this->jsonTypeOf($related),
            'relatedAgents do grupo deve serializar como array JSON, não como objeto'
        );
        $this->assertSame(
            'array',
            $this->jsonTypeOf($relations),
            'agentRelations do grupo deve serializar como array JSON, não como objeto'
        );

        $this->assertCount(1, $related, 'Visitante público deve ver só a relação ativa no relatedAgents');
        $this->assertCount(1, $relations, 'Visitante público deve ver só a relação ativa no agentRelations');

        $this->assertSame($active_agent->id, (int) $related[0]['id']);
        $this->assertSame($active_agent->id, (int) ($relations[0]['agent']['id'] ?? 0));
        $this->assertGreaterThan(0, (int) ($relations[0]['status'] ?? 0));

        $related_ids = array_map(fn($item) => (int) $item['id'], $related);
        $this->assertNotContains(
            $pending_agent->id,
            $related_ids,
            'Relação pendente não deve aparecer para visitante sem permissão'
        );
    }

    /**
     * Tipo JSON que json_encode produziria para o valor (array vs object).
     */
    private function jsonTypeOf(mixed $value): string
    {
        $decoded = json_decode(json_encode($value));
        return gettype($decoded);
    }
}
