<?php

namespace Tests;

use DateTime;
use Exception;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\EntityRevision;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\Event;
use MapasCulturais\Entities\MetaList;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Project;
use MapasCulturais\Entities\Seal;
use MapasCulturais\Entities\Space;
use MapasCulturais\Entities\User;
use MapasCulturais\Entity;
use MapasCulturais\Repositories\EntityRevision as RepositoriesEntityRevision;
use Tests\Abstract\TestCase;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\AgentDirector;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\ProjectDirector;
use Tests\Traits\EventDirector;
use Tests\Traits\Faker;
use Tests\Traits\SealDirector;
use Tests\Traits\SpaceDirector;
use Tests\Traits\SystemRoleDirector;
use Tests\Traits\UserDirector;
use UserManagement\Entities\SystemRole;

class EntityRevisionsTest extends TestCase
{
    use UserDirector,
        AgentDirector,
        SpaceDirector,
        ProjectDirector,
        EventDirector,
        OpportunityBuilder,
        SealDirector,
        Faker,
        SystemRoleDirector;

    protected $entityClasses = [
        Agent::class,
        Space::class,
        Project::class,
        Event::class,
        Opportunity::class,
        EvaluationMethodConfiguration::class,
        User::class,
        Seal::class,
        SystemRole::class,
    ];

    protected function findLastRevision(string $entity_class, int $entity_id): ?EntityRevision
    {
        /** @var RepositoriesEntityRevision */
        $repo = $this->app->repo('EntityRevision');
        $last_revision = $repo->findOneBy(['objectType' => $entity_class, 'objectId' => $entity_id], ['id'=>'desc']);
        return $last_revision;
    }

    function createEntity(string $class, ?User $user = null): Entity
    {
        $this->app->disableAccessControl();
        if (!$user) {
            if ($this->app->auth->isUserAuthenticated()) {
                $user = $this->app->user;
            } else {
                $user = $this->userDirector->createUser();
            }
        }

        switch ($class) {
            case Agent::class:
                $entity = $this->agentDirector->createAgent($user);
                break;
            case Space::class:
                $entity = $this->spaceDirector->createSpace($user->profile);
                break;
            case Project::class:
                $entity = $this->projectDirector->createProject($user->profile);
                break;
            case Event::class:
                $entity = $this->eventDirector->createEvent($user->profile);
                break;
            case Opportunity::class:
                $entity = $this->opportunityBuilder
                    ->reset($user->profile, $user->profile)
                    ->fillRequiredProperties()
                    ->save()
                    ->getInstance();
                break;
            case EvaluationMethodConfiguration::class:
                $entity = $this->opportunityBuilder
                    ->reset($user->profile, $user->profile)
                    ->fillRequiredProperties()
                    ->save()
                    ->addEvaluationPhase(EvaluationMethods::simple)
                        ->save()
                        ->getInstance();
                break;
            case User::class:
                $entity = $this->userDirector->createUser();
                break;
            case Seal::class:
                $entity = $this->sealDirector->createSeal($user->profile);
                break;
            case SystemRole::class:
                $entity = $this->systemRoleDirector->createSpaceAdminRole();
                break;

            default:
                throw new Exception('classe não implementada');
        }

        $this->app->enableAccessControl();
        return $entity;
    }

    function changeEntity(Entity $entity): object
    {
        $result = (object)[
            'key' => null,
            'value' => null
        ];

        switch ($entity->className) {
            case User::class:
                $result->key = 'email';
                $result->value = $this->faker->email;
                break;
                
            default:
                $result->key = 'name';
                $result->value = $this->faker->name;
                break;
            }
            
        $entity->{$result->key} = $result->value;

        $this->app->disableAccessControl();
        $entity->save(true);
        $this->app->enableAccessControl();

        return $result;
    }

    function testCreatedRevision()
    {
        $user = $this->userDirector->createUser();

        $this->login($user);

        foreach ($this->entityClasses as $class) {
            $entity = $this->createEntity($class, $user);

            /** @var EntityRevision */
            $last_revision = $entity->getLastRevision();

            $this->assertEquals(EntityRevision::ACTION_CREATED, $last_revision->action ?? null, "Garantindo que a revisão da criação da entidade {$class} foi criada");

        }
    }

