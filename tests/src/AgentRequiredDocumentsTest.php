<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\AgentMeta;
use MapasCulturais\Entities\Term;
use Tests\Abstract\TestCase;
use Tests\Traits\AgentDirector;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;

/**
 * Issue #32: exigência de documento (CPF/CNPJ) no agente conforme o tipo,
 * controlada pela flag de config "agents.requiredDocumentsByType"
 * (env AGENTS_REQUIRED_DOCUMENTS_BY_TYPE, default false).
 *
 * A flag é mutada em runtime porque a exigência é injetada dinamicamente no
 * hook entity(Agent).validations (módulo AgentDocuments), disparado por
 * Entity::getValidationErrors() a cada salvamento (criação, edição e
 * publicação), lendo a config em tempo de execução.
 *
 * Notas do ambiente que moldam os testes:
 * - cpf/cnpj/documento são metadados privados: só são lidos/validados quando
 *   o usuário corrente pode viewPrivateData (dono logado ou admin). Por isso
 *   os testes sempre trabalham logados com o dono do agente.
 * - Agent::setType() exige permissão changeType (admin) nesta base: criar
 *   agentes tipo 1 exige access control desligado (padrão dos Directors) ou
 *   login admin. Sem isso o tipo seria forçado para 2 (Coletivo).
 * - A taxonomia "area" é obrigatória: payloads HTTP de criação de agente
 *   precisam enviar "terms" => ["area" => [...]].
 */
class AgentRequiredDocumentsTest extends TestCase
{
    use UserDirector,
        AgentDirector,
        RequestFactory;

    private const CONFIG_KEY = 'agents.requiredDocumentsByType';

    private const CPF_VALIDO = '111.444.777-35';
    private const CNPJ_VALIDO = '11.444.777/0001-61';
    private const CPF_INVALIDO = '052.667.910-00';

    private const MSG_CPF_REQUIRED = 'O CPF é obrigatório para este tipo de agente.';
    private const MSG_CNPJ_REQUIRED = 'O CNPJ é obrigatório para este tipo de agente.';
    private const MSG_CPF_INVALIDO = 'O número de CPF informado é inválido.';

    protected function setUp(): void
    {
        parent::setUp();
        // Isola testes da config/env da instância — flag-on chama enableFlag().
        App::i()->config[self::CONFIG_KEY] = false;
    }

    protected function tearDown(): void
    {
        // restaura o default da suíte (flag desligada) para não vazar estado
        App::i()->config[self::CONFIG_KEY] = false;
        parent::tearDown();
    }

    private function enableFlag(): void
    {
        App::i()->config[self::CONFIG_KEY] = true;
    }

    /**
     * Executa operações de persistência direta (builders/Entity::save) com o
     * access control desligado, religando ao final — padrão dos Directors.
     */
    private function withAccessControlDisabled(callable $operations): void
    {
        $app = App::i();
        $app->disableAccessControl();
        try {
            $operations();
        } finally {
            $app->enableAccessControl();
        }
    }

    /**
     * Cria um agente (sem salvar) já logado com o dono, garantindo tipo
     * correto (changeType exige AC desligado para não-admin) e visibilidade
     * dos metadados privados cpf/cnpj.
     */
    private function buildAgentForUser($user, int $type): Agent
    {
        $agent = null;
        $this->withAccessControlDisabled(function () use ($user, $type, &$agent) {
            $agent = $this->agentDirector->createAgent($user, $type, save: false);
        });
        return $agent;
    }

    /**
     * Um termo real da taxonomia obrigatória "area" para payloads HTTP.
     */
    private function areaTerm(): string
    {
        $term = App::i()->repo(Term::class)->findOneBy(['taxonomy' => 'area']);
        if (!$term) {
            $this->fail('Seed do banco de testes deveria conter termos da taxonomia area.');
        }
        return $term->term;
    }

    /**
     * AC-1: flag ON, agente Individual (tipo 1) sem CPF — a validação deve
     * recusar pedindo o CPF (e não o CNPJ).
     */
    function testFlagOnTipo1SemCpfExigeCpf()
    {
        $user = $this->userDirector->createUser();
        $this->login($user);

        $this->enableFlag();

        $agent = $this->buildAgentForUser($user, 1);

        $errors = $agent->validationErrors;

        $this->assertArrayHasKey('cpf', $errors, 'Agente Individual sem CPF deve gerar erro de CPF obrigatório.');
        $this->assertContains(self::MSG_CPF_REQUIRED, $errors['cpf']);
        $this->assertArrayNotHasKey('cnpj', $errors, 'Agente Individual não deve exigir CNPJ.');
    }

