<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\RegistrationFieldConfiguration;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Abstract\TestCase;
use Tests\Traits\AgentDirector;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationFieldBuilder;
use Tests\Traits\UserDirector;

/**
 * Critério de Sucesso #5 — os novos campos são reconhecidos como campos "@"
 * válidos no fluxo de registro de metadados da oportunidade.
 *
 * Quando um gestor vincula um campo "do agente responsável"
 * (fieldType = 'agent-owner-field') a uma propriedade de Agent via
 * config['entityField'], o Opportunity::registerRegistrationMetadata()
 * (Opportunity.php:1661-1700) precisa:
 *   1. localizar o metadado em Agent::getPropertiesMetadata();
 *   2. resolver o field_type (ex.: 'file', 'text', 'select');
 *   3. registrar um metadado correspondente na classe Registration.
 *
 * Se o campo não existir como metadado de Agent, o field_type resolve para
 * 'text' (fallback) silenciosamente — e o campo quebra em runtime. Este teste
 * garante que a resolução funciona para os novos campos e não lança erro.
 */
class AgentDocumentFieldsRegistrationTest extends TestCase
{
    use UserDirector, AgentDirector, OpportunityBuilder, RegistrationFieldBuilder;

    /**
     * Campos representativos por tipo resolvido:
     *  - 'text'  : passaporteNumero (dado);
     *  - 'file'  : racaAnexo, passaporteAnexo, certidaoFiscalAnexo (anexos);
     *  - 'select': rgUF.
     *
     * O segundo elemento é o field_type esperado após registerRegistrationMetadata().
     */
    public static function agentOwnerFieldsAndResolvedTypes(): array
    {
        return [
            'passaporteNumero (text)'    => ['passaporteNumero', 'text'],
            'rgUF (select)'              => ['rgUF', 'select'],
            'racaAnexo (file)'           => ['racaAnexo', 'file'],
            'passaporteAnexo (file)'     => ['passaporteAnexo', 'file'],
            'certidaoFiscalAnexo (file)' => ['certidaoFiscalAnexo', 'file'],
        ];
    }

    /**
     * Cria uma oportunidade mínima (com fase de inscrição) de propriedade do
     * admin, pronta para receber RegistrationFieldConfigurations.
     */
    protected function createOpportunity(): Opportunity
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);

        $opportunity = $this->opportunityBuilder
            ->reset(owner: $owner, owner_entity: $owner)
            ->fillRequiredProperties()
            ->setProponentTypes(['Pessoa Física'])
            ->save()
            ->firstPhase()
                ->fillRequiredProperties()
                ->save()
                ->done()
            ->save()
            ->refresh()
            ->getInstance();

        return $opportunity;
    }

    /**
     * Adiciona um campo "agent-owner-field" à oportunidade, apontando para a
     * propriedade de Agent informada, e dispara o registro de metadados da
     * inscrição. Retorna a configuração criada (com id, logo acessível via
     * getFieldName() => 'field_{id}').
     */
    protected function addAgentOwnerField(Opportunity $opportunity, string $entityField): RegistrationFieldConfiguration
    {
        $this->login($opportunity->owner->user);

        $field = $this->registrationFieldBuilder
            ->reset($opportunity, 'agent-owner-field', 'agent-field-' . $entityField)
            ->fillRequiredProperties()
            ->setEntityField($entityField)
            ->save()
            ->getInstance();

        // Exercita o fluxo central que resolve o field_type e registra o
        // metadado na classe Registration. Se algo estiver inconsistente,
        // é aqui que a exceção apareceria.
        $opportunity->registerRegistrationMetadata();

        return $field;
    }

    /**
     * O registerRegistrationMetadata() registra, em Registration, um metadado
     * cuja chave é 'field_{id da configuração}'. Este teste confirma que o
     * metadado foi registrado e que o field_type foi resolvido corretamente
     * a partir do metadado de Agent (não caiu no fallback 'text' para anexos).
     */
    #[DataProvider('agentOwnerFieldsAndResolvedTypes')]
    public function testAgentOwnerFieldRegistersMetadataWithResolvedType(string $entityField, string $expectedType): void
    {
        $app = App::i();
        $opportunity = $this->createOpportunity();

        $field = $this->addAgentOwnerField($opportunity, $entityField);

        $registered = $app->getRegisteredMetadata(Registration::class);
        $registrationMetaKey = $field->getFieldName(); // 'field_{id}'

        $this->assertArrayHasKey(
            $registrationMetaKey,
            $registered,
            "Esperado que o metadado '{$registrationMetaKey}' fosse registrado em Registration " .
            "após vincular agent-owner-field -> '{$entityField}'."
        );

        $resolved = (object) $registered[$registrationMetaKey];

        $this->assertSame(
            $expectedType,
            $resolved->type,
            "Para o campo '@{$entityField}', esperado field_type resolvido='{$expectedType}', " .
            "obtido='{$resolved->type}'. Se for 'file' e resolveu 'text', o metadado de Agent " .
            "não está com type='file'."
        );
    }

    /**
     * O campo precisa existir em getAgentFields() para ser vinculável no
     * form-builder. Este teste cruza o critério #2 com o #5: confirma que o
     * campo que vamos vincular realmente aparece como "@" (consistência
     * entre form-builder e registerRegistrationMetadata).
     */
    #[DataProvider('agentOwnerFieldsAndResolvedTypes')]
    public function testAgentOwnerFieldIsSelectableInFormBuilder(string $entityField, string $expectedType): void
    {
        $app = App::i();
        $agentFields = $app->modules['RegistrationFieldTypes']->getAgentFields();

        $this->assertContains(
            $entityField,
            $agentFields,
            "Esperado que '{$entityField}' estivesse em getAgentFields() para ser vinculável " .
            "como agent-owner-field no form-builder."
        );
    }

    /**
     * Sanity: o metadado de Agent referenciado precisa existir. Se
     * registerRegistrationMetadata() não encontrar o metadado, o field_type
     * cai no fallback 'text' e o campo quebra em runtime — por isso este
     * pré-requisito é checado isoladamente.
     */
    #[DataProvider('agentOwnerFieldsAndResolvedTypes')]
    public function testReferencedAgentMetadataExists(string $entityField, string $expectedType): void
    {
        $definitions = Agent::getPropertiesMetadata();

        $this->assertArrayHasKey(
            $entityField,
            $definitions,
            "O metadado de Agent '{$entityField}' precisa existir para que o " .
            "agent-owner-field o resolva corretamente."
        );
    }
}