    function testModifiedRevision()
    {
        $user = $this->userDirector->createUser('saasSuperAdmin');

        $this->login($user);

        foreach ($this->entityClasses as $class) {
            $entity = $this->createEntity($class, $user);

            $modification = $this->changeEntity($entity);
            
            /** @var EntityRevision */
            $last_revision = $entity->getLastRevision();

            $this->assertEquals(EntityRevision::ACTION_MODIFIED, $last_revision->action ?? null, "Garantindo que a revisão da criação da entidade {$class} foi criada");

            $this->assertEquals($modification->value, $last_revision->revisionData[$modification->key]->value, "Garantindo que o valor modificado foi salvo na última revisão");

        }
    }

    function testSoftDeletedRevision()
    {
        $user = $this->userDirector->createUser('saasSuperAdmin');

        $this->login($user);

        foreach ($this->entityClasses as $class) {
            // Pula entidades que não usam soft delete ou não têm propriedade status
            if (!$class::usesSoftDelete() || !property_exists($class, 'status')) {
                continue;
            }

            $entity = $this->createEntity($class, $user);

            $entity->delete(true);

            /** @var EntityRevision */
            $last_revision = $entity->getLastRevision();

            $this->assertEquals(EntityRevision::ACTION_TRASHED, $last_revision->action ?? null, "Garantindo que a revisão de deleção (soft delete) da entidade {$class} foi criada");

            $this->assertEquals(Entity::STATUS_TRASH, $last_revision->revisionData['status']->value, "Garantindo que o status TRASH foi salvo na última revisão da entidade {$class}");
        }
    }

    function testDestroyRevision()
    {
        foreach ($this->entityClasses as $class) {
            $this->app->em->clear();
            $user = $this->userDirector->createUser('saasSuperAdmin');
            $this->login($user);
            
            // Pula entidades que não usam soft delete ou não têm propriedade status
            $entity = $this->createEntity($class, $user);
            $entity_id = $entity->id;
            
            $this->app->disableAccessControl();
            if ($class::usesSoftDelete()) {
                $entity->destroy(true);
            } else {
                $entity->delete(true);
            }
            $this->app->enableAccessControl();
            
            /** @var EntityRevision */
            $last_revision = $this->findLastRevision($class, $entity_id);

            $this->assertEquals(EntityRevision::ACTION_DELETED, $last_revision->action ?? null, "Garantindo que a revisão de deleção (soft delete) da entidade {$class} foi criada");
        }
    }

