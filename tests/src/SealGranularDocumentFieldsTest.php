<?php

namespace Tests;

use DateTime;
use DateTimeZone;
use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Seal;
use MapasCulturais\Exceptions\PermissionDenied;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Abstract\TestCase;
use Tests\Traits\AgentDirector;
use Tests\Traits\SealDirector;
use Tests\Traits\UserDirector;

/**
 * Critério de Sucesso #4 — "Campos novos são definíveis nas configurações de
 * Selos Granulares" (controle campo-a-campo via selo, com lockedFieldsConfig).
 *
 * Padrão retirado de SealFieldLockingTest: cria um selo com
 * lockedFieldsConfig apontando para 'agent.{campo}', vincula ao agente,
 * marca o campo como válido (não-expirado) e afirma que o dono do agente
 * recebe PermissionDenied ao tentar modificar o campo travado.
 *
 * A chave de lockedFieldsConfig segue o padrão 'agent.{propriedade}' e é
 * casada em runtime por EntitySealRelation::getLockedFieldSeals() via
 * preg_match("#agent\.(.*)#"). Logo, qualquer metadado novo — mesmo sem
 * available_for_opportunities — é controlável por selo (ver spec §Arquitetura).
 *
 * O data provider cobre três categorias representativas dos novos campos:
 *  - um anexo de dado sensível (racaAnexo);
 *  - um dado de documento (passaporteNumero) + seu anexo (passaporteAnexo);
 *  - uma certidão pura (certidaoFiscalAnexo).
 */
class SealGranularDocumentFieldsTest extends TestCase
{
    use UserDirector, AgentDirector, SealDirector;

    /**
     * Campos representativos por categoria. O segundo elemento é um valor
     * de exemplo usado na tentativa de escrita (para anexos usamos uma string
     * qualquer — o que importa é que o setter dispare a checagem de bloqueio).
     */
    public static function granularLockedFields(): array
    {
        return [
            'racaAnexo (anexo sensível)'   => ['racaAnexo', 'file-token-1'],
            'passaporteNumero (dado)'      => ['passaporteNumero', 'XX123456'],
            'passaporteAnexo (anexo doc)'  => ['passaporteAnexo', 'file-token-2'],
            'certidaoFiscalAnexo (certidão)' => ['certidaoFiscalAnexo', 'file-token-3'],
        ];
    }

    /**
     * Cria um agente com um selo cujo lockedFieldsConfig trava o campo
     * informado, e marca a relação como válida (não-expirada).
     *
     * @return array{0:Seal,1:Agent}
     */
    protected function createAgentWithLockedField(string $fieldName): array
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $seal = $this->sealDirector->createSeal($admin->profile);
        $seal->validPeriod = 0;
        $seal->lockedFieldsConfig = [
            "agent.{$fieldName}" => [
                'hasExpiry' => true,
                'periodValue' => 1,
                'periodUnit' => 'year',
                'isInvalidator' => false,
            ],
        ];
        $seal->save(true);

        $owner = $this->userDirector->createUser();
        $agent = $owner->profile;
        $agent->createSealRelation($seal, true, true, $admin->profile);

        // Marca o campo da relação como válido (futuro) para que ele bloqueie.
        $this->setRelationFieldDate($agent, $seal, "agent.{$fieldName}", '+30 days');

        $agent = $agent->refreshed();

        return [$seal, $agent];
    }

    /**
     * Define a data de validade de um SealRelationField diretamente,
     * reproduzindo o helper de SealFieldLockingTest::setFieldDate().
     */
    protected function setRelationFieldDate(Agent $agent, Seal $seal, string $fieldKey, ?string $dateSpec): void
    {
        $app = App::i();
        foreach ($agent->getSealRelations() as $relation) {
            if ($relation->seal->id !== $seal->id) {
                continue;
            }
            foreach ($relation->getSealRelationFields() as $field) {
                if ($field->fieldName === $fieldKey) {
                    $field->expiryDate = $dateSpec === null
                        ? null
                        : new DateTime($dateSpec, new DateTimeZone('UTC'));
                    $app->em->persist($field);
                    $app->em->flush();
                    return;
                }
            }
        }

        $this->fail("Field '{$fieldKey}' não encontrado na relação do selo {$seal->id}.");
    }

    /**
     * Campo válido (selo dentro do prazo) deve bloquear a edição pelo dono
     * do agente. Este é o comportamento central dos Selos Granulares.
     */
    #[DataProvider('granularLockedFields')]
    public function testValidFieldIsLockedForOwner(string $fieldName, string $attemptValue): void
    {
        [$seal, $agent] = $this->createAgentWithLockedField($fieldName);

        $owner = $agent->owner->user;
        $this->login($owner);

        $this->expectException(PermissionDenied::class);

        $agent->$fieldName = $attemptValue . '-' . uniqid();
        $agent->save(true);
    }

    /**
     * Um campo expirado deixa de bloquear — o dono pode editar. Esta é a
     * contraprova: confirma que o bloqueio observado acima decorre do status
     * do selo e não de um PermissionDenied espúrio (permissão de entidade).
     */
    #[DataProvider('granularLockedFields')]
    public function testExpiredFieldIsUnlockedForOwner(string $fieldName, string $attemptValue): void
    {
        [$seal, $agent] = $this->createAgentWithLockedField($fieldName);

        // Expira o campo e recarrega.
        $this->setRelationFieldDate($agent, $seal, "agent.{$fieldName}", '-1 day');
        $agent = $agent->refreshed();

        $owner = $agent->owner->user;
        $this->login($owner);

        $newValue = $attemptValue . '-' . uniqid();
        $agent->$fieldName = $newValue;
        $agent->save(true);
        $agent = $agent->refreshed();

        $this->assertSame(
            $newValue,
            $agent->$fieldName,
            "Campo expirado '{$fieldName}' deveria estar desbloqueado para o dono."
        );
    }

    /**
     * Admin continua podendo editar o campo mesmo travado por selo.
     */
    #[DataProvider('granularLockedFields')]
    public function testAdminCanEditLockedField(string $fieldName, string $attemptValue): void
    {
        [$seal, $agent] = $this->createAgentWithLockedField($fieldName);

        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $newValue = $attemptValue . '-' . uniqid();
        $agent->$fieldName = $newValue;
        $agent->save(true);
        $agent = $agent->refreshed();

        $this->assertSame(
            $newValue,
            $agent->$fieldName,
            "Admin deveria conseguir editar o campo travado '{$fieldName}'."
        );
    }
}
