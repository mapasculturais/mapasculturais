<?php

namespace Tests;

use MapasCulturais\ApiQuery;
use MapasCulturais\Entity;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Project;
use MapasCulturais\Types\GeoPoint;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\Open;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\ProjectDirector;
use Tests\Traits\UserDirector;

/**
 * Testa o RF-B6 (épica #99): endereço opcional em Projeto e Oportunidade.
 *
 * Cobre:
 * - CA-1: persistência de endereço brasileiro (campos En_*) e internacional (campos address_*)
 * - Normalização: hook do CountryLocalizations popula address_level0/2/4/6,
 *   address_postalCode, address_line1/2 e address de forma coerente com os En_*
 * - CA-3: salvar e publicar (status 1) com endereço totalmente vazio
 * - CA-4: ApiQuery com @select incluindo En_* retorna os valores e o filtro
 *   En_Estado=EQ(SP) encontra a entidade sem lançar PropertyDoesNotExists
 * - Mapa: filtro location !EQ([0,0]) exclui entidades sem endereço e inclui as georreferenciadas
 */
class ProjectOpportunityAddressTest extends TestCase
{
    use UserDirector,
        ProjectDirector,
        OpportunityBuilder;

    // ─── helpers ──────────────────────────────────────────────────────────────

    /** Preenche os campos de endereço brasileiro (En_*) + país que dispara a normalização */
    private function fillBrazilianAddress(Project|Opportunity $entity, string $estado = 'SP', string $municipio = 'São Paulo'): void
    {
        $entity->address_level0 = 'BR';
        $entity->En_CEP = '01310-100';
        $entity->En_Nome_Logradouro = 'Avenida Paulista';
        $entity->En_Num = '1000';
        $entity->En_Complemento = 'Sala 101';
        $entity->En_Bairro = 'Bela Vista';
        $entity->En_Municipio = $municipio;
        $entity->En_Estado = $estado;
    }

    /** Preenche os campos de endereço internacional (address_*) */
    private function fillInternationalAddress(Project|Opportunity $entity): void
    {
        $entity->address_level0 = 'AR';
        $entity->address_postalCode = 'C1043AAZ';
        $entity->address_line1 = 'Av. Corrientes 1234';
        $entity->address_line2 = 'Piso 3';
        $entity->address_level2 = 'Buenos Aires';
        $entity->address_level4 = 'Ciudad Autónoma de Buenos Aires';
        $entity->address_level6 = 'San Nicolás';
        $entity->address = 'Av. Corrientes 1234, San Nicolás, Ciudad Autónoma de Buenos Aires, Argentina';
    }

    private function createProjectWith(callable $fill = null): Project
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $project = $this->projectDirector->createProject(owner: $admin->profile);

        if ($fill) {
            $fill($project);
            $project->save(true);
        }