    function testTaxonomyTermsRevision()
    {
        $user = $this->userDirector->createUser('saasSuperAdmin');

        $this->login($user);

        foreach ($this->entityClasses as $class) {
            // Pula entidades que não usam taxonomias
            if (!$class::usesTaxonomies()) {
                continue;
            }

            $entity = $this->createEntity($class, $user);

            // Adicionar termos
            $terms_to_add = ['tag1', 'tag2', 'tag3'];
            $entity->terms['tag'] = $terms_to_add;
            $entity->save(true);

            /** @var EntityRevision */
            $last_revision = $entity->getLastRevision();
            $this->assertEquals(EntityRevision::ACTION_MODIFIED, $last_revision->action ?? null, "Garantindo que a revisão foi criada ao adicionar termos na entidade {$class}");

            // Verifica se os termos foram salvos na revisão
            $revision_terms_value = $last_revision->revisionData['_terms']->value ?? null;
            $this->assertEquals(true, $revision_terms_value !== null, "Garantindo que os termos foram salvos na revisão da entidade {$class}");
            
            // Converte para array se for objeto
            $revision_terms = is_object($revision_terms_value) ? (array)$revision_terms_value : $revision_terms_value;
            $revision_terms_tag = is_object($revision_terms['tag'] ?? null) ? (array)$revision_terms['tag'] : ($revision_terms['tag'] ?? null);
            
            $this->assertEquals(true, isset($revision_terms['tag']), "Garantindo que a taxonomia 'tag' está presente na revisão da entidade {$class}");
            $this->assertEquals($terms_to_add, $revision_terms_tag, "Garantindo que os termos adicionados estão corretos na revisão da entidade {$class}");

            // Modificar termos 
            $entity->terms['tag'][] = 'tag4';
            $entity->save(true);

            $last_revision = $entity->getLastRevision();
            $this->assertEquals(EntityRevision::ACTION_MODIFIED, $last_revision->action ?? null, "Garantindo que a revisão foi criada ao modificar termos na entidade {$class}");

            $revision_terms_value = $last_revision->revisionData['_terms']->value ?? null;
            $this->assertEquals(true, $revision_terms_value !== null, "Garantindo que os termos modificados foram salvos na revisão da entidade {$class}");
            
            // Converte para array se for objeto
            $revision_terms = is_object($revision_terms_value) ? (array)$revision_terms_value : $revision_terms_value;
            $revision_terms_tag = is_object($revision_terms['tag'] ?? null) ? (array)$revision_terms['tag'] : ($revision_terms['tag'] ?? null);
            
            $expected_terms_with_tag4 = ['tag1', 'tag2', 'tag3', 'tag4'];
            $this->assertEquals($expected_terms_with_tag4, $revision_terms_tag, "Garantindo que o novo termo foi adicionado na revisão da entidade {$class}");

            // Remover termos
            $entity->terms['tag'] = [];
            $entity->save(true);

            $last_revision = $entity->getLastRevision();
            $this->assertEquals(EntityRevision::ACTION_MODIFIED, $last_revision->action ?? null, "Garantindo que a revisão foi criada ao remover termos na entidade {$class}");

            $revision_terms_value = $last_revision->revisionData['_terms']->value ?? null;
            $this->assertEquals(true, $revision_terms_value !== null, "Garantindo que os termos removidos foram salvos na revisão da entidade {$class}");
            
            // Converte para array se for objeto
            $revision_terms = is_object($revision_terms_value) ? (array)$revision_terms_value : $revision_terms_value;
            $revision_terms_tag = is_object($revision_terms['tag'] ?? null) ? (array)$revision_terms['tag'] : ($revision_terms['tag'] ?? null);
            
            $this->assertEquals([], $revision_terms_tag, "Garantindo que os termos foram removidos na revisão da entidade {$class}");
        }
    }