    /**
     * AC-1: flag ON, agente Individual (tipo 1) com CPF válido — salva.
     */
    function testFlagOnTipo1ComCpfValidoSalva()
    {
        $user = $this->userDirector->createUser();
        $this->login($user);

        $this->enableFlag();

        $agent = $this->buildAgentForUser($user, 1);

        $this->withAccessControlDisabled(function () use ($agent) {
            $agent->cpf = self::CPF_VALIDO;
        });

        $errors = $agent->validationErrors;
        $this->assertArrayNotHasKey('cpf', $errors, 'CPF válido preenchido não deve gerar erro.');
        $this->assertArrayNotHasKey('cnpj', $errors);

        $this->withAccessControlDisabled(fn () => $agent->save(true));
        $this->assertNotEmpty($agent->id, 'Agente Individual com CPF válido deve salvar.');
    }

    /**
     * AC-1: flag ON, agente Coletivo (tipo 2) sem CNPJ — a validação deve
     * recusar pedindo o CNPJ (e não o CPF).
     */
    function testFlagOnTipo2SemCnpjExigeCnpj()
    {
        $user = $this->userDirector->createUser();
        $this->login($user);

        $this->enableFlag();

        $agent = $this->buildAgentForUser($user, 2);

        $errors = $agent->validationErrors;

        $this->assertArrayHasKey('cnpj', $errors, 'Agente Coletivo sem CNPJ deve gerar erro de CNPJ obrigatório.');
        $this->assertContains(self::MSG_CNPJ_REQUIRED, $errors['cnpj']);
        $this->assertArrayNotHasKey('cpf', $errors, 'Agente Coletivo não deve exigir CPF.');
    }

    /**
     * AC-1: flag ON, agente Coletivo (tipo 2) com CNPJ válido — salva.
     */
    function testFlagOnTipo2ComCnpjValidoSalva()
    {
        $user = $this->userDirector->createUser();
        $this->login($user);

        $this->enableFlag();

        $agent = $this->buildAgentForUser($user, 2);

        $this->withAccessControlDisabled(function () use ($agent) {
            $agent->cnpj = self::CNPJ_VALIDO;
        });

        $errors = $agent->validationErrors;
        $this->assertArrayNotHasKey('cnpj', $errors, 'CNPJ válido preenchido não deve gerar erro.');
        $this->assertArrayNotHasKey('cpf', $errors);

        $this->withAccessControlDisabled(fn () => $agent->save(true));
        $this->assertNotEmpty($agent->id, 'Agente Coletivo com CNPJ válido deve salvar.');
    }

    /**
     * AC-1 (fluxo HTTP de criação, tipo 1): POST /agent com flag ON — como
     * admin (changeType é admin-only), cadastro Individual sem CPF recusado
     * (400); com CPF válido, aceito (200).
     */
    function testFlagOnCriacaoHttpDeIndividualSemCpfERecusada()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $this->enableFlag();

        $payload_base = [
            'name' => 'Agente Individual de Teste',
            'shortDescription' => 'Descrição curta do agente de teste.',
            'type' => 1,
            'terms' => ['area' => [$this->areaTerm()]],
        ];

        $request = $this->requestFactory->POST('agent', 'index', payload: $payload_base);
        $this->assertStatus400($request, 'POST /agent tipo 1 sem CPF deve recusar (400) e pedir o CPF.');