        return $project;
    }

    private function createOpportunityFor(Project $project, callable $fill = null): Opportunity
    {
        $opportunity = $this->opportunityBuilder
            ->reset(owner: $project->owner, owner_entity: $project)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->getInstance();

        if ($fill) {
            $fill($opportunity);
            $opportunity->save(true);
        }

        return $opportunity;
    }

    private function assertBrazilianAddressPersisted(Project|Opportunity $entity, string $estado = 'SP', string $municipio = 'São Paulo'): void
    {
        $this->assertEquals('01310-100', $entity->En_CEP, 'CEP persistido');
        $this->assertEquals('Avenida Paulista', $entity->En_Nome_Logradouro, 'Logradouro persistido');
        $this->assertEquals('1000', $entity->En_Num, 'Número persistido');
        $this->assertEquals('Sala 101', $entity->En_Complemento, 'Complemento persistido');
        $this->assertEquals('Bela Vista', $entity->En_Bairro, 'Bairro persistido');
        $this->assertEquals($municipio, $entity->En_Municipio, 'Município persistido');
        $this->assertEquals($estado, $entity->En_Estado, 'Estado persistido');
        $this->assertEquals('BR', $entity->En_Pais, 'País persistido');
    }

    private function assertBrazilianAddressNormalized(Project|Opportunity $entity): void
    {
        $this->assertEquals('BR', $entity->address_level0, 'address_level0 normalizado');
        $this->assertEquals('SP', $entity->address_level2, 'address_level2 (estado) normalizado a partir de En_Estado');
        $this->assertEquals('São Paulo', $entity->address_level4, 'address_level4 (município) normalizado a partir de En_Municipio');
        $this->assertEquals('Bela Vista', $entity->address_level6, 'address_level6 (bairro) normalizado a partir de En_Bairro');
        $this->assertEquals('01310-100', $entity->address_postalCode, 'address_postalCode normalizado a partir de En_CEP');
        $this->assertEquals('Avenida Paulista, 1000', $entity->address_line1, 'address_line1 normalizado a partir de logradouro + número');
        $this->assertEquals('Sala 101', $entity->address_line2, 'address_line2 normalizado a partir de En_Complemento');
        $this->assertEquals(
            'Avenida Paulista, 1000 - Sala 101 - Bela Vista - São Paulo - SP - CEP: 01310-100',
            $entity->address,
            'address (endereço completo) coerente com os campos En_*'
        );
    }

    private function assertInternationalAddressPersisted(Project|Opportunity $entity): void
    {
        $this->assertEquals('AR', $entity->address_level0, 'address_level0 persistido');
        $this->assertEquals('C1043AAZ', $entity->address_postalCode, 'address_postalCode persistido');
        $this->assertEquals('Av. Corrientes 1234', $entity->address_line1, 'address_line1 persistido');
        $this->assertEquals('Piso 3', $entity->address_line2, 'address_line2 persistido');
        $this->assertEquals('Buenos Aires', $entity->address_level2, 'address_level2 persistido');
        $this->assertEquals('Ciudad Autónoma de Buenos Aires', $entity->address_level4, 'address_level4 persistido');
        $this->assertEquals('San Nicolás', $entity->address_level6, 'address_level6 persistido');
        $this->assertEquals(
            'Av. Corrientes 1234, San Nicolás, Ciudad Autónoma de Buenos Aires, Argentina',
            $entity->address,
            'address persistido'
        );

        // endereço internacional não escreve nos campos brasileiros
        $this->assertEmpty($entity->En_CEP, 'Endereço internacional não deve preencher En_CEP');
        $this->assertEmpty($entity->En_Estado, 'Endereço internacional não deve preencher En_Estado');
    }

    private function assertEmptyAddress(Project|Opportunity $entity): void
    {
        $this->assertEmpty($entity->endereco, 'endereco vazio');
        $this->assertEmpty($entity->En_CEP, 'En_CEP vazio');
        $this->assertEmpty($entity->En_Nome_Logradouro, 'En_Nome_Logradouro vazio');
        $this->assertEmpty($entity->En_Estado, 'En_Estado vazio');
        $this->assertEmpty($entity->address_level0, 'address_level0 vazio');
        $this->assertEmpty($entity->address_postalCode, 'address_postalCode vazio');
        $this->assertEmpty($entity->address_line1, 'address_line1 vazio');
    }

    // ─── CA-1: endereço brasileiro ────────────────────────────────────────────

    function testProjectSavesBrazilianAddress()
    {
        $project = $this->createProjectWith(function (Project $project) {
            $this->fillBrazilianAddress($project);
        });

        $this->assertBrazilianAddressPersisted($project->refreshed());
    }

    function testOpportunitySavesBrazilianAddress()
    {
        $project = $this->createProjectWith();
        $opportunity = $this->createOpportunityFor($project, function (Opportunity $opportunity) {
            $this->fillBrazilianAddress($opportunity);
        });

        $this->assertBrazilianAddressPersisted($opportunity->refreshed());
    }

    // ─── CA-1: endereço internacional ─────────────────────────────────────────

    function testProjectSavesInternationalAddress()
    {
        $project = $this->createProjectWith(function (Project $project) {
            $this->fillInternationalAddress($project);
        });

        $this->assertInternationalAddressPersisted($project->refreshed());
    }

    function testOpportunitySavesInternationalAddress()
    {
        $project = $this->createProjectWith();
        $opportunity = $this->createOpportunityFor($project, function (Opportunity $opportunity) {
            $this->fillInternationalAddress($opportunity);
        });

        $this->assertInternationalAddressPersisted($opportunity->refreshed());
    }

    // ─── normalização CountryLocalizations ────────────────────────────────────

    function testProjectBrazilianAddressNormalization()
    {
        $project = $this->createProjectWith(function (Project $project) {
            $this->fillBrazilianAddress($project);
        });

        $this->assertBrazilianAddressNormalized($project->refreshed());
    }

    function testOpportunityBrazilianAddressNormalization()
    {
        $project = $this->createProjectWith();
        $opportunity = $this->createOpportunityFor($project, function (Opportunity $opportunity) {
            $this->fillBrazilianAddress($opportunity);
        });

        $this->assertBrazilianAddressNormalized($opportunity->refreshed());
    }

    // ─── CA-3: publicação com endereço vazio ──────────────────────────────────

    function testProjectPublishesWithEmptyAddress()
    {
        $project = $this->createProjectWith();

        $project->status = Entity::STATUS_DRAFT;
        $project->save(true);
        $this->assertEquals(Entity::STATUS_DRAFT, $project->status, 'Projeto salvo como rascunho sem endereço');

        $project->status = Entity::STATUS_ENABLED;
        $project->save(true);

        $project = $project->refreshed();
        $this->assertEquals(Entity::STATUS_ENABLED, $project->status, 'Projeto publicado (status 1) sem endereço');
        $this->assertEmptyAddress($project);
    }

    function testOpportunityPublishesWithEmptyAddress()
    {
        $project = $this->createProjectWith();
        $opportunity = $this->createOpportunityFor($project);

        $opportunity->status = Entity::STATUS_DRAFT;
        $opportunity->save(true);
        $this->assertEquals(Entity::STATUS_DRAFT, $opportunity->status, 'Oportunidade salva como rascunho sem endereço');

        $opportunity->status = Entity::STATUS_ENABLED;
        $opportunity->save(true);

        $opportunity = $opportunity->refreshed();
        $this->assertEquals(Entity::STATUS_ENABLED, $opportunity->status, 'Oportunidade publicada (status 1) sem endereço');
        $this->assertEmptyAddress($opportunity);
    }

    // ─── CA-4: API com @select de En_* e filtro por En_Estado ─────────────────

    function testProjectApiSelectAndFilterByEnEstado()
    {
        $project_sp = $this->createProjectWith(function (Project $project) {
            $this->fillBrazilianAddress($project, 'SP', 'São Paulo');
            $project->publicLocation = true;
        });

        $project_rj = $this->createProjectWith(function (Project $project) {
            $this->fillBrazilianAddress($project, 'RJ', 'Rio de Janeiro');
            $project->publicLocation = true;
        });

        $this->logout();

        // se o metadado não estivesse registrado, a ApiQuery lançaria PropertyDoesNotExists
        $query = new ApiQuery(Project::class, [
            '@select' => 'id,En_CEP,En_Nome_Logradouro,En_Num,En_Complemento,En_Bairro,En_Municipio,En_Estado,En_Pais',
            'En_Estado' => 'EQ(SP)',
        ]);

        $result = $query->find();
        $result_ids = array_column($result, 'id');

        $this->assertContains($project_sp->id, $result_ids, 'ApiQuery com En_Estado=EQ(SP) deve retornar o projeto de SP');
        $this->assertNotContains($project_rj->id, $result_ids, 'ApiQuery com En_Estado=EQ(SP) não deve retornar o projeto de RJ');

        $row = $result[array_search($project_sp->id, $result_ids)];
        $this->assertEquals('SP', $row['En_Estado'], '@select deve retornar o valor de En_Estado');
        $this->assertEquals('01310-100', $row['En_CEP'], '@select deve retornar o valor de En_CEP');
        $this->assertEquals('Avenida Paulista', $row['En_Nome_Logradouro'], '@select deve retornar o valor de En_Nome_Logradouro');
        $this->assertEquals('São Paulo', $row['En_Municipio'], '@select deve retornar o valor de En_Municipio');
    }

    function testOpportunityApiSelectAndFilterByEnEstado()
    {
        $project = $this->createProjectWith();

        $opportunity_sp = $this->createOpportunityFor($project, function (Opportunity $opportunity) {
            $this->fillBrazilianAddress($opportunity, 'SP', 'São Paulo');
            $opportunity->publicLocation = true;
        });

        $opportunity_rj = $this->createOpportunityFor($project, function (Opportunity $opportunity) {
            $this->fillBrazilianAddress($opportunity, 'RJ', 'Rio de Janeiro');
            $opportunity->publicLocation = true;
        });

        $this->logout();

        // se o metadado não estivesse registrado, a ApiQuery lançaria PropertyDoesNotExists
        $query = new ApiQuery(Opportunity::class, [
            '@select' => 'id,En_CEP,En_Nome_Logradouro,En_Num,En_Complemento,En_Bairro,En_Municipio,En_Estado,En_Pais',
            'En_Estado' => 'EQ(SP)',
        ]);

        $result = $query->find();
        $result_ids = array_column($result, 'id');

        $this->assertContains($opportunity_sp->id, $result_ids, 'ApiQuery com En_Estado=EQ(SP) deve retornar a oportunidade de SP');
        $this->assertNotContains($opportunity_rj->id, $result_ids, 'ApiQuery com En_Estado=EQ(SP) não deve retornar a oportunidade de RJ');

        $row = $result[array_search($opportunity_sp->id, $result_ids)];
        $this->assertEquals('SP', $row['En_Estado'], '@select deve retornar o valor de En_Estado');
        $this->assertEquals('01310-100', $row['En_CEP'], '@select deve retornar o valor de En_CEP');
        $this->assertEquals('Avenida Paulista', $row['En_Nome_Logradouro'], '@select deve retornar o valor de En_Nome_Logradouro');
        $this->assertEquals('São Paulo', $row['En_Municipio'], '@select deve retornar o valor de En_Municipio');
    }

    // ─── mapa: filtro location !EQ([0,0]) ─────────────────────────────────────

    function testProjectApiLocationFilterExcludesEntitiesWithoutAddress()
    {
        $located = $this->createProjectWith(function (Project $project) {
            $project->publicLocation = true;
            $project->location = new GeoPoint(-46.633309, -23.55052);
        });

        $without_address = $this->createProjectWith();

        $this->logout();

        $query = new ApiQuery(Project::class, [
            '@select' => 'id,location',
            'location' => '!EQ([0,0])',
        ]);

        $result = $query->find();
        $result_ids = array_column($result, 'id');

        $this->assertContains($located->id, $result_ids, 'O filtro location !EQ([0,0]) deve incluir o projeto georreferenciado');
        $this->assertNotContains($without_address->id, $result_ids, 'O filtro location !EQ([0,0]) deve excluir o projeto sem endereço');

        $row = $result[array_search($located->id, $result_ids)];
        $this->assertNotEquals(0, $row['location']->latitude, 'A localização retornada deve ser a georreferenciada');
        $this->assertNotEquals(0, $row['location']->longitude, 'A localização retornada deve ser a georreferenciada');
    }

    function testOpportunityApiLocationFilterExcludesEntitiesWithoutAddress()
    {
        $project = $this->createProjectWith();

        $located = $this->createOpportunityFor($project, function (Opportunity $opportunity) {
            $opportunity->publicLocation = true;
            $opportunity->location = new GeoPoint(-46.633309, -23.55052);
        });

        $without_address = $this->createOpportunityFor($project);

        $this->logout();

        $query = new ApiQuery(Opportunity::class, [
            '@select' => 'id,location',
            'location' => '!EQ([0,0])',
        ]);

        $result = $query->find();
        $result_ids = array_column($result, 'id');

        $this->assertContains($located->id, $result_ids, 'O filtro location !EQ([0,0]) deve incluir a oportunidade georreferenciada');
        $this->assertNotContains($without_address->id, $result_ids, 'O filtro location !EQ([0,0]) deve excluir a oportunidade sem endereço');

        $row = $result[array_search($located->id, $result_ids)];
        $this->assertNotEquals(0, $row['location']->latitude, 'A localização retornada deve ser a georreferenciada');
        $this->assertNotEquals(0, $row['location']->longitude, 'A localização retornada deve ser a georreferenciada');
    }
}