    function testMetaListsRevision()
    {
        $user = $this->userDirector->createUser('saasSuperAdmin');

        $this->login($user);

        foreach ($this->entityClasses as $class) {
            // Pula entidades que não usam metalists
            if (!$class::usesMetaLists()) {
                continue;
            }

            $entity = $this->createEntity($class, $user);

            // Adicionar metalists (links)
            $link1 = new MetaList();
            $link1->owner = $entity;
            $link1->group = 'links';
            $link1->title = 'Link 1';
            $link1->value = 'https://example.com/link1';
            $link1->save(true);

            $link2 = new MetaList();
            $link2->owner = $entity;
            $link2->group = 'links';
            $link2->title = 'Link 2';
            $link2->value = 'https://example.com/link2';
            $link2->save(true);

            // Salva a entidade para gerar revisão
            $entity->save(true);

            /** @var EntityRevision */
            $last_revision = $entity->getLastRevision();
            $this->assertEquals(EntityRevision::ACTION_MODIFIED, $last_revision->action ?? null, "Garantindo que a revisão foi criada ao adicionar metalists na entidade {$class}");

            // Verifica se os metalists foram salvos na revisão
            $revision_links_value = $last_revision->revisionData['links']->value ?? null;
            $this->assertEquals(true, $revision_links_value !== null, "Garantindo que os metalists foram salvos na revisão da entidade {$class}");
            
            // Converte para array se for objeto
            $revision_links = is_object($revision_links_value) ? (array)$revision_links_value : $revision_links_value;
            
            $this->assertEquals(true, isset($revision_links), "Garantindo que o grupo 'links' está presente na revisão da entidade {$class}");
            $this->assertEquals(2, count($revision_links), "Garantindo que 2 links foram adicionados na revisão da entidade {$class}");
            
            // Verifica se os dados do primeiro link estão corretos
            $first_link = is_object($revision_links[0] ?? null) ? (array)$revision_links[0] : ($revision_links[0] ?? null);
            $this->assertEquals('Link 1', $first_link['title'], "Garantindo que o título do primeiro link está correto na revisão da entidade {$class}");
            $this->assertEquals('https://example.com/link1', $first_link['value'], "Garantindo que o valor do primeiro link está correto na revisão da entidade {$class}");

            // Modificar metalist
            $link1->title = 'Link 1 Modificado';
            $link1->save(true);
            $entity->save(true);

            $last_revision = $entity->getLastRevision();
            $this->assertEquals(EntityRevision::ACTION_MODIFIED, $last_revision->action ?? null, "Garantindo que a revisão foi criada ao modificar metalist na entidade {$class}");

            $revision_links_value = $last_revision->revisionData['links']->value ?? null;
            $this->assertEquals(true, $revision_links_value !== null, "Garantindo que os metalists modificados foram salvos na revisão da entidade {$class}");
            
            // Converte para array se for objeto
            $revision_links = is_object($revision_links_value) ? (array)$revision_links_value : $revision_links_value;
            
            $first_link = is_object($revision_links[0] ?? null) ? (array)$revision_links[0] : ($revision_links[0] ?? null);
            $this->assertEquals('Link 1 Modificado', $first_link['title'], "Garantindo que o título modificado foi salvo na revisão da entidade {$class}");

            // Remover metalist
            $link1->delete(true);
            $entity->save(true);

            $last_revision = $entity->getLastRevision();
            $this->assertEquals(EntityRevision::ACTION_MODIFIED, $last_revision->action ?? null, "Garantindo que a revisão foi criada ao remover metalist na entidade {$class}");

            $revision_links_value = $last_revision->revisionData['links']->value ?? null;
            $this->assertEquals(true, $revision_links_value !== null, "Garantindo que os metalists removidos foram salvos na revisão da entidade {$class}");
            
            // Converte para array se for objeto
            $revision_links = is_object($revision_links_value) ? (array)$revision_links_value : $revision_links_value;
            
            $this->assertEquals(1, count($revision_links), "Garantindo que apenas 1 link restou na revisão da entidade {$class}");
            
            // Verifica se o link restante é o segundo
            $remaining_link = is_object($revision_links[0] ?? null) ? (array)$revision_links[0] : ($revision_links[0] ?? null);
            $this->assertEquals('Link 2', $remaining_link['title'], "Garantindo que o link correto permaneceu na revisão da entidade {$class}");
        }
    }

