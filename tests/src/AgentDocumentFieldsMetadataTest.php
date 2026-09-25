<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Abstract\TestCase;
use Tests\Traits\UserDirector;

/**
 * Critério de Sucesso #3 — "Par dado+anexo persiste corretamente ao salvar
 * o Agent".
 *
 * Para os campos de DADO (texto/select), segue o padrão de EntityMetadataTest:
 * atribuir um valor, salvar, recarregar do banco (em->clear + refreshed) e
 * afirmar que o valor persistiu.
 *
 * Para os ANEXOS (type => 'file'), o upload real de arquivo é tratado por uma
 * rota/fluxo próprio (ControllerUploads + trait EntityFiles); nestes testes
 * verificamos o que está ao alcance da camada de metadados/FileGroup:
 *   - o metadado existe e é do tipo 'file';
 *   - o FileGroup correspondente (L2) está registrado em App.php.
 * Sem FileGroup registrado, o upload seria rejeitado — esta é a falha silenciosa
 * mais comum do modelo de 3 camadas (ver Matriz de Rastreabilidade da spec).
 */
class AgentDocumentFieldsMetadataTest extends TestCase
{
    use UserDirector;

    /**
     * Campos de DADO (não-anexo) que devem suportar o ciclo
     * atribuir → salvar → recarregar → conferir.
     */
    public static function scalarDocumentFields(): array
    {
        return [
            'passaporteNumero' => ['passaporteNumero', 'AB123456'],
            'rgNumero'         => ['rgNumero', '12.345.678-9'],
            'rgOrgaoEmissor'   => ['rgOrgaoEmissor', 'SSP-PE'],
            'rgUF'             => ['rgUF', 'PE'],
        ];
    }

    /**
     * Anexos (type => 'file') mapeados ao nome do FileGroup (L2) que autoriza
     * o upload. Cada linha reproduz a Matriz de Rastreabilidade da spec:
     *   metadado  ->  group-name registrado em App.php
     */
    public static function attachmentMetadataAndGroups(): array
    {
        return [
            'rgAnexo'                        => ['rgAnexo', 'docs-rg'],
            'passaporteAnexo'                => ['passaporteAnexo', 'docs-passaporte'],
            'racaAnexo'                      => ['racaAnexo', 'docs-raca'],
            'pessoaDeficienciaAnexo'         => ['pessoaDeficienciaAnexo', 'docs-pcd'],
            'comunidadesTradicionalAnexo'    => ['comunidadesTradicionalAnexo', 'docs-comunidades'],
            'comprovanteResidenciaAnexo'     => ['comprovanteResidenciaAnexo', 'docs-residencia'],
            'comprovanteVinculoTerritorialAnexo' => ['comprovanteVinculoTerritorialAnexo', 'docs-vinculo-territorial'],
            'curriculoAnexo'                 => ['curriculoAnexo', 'docs-curriculo'],
            'portfolioAnexo'                 => ['portfolioAnexo', 'docs-portfolio'],
            'certidaoFiscalAnexo'            => ['certidaoFiscalAnexo', 'docs-certidao-fiscal'],
            'certidaoTrabalhistaAnexo'       => ['certidaoTrabalhistaAnexo', 'docs-certidao-trabalhista'],
            'certidaoPrestacaoContasAnexo'   => ['certidaoPrestacaoContasAnexo', 'docs-certidao-contas'],
        ];
    }

    /**
     * Ciclo de persistência de um campo de dado escalar (texto/select).
     * Padrão retirado de EntityMetadataTest::testMetadataUpdate.
     */
    #[DataProvider('scalarDocumentFields')]
    public function testScalarFieldPersistsAcrossSave(string $field, string $value): void
    {
        $app = $this->app;

        $user = $this->userDirector->createUser();
        $this->login($user);

        $agent = $app->repo('Agent')->find($user->profile->id);
        $agent->$field = $value;
        $agent->save(true);

        $app->em->clear();

        $reloaded = $app->repo('Agent')->find($agent->id);
        $this->assertSame(
            $value,
            $reloaded->$field,
            "Esperado que o metadado '{$field}' persistisse com o valor '{$value}' após salvar e recarregar."
        );
    }

    /**
     * Atualizar um campo já preenchido deve sobrescrever o valor anterior
     * (regressão de "segundo save não grava").
     */
    #[DataProvider('scalarDocumentFields')]
    public function testScalarFieldCanBeUpdated(string $field, string $value): void
    {
        $app = $this->app;

        $user = $this->userDirector->createUser();
        $this->login($user);

        $agent = $app->repo('Agent')->find($user->profile->id);

        $agent->$field = $value;
        $agent->save(true);

        $updatedValue = $value . '-ATUALIZADO';
        $agent->$field = $updatedValue;
        $agent->save(true);

        $app->em->clear();
        $reloaded = $app->repo('Agent')->find($agent->id);

        $this->assertSame(
            $updatedValue,
            $reloaded->$field,
            "Esperado que '{$field}' refletisse o segundo valor atribuído após novo save."
        );
    }

    /**
     * Cada anexo precisa existir como metadado do tipo 'file' — caso
     * contrário o componente <entity-field> não renderiza o upload (o
     * field_type 'file' dispara o template <entity-file>).
     */
    #[DataProvider('attachmentMetadataAndGroups')]
    public function testAttachmentMetadataIsRegisteredAsFileType(string $metadata, string $groupName): void
    {
        $definitions = Agent::getPropertiesMetadata();

        $this->assertArrayHasKey(
            $metadata,
            $definitions,
            "O metadado de anexo '{$metadata}' não está registrado em Agent."
        );

        $def = (object) $definitions[$metadata];

        $this->assertTrue(
            $def->isMetadata,
            "Esperado que '{$metadata}' seja um metadado."
        );

        $this->assertSame(
            'file',
            $def->type,
            "Esperado que o metadado '{$metadata}' tenha type='file' (caso contrário o upload não renderiza)."
        );
    }

    /**
     * Cada anexo precisa de um FileGroup registrado (L2) para que o upload
     * seja aceito. Esta é a falha silenciosa central do modelo de 3 camadas:
     * sem o FileGroup, o arquivo é rejeitado com erro genérico de MIME/grupo.
     */
    #[DataProvider('attachmentMetadataAndGroups')]
    public function testFileGroupIsRegistered(string $metadata, string $groupName): void
    {
        $app = App::i();

        $group = $app->getRegisteredFileGroup('agent', $groupName);

        $this->assertNotNull(
            $group,
            "Esperado FileGroup 'agent/{$groupName}' registrado (anexo '{$metadata}'). " .
            "Sem ele, o upload do anexo é rejeitado."
        );

        // Anexos de dados sensíveis/documentos devem ser private => true (LGPD).
        $this->assertTrue(
            $group->private,
            "Esperado que o FileGroup 'agent/{$groupName}' seja private (LGPD: documento sensível)."
        );
    }
}
