<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Abstract\TestCase;

/**
 * Critério de Sucesso #2 — "Campos novos aparecem como selecionáveis no
 * form-builder (campos @)".
 *
 * Verifica que a lista de campos "@" exposta ao form-builder de oportunidades,
 * produzida por RegistrationFieldTypes\Module::getAgentFields(), contém todos
 * os 16 campos (4 do RG ativados + 12 novos), e que cada um deles está
 * declarado com available_for_opportunities => true no metadado do Agent.
 *
 * A presença em getAgentFields() é determinada por (Module.php:1028):
 *     if ($def->isMetadata && $def->available_for_opportunities) { ... }
 * Logo, este teste exercita exatamente o filtro que decide o que aparece no
 * <select> "Campo do agente responsável" do form-builder.
 */
class AgentFieldsForOpportunitiesTest extends TestCase
{
    /**
     * Os 16 campos cobertos por esta implementação:
     *  - 4 do RG (ativação do flag available_for_opportunities)
     *  - passaporteNumero + passaporteAnexo (novo par)
     *  - 3 anexos de dados existentes (raça, PCD, comunidades)
     *  - 7 anexos puros (comprovantes, currículo, portfólio, certidões)
     */
    public static function newDocumentFields(): array
    {
        return [
            // --- RG (ativação) ---
            'rgNumero'               => ['rgNumero'],
            'rgAnexo'                => ['rgAnexo'],
            'rgOrgaoEmissor'         => ['rgOrgaoEmissor'],
            'rgUF'                   => ['rgUF'],
            // --- Passaporte (novo par) ---
            'passaporteNumero'       => ['passaporteNumero'],
            'passaporteAnexo'        => ['passaporteAnexo'],
            // --- Anexos de dados existentes ---
            'racaAnexo'              => ['racaAnexo'],
            'pessoaDeficienciaAnexo' => ['pessoaDeficienciaAnexo'],
            'comunidadesTradicionalAnexo' => ['comunidadesTradicionalAnexo'],
            // --- Anexos puros ---
            'comprovanteResidenciaAnexo'         => ['comprovanteResidenciaAnexo'],
            'comprovanteVinculoTerritorialAnexo' => ['comprovanteVinculoTerritorialAnexo'],
            'curriculoAnexo'                     => ['curriculoAnexo'],
            'portfolioAnexo'                     => ['portfolioAnexo'],
            'certidaoFiscalAnexo'                => ['certidaoFiscalAnexo'],
            'certidaoTrabalhistaAnexo'           => ['certidaoTrabalhistaAnexo'],
            'certidaoPrestacaoContasAnexo'       => ['certidaoPrestacaoContasAnexo'],
        ];
    }

    /**
     * Cada um dos 16 metadados precisa estar declarado com
     * available_for_opportunities => true. Sem isso, o filtro de
     * getAgentFields() (Module.php:1028) omite o campo.
     */
    #[DataProvider('newDocumentFields')]
    public function testMetadataHasAvailableForOpportunitiesEnabled(string $field): void
    {
        $definitions = Agent::getPropertiesMetadata();

        $this->assertArrayHasKey(
            $field,
            $definitions,
            "O metadado '{$field}' não está registrado em Agent::getPropertiesMetadata()."
        );

        $def = (object) $definitions[$field];

        $this->assertTrue(
            $def->isMetadata,
            "Esperado que '{$field}' seja um metadado (isMetadata=true)."
        );

        $this->assertTrue(
            $def->available_for_opportunities,
            "Esperado que '{$field}' tenha available_for_opportunities=true; " .
            "sem este flag o campo não aparece no form-builder de oportunidades."
        );
    }

    /**
     * Garante que getAgentFields() retorna TODOS os 16 campos de uma só vez.
     * Este é o teste de "porta de saída": se a lista retornada para o
     * <select> do form-builder não contiver um campo, o gestor não consegue
     * vinculá-lo à oportunidade.
     */
    public function testGetAgentFieldsIncludesAllNewDocumentFields(): void
    {
        $app = App::i();

        $module = $app->modules['RegistrationFieldTypes'] ?? null;
        $this->assertNotNull(
            $module,
            'O módulo RegistrationFieldTypes deve estar registrado para expor getAgentFields().'
        );

        $agentFields = $module->getAgentFields();

        foreach (array_keys(self::newDocumentFields()) as $field) {
            $this->assertContains(
                $field,
                $agentFields,
                "Esperado que getAgentFields() retorne '{$field}' (campo '@' do form-builder)."
            );
        }
    }

    /**
     * Confirma que os campos de referência (cpf/cpfAnexo/cnhNumero) continuam
     * presentes — proteção de regressão: a adição dos novos campos não pode
     * deslocar/remover os campos "@" hoje funcionais.
     */
    public function testReferenceFieldsRemainAvailableForOpportunities(): void
    {
        $app = App::i();
        $agentFields = $app->modules['RegistrationFieldTypes']->getAgentFields();

        foreach (['cpf', 'cpfAnexo', 'cnpj', 'cnpjAnexo', 'cnhNumero', 'cnhAnexo'] as $reference) {
            $this->assertContains(
                $reference,
                $agentFields,
                "Campo de referência '{$reference}' deixou de aparecer em getAgentFields() (regressão)."
            );
        }
    }
}