    function testRelatedAgentsRevision()
    {
        $user = $this->userDirector->createUser('saasSuperAdmin');

        $this->login($user);

        foreach ($this->entityClasses as $class) {
            // Pula entidades que não usam agentes relacionados
            if (!$class::usesAgentRelation()) {
                continue;
            }

            $entity = $this->createEntity($class, $user);

            // Cria agentes para relacionar
            $agent1 = $this->agentDirector->createAgent($user);
            $agent2 = $this->agentDirector->createAgent($user);
            $agent3 = $this->agentDirector->createAgent($user);

            // Adicionar agentes relacionados
            $group = 'colaboradores';
            $entity->createAgentRelation($agent1, $group, false, true, true);
            $entity->createAgentRelation($agent2, $group, false, true, true);
            $entity->save(true);

            /** @var EntityRevision */
            $last_revision = $entity->getLastRevision();
            $this->assertEquals(EntityRevision::ACTION_MODIFIED, $last_revision->action ?? null, "Garantindo que a revisão foi criada ao adicionar agentes relacionados na entidade {$class}");

            // Verifica se os agentes relacionados foram salvos na revisão
            $revision_agents_value = $last_revision->revisionData['_agents']->value ?? null;
            $this->assertEquals(true, $revision_agents_value !== null, "Garantindo que os agentes relacionados foram salvos na revisão da entidade {$class}");
            
            // Converte para array se for objeto
            $revision_agents = is_object($revision_agents_value) ? (array)$revision_agents_value : $revision_agents_value;
            
            // Acessa o grupo dentro de _agents
            $revision_agents_group_value = $revision_agents[$group] ?? null;
            $revision_agents_group = is_object($revision_agents_group_value) ? (array)$revision_agents_group_value : ($revision_agents_group_value ?? []);
            
            $this->assertEquals(true, isset($revision_agents[$group]), "Garantindo que o grupo '{$group}' está presente na revisão da entidade {$class}");
            $this->assertEquals(2, count($revision_agents_group), "Garantindo que 2 agentes foram adicionados na revisão da entidade {$class}");

            // Adicionar mais um agente relacionado (modificação)
            $entity->createAgentRelation($agent3, $group, false, true, true);
            $entity->save(true);

            $last_revision = $entity->getLastRevision();
            $this->assertEquals(EntityRevision::ACTION_MODIFIED, $last_revision->action ?? null, "Garantindo que a revisão foi criada ao adicionar mais um agente relacionado na entidade {$class}");

            $revision_agents_value = $last_revision->revisionData['_agents']->value ?? null;
            $this->assertEquals(true, $revision_agents_value !== null, "Garantindo que os agentes modificados foram salvos na revisão da entidade {$class}");
            
            // Converte para array se for objeto
            $revision_agents = is_object($revision_agents_value) ? (array)$revision_agents_value : $revision_agents_value;
            
            // Acessa o grupo dentro de _agents
            $revision_agents_group_value = $revision_agents[$group] ?? null;
            $revision_agents_group = is_object($revision_agents_group_value) ? (array)$revision_agents_group_value : ($revision_agents_group_value ?? []);
            
            $this->assertEquals(3, count($revision_agents_group), "Garantindo que 3 agentes estão presentes na revisão da entidade {$class}");

            // Remover um agente relacionado
            $entity->removeAgentRelation($agent1, $group, true);
            $entity->save(true);

            $last_revision = $entity->getLastRevision();
            $this->assertEquals(EntityRevision::ACTION_MODIFIED, $last_revision->action ?? null, "Garantindo que a revisão foi criada ao remover agente relacionado na entidade {$class}");

            $revision_agents_value = $last_revision->revisionData['_agents']->value ?? null;
            $this->assertEquals(true, $revision_agents_value !== null, "Garantindo que os agentes removidos foram salvos na revisão da entidade {$class}");
            
            // Converte para array se for objeto
            $revision_agents = is_object($revision_agents_value) ? (array)$revision_agents_value : $revision_agents_value;
            
            // Acessa o grupo dentro de _agents
            $revision_agents_group_value = $revision_agents[$group] ?? null;
            $revision_agents_group = is_object($revision_agents_group_value) ? (array)$revision_agents_group_value : ($revision_agents_group_value ?? []);
            
            $this->assertEquals(2, count($revision_agents_group), "Garantindo que apenas 2 agentes restaram na revisão da entidade {$class}");
        }
    }

    function testUserRolesRevision()
    {
        $admin = $this->userDirector->createUser('saasSuperAdmin');
        $this->login($admin);

        $user = $this->userDirector->createUser();

        $user->addRole('admin');

        $last_revision = $user->lastRevision;

        $this->assertEquals(EntityRevision::ACTION_MODIFIED, $last_revision->action, 'Garantindo que após adicionar um role, a última revisão do user é do tipo modified');

        $this->assertEquals(['admin'],$last_revision->revisionData['roles']->value, 'Garantindo que após adicionar um role, a última revisão do user contém a lista de roles do usuário com a role adicionado');

        $user->removeRole('admin');

        $last_revision = $user->lastRevision;

        $this->assertEquals([],$last_revision->revisionData['roles']->value, 'Garantindo que após remover um role, a última revisão do user contém a lista de roles do usuário sem o role removido');

    }
}