        $request = $this->requestFactory->POST('agent', 'index', payload: $payload_base + ['cpf' => self::CPF_VALIDO]);
        $this->assertStatus200($request, 'POST /agent tipo 1 com CPF válido deve salvar.');
    }

    /**
     * AC-1 (fluxo HTTP de criação, tipo 2): POST /agent com flag ON —
     * cadastro Coletivo sem CNPJ recusado (400); com CNPJ válido, aceito (200).
     */
    function testFlagOnCriacaoHttpDeColetivoSemCnpjERecusada()
    {
        $user = $this->userDirector->createUser();
        $this->login($user);

        $this->enableFlag();

        $payload_base = [
            'name' => 'Agente Coletivo de Teste',
            'shortDescription' => 'Descrição curta do agente coletivo.',
            'type' => 2,
            'terms' => ['area' => [$this->areaTerm()]],
        ];

        $request = $this->requestFactory->POST('agent', 'index', payload: $payload_base);
        $this->assertStatus400($request, 'POST /agent tipo 2 sem CNPJ deve recusar (400) e pedir o CNPJ.');

        $request = $this->requestFactory->POST('agent', 'index', payload: $payload_base + ['cnpj' => self::CNPJ_VALIDO]);
        $this->assertStatus200($request, 'POST /agent tipo 2 com CNPJ válido deve salvar.');
    }

    /**
     * AC-2: flag ON, agente existente criado sem documento (legado, flag OFF
     * na criação) — nenhuma edição completa salva até o documento ser
     * preenchido: PUT_single (via POST_single) recusa (400), publish recusa
     * (400) e PATCH que envia o campo de documento vazio recusa (400).
     * Completando o documento, as mesmas operações passam (200).
     */
    function testFlagOnEdicaoDeAgenteExistenteSemDocumentoERecusada()
    {
        $user = $this->userDirector->createUser();

        // agente criado com a flag desligada (legado, sem documento)
        $agent = null;
        $this->withAccessControlDisabled(function () use ($user, &$agent) {
            $agent = $this->agentDirector->createAgent($user, 1);
        });
        $this->assertNotEmpty($agent->id, 'Com a flag desligada o agente salva como hoje.');

        $this->enableFlag();

        $this->login($user);

        // edição completa (POST_single é alias de PUT_single): validação
        // integral — sem CPF, a requisição é recusada
        $put_payload = [
            'name' => 'Novo Nome',
            'shortDescription' => 'Nova descrição curta.',
        ];
        $request = $this->requestFactory->POST('agent', 'single', [$agent->id], payload: $put_payload);
        $this->assertStatus400($request, 'PUT/POST single completo sem CPF deve recusar (400).');

        // publicação do rascunho também é bloqueada (ControllerDraft::ALL_publish)
        $request = $this->requestFactory->POST('agent', 'publish', [$agent->id]);
        $this->assertStatus400($request, 'publish sem CPF deve recusar (400).');

        // fluxo de edição pontual: ao enviar o formulário incluindo o CPF vazio,
        // a resposta deve ser 400 (recusa e pede o CPF)
        $request = $this->requestFactory->PATCH('agent', 'single', [$agent->id], payload: [
            'name' => 'Novo Nome',
            'cpf' => '',
        ]);
        $this->assertStatus400($request, 'PATCH single com CPF vazio deve recusar (400) e pedir o CPF.');

        // completando o documento, a mesma edição passa a salvar
        $request = $this->requestFactory->PATCH('agent', 'single', [$agent->id], payload: [
            'name' => 'Novo Nome',
            'cpf' => self::CPF_VALIDO,
        ]);
        $this->assertStatus200($request, 'PATCH single com CPF válido deve salvar.');

        // e a edição completa (PUT_single), que antes recusava, agora passa
        $request = $this->requestFactory->POST('agent', 'single', [$agent->id], payload: $put_payload);
        $this->assertStatus200($request, 'PUT/POST single completo com CPF preenchido deve salvar.');

        $app = App::i();
        $app->em->clear();
        $reloaded = $app->repo(Agent::class)->find($agent->id);
        $this->assertNotEmpty(trim((string) $reloaded->cpf), 'CPF deve persistir após a edição aceita.');
    }

    /**
     * Semântica pré-existente do PATCH parcial (ControllerEntityActions):
     * erros de campos NÃO enviados no payload não bloqueiam o PATCH — este é
     * o comportamento do core para qualquer campo obrigatório hoje. O
     * enforcement macro continua sendo o funil de completude
     * (validationErrors não vazio mantém o usuário preso na edição do perfil
     * — Theme.php app.redirect_profile_validate). Teste documenta o
     * comportamento para conhecimento da issue.
     */
    function testFlagOnPatchParcialDeCampoNaoEnviadoSegueSemanticaAtualDoCore()
    {
        $user = $this->userDirector->createUser();

        $agent = null;
        $this->withAccessControlDisabled(function () use ($user, &$agent) {
            $agent = $this->agentDirector->createAgent($user, 1);
        });

        $this->enableFlag();

        $this->login($user);
        $request = $this->requestFactory->PATCH('agent', 'single', [$agent->id], payload: [
            'name' => 'Nome Alterado',
        ]);
        $this->assertStatus200($request, 'PATCH parcial sem tocar no campo de documento segue a semântica atual do core.');

        // ainda assim, a validação completa continua apontando o documento faltante
        $app = App::i();
        $app->em->clear();
        $reloaded = $app->repo(Agent::class)->find($agent->id);
        $this->assertArrayHasKey('cpf', $reloaded->validationErrors, 'validationErrors continua exigindo o CPF (funil).');
    }

    /**
     * A exigência considera o valor EFETIVO após o fallback cpf -> documento
     * (agentes legados que possuem apenas o metadado "documento").
     */
    function testFlagOnDocumentoLegadoSatisfazExigenciaDeCpf()
    {
        $app = App::i();
        $user = $this->userDirector->createUser();
        $this->login($user);

        // agente legado: apenas o metadado documento preenchido, sem cpf
        $agent = null;
        $this->withAccessControlDisabled(function () use ($app, $user, &$agent) {
            $agent = $this->agentDirector->createAgent($user, 1);

            $meta = new AgentMeta;
            $meta->key = 'documento';
            $meta->owner = $agent;
            $meta->value = self::CPF_VALIDO;
            $meta->save(true);
        });

        $this->enableFlag();

        $app->em->clear();
        $reloaded = $app->repo(Agent::class)->find($agent->id);

        $this->assertSame(self::CPF_VALIDO, $reloaded->documento, 'Sanity: metadado documento legado presente.');
        $this->assertArrayNotHasKey('cpf', $reloaded->validationErrors, 'Documento legado em "documento" deve satisfazer a exigência de CPF.');
    }

    /**
     * AC-5: documento preenchido com valor inválido continua rejeitado com a
     * mensagem atual de formato, com a flag ligada E desligada.
     */
    function testDocumentoInvalidoMantemMensagemAtualDeFormato()
    {
        $user = $this->userDirector->createUser();
        $this->login($user);

        foreach ([true, false] as $flag) {
            App::i()->config[self::CONFIG_KEY] = $flag;

            $agent = $this->buildAgentForUser($user, 1);

            $this->withAccessControlDisabled(function () use ($agent) {
                $agent->cpf = self::CPF_INVALIDO;
            });

            $errors = $agent->validationErrors;
            $this->assertArrayHasKey('cpf', $errors, 'Flag ' . var_export($flag, true) . ': CPF inválido deve gerar erro.');
            $this->assertContains(self::MSG_CPF_INVALIDO, $errors['cpf'], 'Mensagem atual de formato deve ser mantida.');
            $this->assertNotContains(self::MSG_CPF_REQUIRED, $errors['cpf'], 'Valor inválido preenchido gera erro de formato, não de obrigatoriedade.');
        }
    }

    /**
     * AC-4: flag OFF/ausente — comportamento idêntico ao atual: agentes sem
     * documento não geram erro e salvam normalmente (nos dois tipos).
     */
    function testFlagOffComportaComoAtual()
    {
        App::i()->config[self::CONFIG_KEY] = false;

        $user = $this->userDirector->createUser();
        $this->login($user);

        $individual = $this->buildAgentForUser($user, 1);
        $errors = $individual->validationErrors;
        $this->assertArrayNotHasKey('cpf', $errors, 'Flag off: CPF não deve ser exigido.');
        $this->assertArrayNotHasKey('cnpj', $errors, 'Flag off: CNPJ não deve ser exigido.');
        $this->withAccessControlDisabled(fn () => $individual->save(true));
        $this->assertNotEmpty($individual->id, 'Flag off: Individual sem CPF salva como hoje.');

        $coletivo = $this->buildAgentForUser($user, 2);
        $errors = $coletivo->validationErrors;
        $this->assertArrayNotHasKey('cpf', $errors);
        $this->assertArrayNotHasKey('cnpj', $errors);
        $this->withAccessControlDisabled(fn () => $coletivo->save(true));
        $this->assertNotEmpty($coletivo->id, 'Flag off: Coletivo sem CNPJ salva como hoje.');
    }

    /**
     * AC-4 (fluxo HTTP): flag OFF, criação de agente sem documento via POST
     * /agent é aceita como hoje (200), nos dois tipos.
     */
    function testFlagOffCriacaoHttpSemDocumentoAceitaComoAtual()
    {
        App::i()->config[self::CONFIG_KEY] = false;

        $user = $this->userDirector->createUser();
        $this->login($user);

        $payload_base = [
            'shortDescription' => 'Descrição curta do agente sem documento.',
            'terms' => ['area' => [$this->areaTerm()]],
        ];

        // tipo 2: usuário comum pode criar (changeType admin-only força 2)
        $request = $this->requestFactory->POST('agent', 'index', payload: $payload_base + [
            'name' => 'Coletivo Sem Documento',
            'type' => 2,
        ]);
        $this->assertStatus200($request, 'Flag off: POST /agent tipo 2 sem CNPJ salva como hoje.');

        // tipo 1: exige admin (changeType)
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);
        $request = $this->requestFactory->POST('agent', 'index', payload: $payload_base + [
            'name' => 'Individual Sem Documento',
            'type' => 1,
        ]);
        $this->assertStatus200($request, 'Flag off: POST /agent tipo 1 sem CPF salva como hoje.');
    }

    /**
     * AC-3: formulários marcam o campo obrigatório correto conforme o tipo —
     * isPropertyRequired é o mecanismo usado pelos templates do BaseV1
     * (agent-form-1/2) e, via binding, pelas views de edição do BaseV2.
     */
    function testFlagOnMarcaCampoCorretoComoObrigatorioNosFormularios()
    {
        $user = $this->userDirector->createUser();
        $this->login($user);

        $this->enableFlag();

        $individual = $this->buildAgentForUser($user, 1);
        $coletivo = $this->buildAgentForUser($user, 2);

        $this->assertTrue($individual->isPropertyRequired($individual, 'cpf'), 'Individual: CPF marcado como obrigatório.');
        $this->assertFalse($individual->isPropertyRequired($individual, 'cnpj'), 'Individual: CNPJ NÃO marcado como obrigatório.');

        $this->assertTrue($coletivo->isPropertyRequired($coletivo, 'cnpj'), 'Coletivo: CNPJ marcado como obrigatório.');
        $this->assertFalse($coletivo->isPropertyRequired($coletivo, 'cpf'), 'Coletivo: CPF NÃO marcado como obrigatório.');

        // flag desligada: nada marcado (comportamento atual)
        App::i()->config[self::CONFIG_KEY] = false;
        $this->assertFalse($individual->isPropertyRequired($individual, 'cpf'), 'Flag off: CPF não marcado.');
        $this->assertFalse($coletivo->isPropertyRequired($coletivo, 'cnpj'), 'Flag off: CNPJ não marcado.');
    }

    /**
     * AC-3 (templates): os formulários dos dois temas marcam o campo do
     * documento conforme o tipo — BaseV1 via isPropertyRequired no template
     * do campo, BaseV2 via binding :required nas views de edição por tipo
     * (edit-1 = Individual/cpf, edit-2 = Coletivo/cnpj), com o CNPJ do MEI
     * no Individual permanecendo opcional.
     */
    function testTemplatesDosFormulariosMarcamOCampoCertoPorTipo()
    {
        $root = dirname(__DIR__);

        $assertFileContains = function (string $path, string $needle, string $message) {
            $this->assertFileExists($path, "Template não encontrado: {$path}");
            $this->assertStringContainsString(
                $needle,
                file_get_contents($path),
                $message
            );
        };

        // BaseV1: form do Individual (tipo 1) marca CPF via isPropertyRequired
        $assertFileContains(
            $root . '/src/themes/BaseV1/layouts/parts/singles/agent-form-1.php',
            'isPropertyRequired($entity,"cpf")',
            'BaseV1 agent-form-1 deve marcar o CPF via isPropertyRequired.'
        );

        // BaseV1: form do Coletivo (tipo 2) marca CNPJ via isPropertyRequired
        $assertFileContains(
            $root . '/src/themes/BaseV1/layouts/parts/singles/agent-form-2.php',
            'isPropertyRequired($entity,"cnpj")',
            'BaseV1 agent-form-2 deve marcar o CNPJ via isPropertyRequired.'
        );

        // BaseV2 (tema default): view de edição do Individual (tipo 1) repassa
        // a exigência do CPF para o componente entity-field
        $basev2_edit1 = $root . '/src/modules/Entities/views/agent/edit-1.php';
        $assertFileContains(
            $basev2_edit1,
            'prop="cpf" :required="<?= $entity->isPropertyRequired($entity, \'cpf\')',
            'BaseV2 edit-1 deve vincular a marcação de obrigatório do CPF via isPropertyRequired.'
        );

        // o CNPJ do MEI no Individual permanece opcional: campo presente sem binding :required
        $edit1_content = file_get_contents($basev2_edit1);
        $cnpj_lines = array_values(array_filter(
            explode("\n", $edit1_content),
            fn (string $line) => str_contains($line, 'prop="cnpj"')
        ));
        $this->assertNotEmpty($cnpj_lines, 'Sanity: campo CNPJ do MEI presente no edit-1.');
        foreach ($cnpj_lines as $line) {
            $this->assertStringNotContainsString(':required', $line, 'CNPJ do MEI no Individual não deve ser marcado como obrigatório: ' . trim($line));
        }

        // BaseV2: view de edição do Coletivo (tipo 2) repassa a exigência do CNPJ
        $assertFileContains(
            $root . '/src/modules/Entities/views/agent/edit-2.php',
            'prop="cnpj" label="CNPJ" :required="<?= $entity->isPropertyRequired($entity, \'cnpj\')',
            'BaseV2 edit-2 deve vincular a marcação de obrigatório do CNPJ via isPropertyRequired.'
        );

        // componente entity-field aceita a sobrescrita da marcação via prop required
        $assertFileContains(
            $root . '/src/modules/Entities/components/entity-field/script.js',
            'required:',
            'entity-field deve expor a prop required para sobrescrever a marcação de obrigatório.'
        );
        $assertFileContains(
            $root . '/src/modules/Entities/components/entity-field/template.php',
            "required === true || (required === null && description.required)",
            'entity-field deve considerar a prop required antes da marcação da descrição.'
        );

        // Modal de criação (BaseV2): documento condicional por tipo via computed,
        // sem bloco PHP ad hoc — padrão create-agent + jsObject AgentDocuments
        $create_agent_fields = $root . '/src/modules/Entities/layouts/parts/entities/create-agent-fields.php';
        $assertFileContains(
            $create_agent_fields,
            'v-if="requiredDocumentProp"',
            'Modal create-agent deve exibir o campo de documento via requiredDocumentProp.'
        );
        $assertFileContains(
            $create_agent_fields,
            'hide-required',
            'Modal create-agent deve seguir o padrão da casa (hide-required nos campos).'
        );
        $this->assertStringNotContainsString(
            "if (\$app->config['agents.requiredDocumentsByType']",
            file_get_contents($create_agent_fields),
            'Modal create-agent não deve usar bloco PHP condicional fora do padrão da casa.'
        );

        $assertFileContains(
            $create_agent_fields,
            '@change="onAgentTypeChange"',
            'Modal create-agent deve reagir à troca de tipo no select.'
        );
        $assertFileContains(
            $create_agent_fields,
            ':key="requiredDocumentProp"',
            'Modal create-agent deve remontar o campo de documento ao trocar CPF/CNPJ.'
        );
        $assertFileContains(
            $root . '/src/modules/Entities/components/create-agent/script.js',
            'agentTypeIdForDocument',
            'create-agent deve espelhar o tipo em dado reativo (Entity.type não dispara computed).'
        );
        $assertFileContains(
            $root . '/src/modules/Entities/components/create-agent/script.js',
            'onAgentTypeChange',
            'create-agent deve sincronizar o campo de documento ao mudar o tipo.'
        );

        $assertFileContains(
            $root . '/src/modules/AgentDocuments/Module.php',
            "jsObject['config']['agentDocuments']",
            'AgentDocuments deve publicar a flag e o mapa tipo→documento no jsObject.'
        );
    }

    /**
     * Com a flag ligada, mudar o tipo do agente passa a exigir o documento do
     * novo tipo no mesmo salvamento (agente sem nenhum documento vira
     * Coletivo: passa a exigir CNPJ e não exige mais CPF).
     */
    function testFlagOnTrocaDeTipoPassaAExigirDocumentoDoNovoTipo()
    {
        $user = $this->userDirector->createUser();
        $this->login($user);

        $agent = null;
        $this->withAccessControlDisabled(function () use ($user, &$agent) {
            $agent = $this->agentDirector->createAgent($user, 1);
        });

        $this->enableFlag();

        $this->withAccessControlDisabled(function () use (&$agent) {
            $agent->type = 2;
        });

        $errors = $agent->validationErrors;
        $this->assertArrayNotHasKey('cpf', $errors, 'Novo tipo 2 não exige CPF.');
        $this->assertArrayHasKey('cnpj', $errors, 'Mudança para Coletivo passa a exigir CNPJ.');
        $this->assertContains(self::MSG_CNPJ_REQUIRED, $errors['cnpj']);
    }
}
