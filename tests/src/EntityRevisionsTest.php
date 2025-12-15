<?php

namespace Tests;

use DateTime;
use Exception;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\EntityRevision;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\Event;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Project;
use MapasCulturais\Entities\Seal;
use MapasCulturais\Entities\Space;
use MapasCulturais\Entities\User;
use MapasCulturais\Entity;
use Tests\Abstract\TestCase;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\AgentDirector;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\ProjectDirector;
use Tests\Traits\EventDirector;
use Tests\Traits\Faker;
use Tests\Traits\SpaceDirector;
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
        Faker;

    protected $entityClasses = [
        Agent::class,
        Space::class,
        Project::class,
        Event::class,
        Opportunity::class,
        // EvaluationMethodConfiguration::class,
        // User::class,
        // Seal::class,
        // SystemRole::class,
    ];

    function createEntity(string $class, ?User $user = null): Entity
    {

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
            // case Seal::class:
            //     break;
            // case SystemRole::class:
            //     break;

            default:
                throw new Exception('classe não implementada');
        }


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

        $entity->save(true);

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
        $user = $this->userDirector->createUser();

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
        $user = $this->userDirector->createUser();

        $this->login($user);

        foreach ($this->entityClasses as $class) {
            $entity = $this->createEntity($class, $user);

            $entity->delete(true);

            /** @var EntityRevision */
            $last_revision = $entity->getLastRevision();

            $this->assertEquals(EntityRevision::ACTION_TRASHED, $last_revision->action ?? null, "Garantindo que a revisão de deleção (soft delete) da entidade {$class} foi criada");

            $this->assertEquals(Entity::STATUS_TRASH, $last_revision->revisionData['status']->value, "Garantindo que o status TRASH foi salvo na última revisão da entidade {$class}");
        }
    }

    function testTaxonomyTermsRevision()
    {
        $user = $this->userDirector->createUser();

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
}
