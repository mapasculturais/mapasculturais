<?php

namespace Tests;

use MapasCulturais\Entities\Seal;
use MapasCulturais\Exceptions\BadRequest;
use Tests\Abstract\TestCase;
use Tests\Traits\RequestFactory;
use Tests\Traits\SealDirector;
use Tests\Traits\UserDirector;

/**
 * Backend validation tests for Seal entity properties and lockedFieldsConfig.
 */
class SealValidationTest extends TestCase
{
    use UserDirector, SealDirector, RequestFactory;

    protected function createDraftSeal(): Seal
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $seal = $this->sealDirector->createSeal($admin->profile, false, false);
        $seal->validPeriod = 0;

        return $seal;
    }

    public function testEmptyNameFailsValidation(): void
    {
        $seal = $this->createDraftSeal();
        $seal->name = '';
        $seal->shortDescription = 'Descrição válida';

        $errors = $seal->getValidationErrors();

        $this->assertArrayHasKey('name', $errors);
    }

    public function testShortDescriptionAbove400CharsFailsValidation(): void
    {
        $seal = $this->createDraftSeal();
        $seal->name = 'Selo válido';
        $seal->shortDescription = str_repeat('a', 401);

        $errors = $seal->getValidationErrors();

        $this->assertArrayHasKey('shortDescription', $errors);
    }

    public function testNegativeValidPeriodFailsValidation(): void
    {
        $seal = $this->createDraftSeal();
        $seal->name = 'Selo válido';
        $seal->shortDescription = 'Descrição válida';
        $seal->validPeriod = -1;

        $errors = $seal->getValidationErrors();

        $this->assertArrayHasKey('validPeriod', $errors);
    }

    public function testLockedFieldsConfigInvalidatorWithoutExpiryThrowsBadRequest(): void
    {
        $seal = $this->createDraftSeal();

        $this->expectException(BadRequest::class);

        $seal->lockedFieldsConfig = [
            'agent.name' => ['hasExpiry' => false, 'isInvalidator' => true],
        ];
    }

    public function testLockedFieldsConfigZeroPeriodValueThrowsBadRequest(): void
    {
        $seal = $this->createDraftSeal();

        $this->expectException(BadRequest::class);

        $seal->lockedFieldsConfig = [
            'agent.name' => ['hasExpiry' => true, 'periodValue' => 0, 'periodUnit' => 'year'],
        ];
    }

    public function testLockedFieldsConfigInvalidPeriodUnitThrowsBadRequest(): void
    {
        $seal = $this->createDraftSeal();

        $this->expectException(BadRequest::class);

        $seal->lockedFieldsConfig = [
            'agent.name' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'decade'],
        ];
    }

    public function testLockedFieldsConfigWithoutExpiryCleansExpiryRelatedKeys(): void
    {
        $seal = $this->createDraftSeal();
        $seal->name = 'Selo válido';
        $seal->shortDescription = 'Descrição válida';

        $seal->lockedFieldsConfig = [
            'agent.name' => ['hasExpiry' => false, 'periodValue' => 10, 'periodUnit' => 'year'],
        ];

        $config = $seal->lockedFieldsConfig;

        $this->assertArrayHasKey('agent.name', $config);
        $this->assertSame(['hasExpiry' => false], $config['agent.name']);
        $this->assertSame(['agent.name'], $seal->lockedFields);
    }

    public function testLockedFieldsConfigSynchronizesLegacyLockedFields(): void
    {
        $seal = $this->createDraftSeal();
        $seal->name = 'Selo válido';
        $seal->shortDescription = 'Descrição válida';

        $seal->lockedFieldsConfig = [
            'agent.name' => ['hasExpiry' => true, 'periodValue' => 2, 'periodUnit' => 'month', 'isInvalidator' => false],
            'space.name' => ['hasExpiry' => false],
        ];

        $this->assertSame(['agent.name', 'space.name'], $seal->lockedFields);
    }

    public function testLockedFieldsConfigPrevailsOverLockedFields(): void
    {
        $seal = $this->createDraftSeal();
        $seal->name = 'Selo válido';
        $seal->shortDescription = 'Descrição válida';

        // Simula um payload que envia ambos: primeiro a configuração granular
        $seal->lockedFieldsConfig = [
            'agent.name' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => false],
        ];

        // Depois o array legado (ordem inversa também deve ser coberta, mas aqui
        // testamos o cenário crítico em que lockedFields é atribuído depois).
        $seal->lockedFields = ['agent.name', 'space.name'];

        $config = $seal->lockedFieldsConfig;

        $this->assertArrayHasKey('agent.name', $config);
        $this->assertTrue($config['agent.name']['hasExpiry']);
        $this->assertSame(1, $config['agent.name']['periodValue']);
        $this->assertSame('year', $config['agent.name']['periodUnit']);
        $this->assertArrayNotHasKey('space.name', $config);
        $this->assertSame(['agent.name'], $seal->lockedFields);
    }

    public function testLockedFieldsConfigPrevailsWhenLockedFieldsAssignedFirst(): void
    {
        $seal = $this->createDraftSeal();
        $seal->name = 'Selo válido';
        $seal->shortDescription = 'Descrição válida';

        // lockedFields atribuído antes de lockedFieldsConfig
        $seal->lockedFields = ['agent.name', 'space.name'];
        $seal->lockedFieldsConfig = [
            'agent.name' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year'],
        ];

        $this->assertSame(['agent.name'], $seal->lockedFields);
    }

    public function testLockedFieldsConfigRejectsEmptyFieldKey(): void
    {
        $seal = $this->createDraftSeal();

        $this->expectException(BadRequest::class);

        $seal->lockedFieldsConfig = [
            '' => ['hasExpiry' => false],
        ];
    }

    public function testLockedFieldsConfigRejectsNumericFieldKey(): void
    {
        $seal = $this->createDraftSeal();

        $this->expectException(BadRequest::class);

        $seal->lockedFieldsConfig = [
            0 => ['hasExpiry' => false],
        ];
    }

    public function testLockedFieldsConfigStringFalseHasExpiryIsFalse(): void
    {
        $seal = $this->createDraftSeal();
        $seal->name = 'Selo válido';
        $seal->shortDescription = 'Descrição válida';

        $seal->lockedFieldsConfig = [
            'agent.name' => ['hasExpiry' => 'false', 'periodValue' => 10, 'periodUnit' => 'year'],
        ];

        $config = $seal->lockedFieldsConfig;

        $this->assertArrayHasKey('agent.name', $config);
        $this->assertFalse($config['agent.name']['hasExpiry']);
        $this->assertSame(['hasExpiry' => false], $config['agent.name']);
    }

    public function testLockedFieldsConfigNonNumericPeriodValueThrowsBadRequest(): void
    {
        $seal = $this->createDraftSeal();

        $this->expectException(BadRequest::class);

        $seal->lockedFieldsConfig = [
            'agent.name' => ['hasExpiry' => true, 'periodValue' => 'abc', 'periodUnit' => 'year'],
        ];
    }

    public function testLockedFieldsConfigFloatPeriodValueThrowsBadRequest(): void
    {
        $seal = $this->createDraftSeal();

        $this->expectException(BadRequest::class);

        $seal->lockedFieldsConfig = [
            'agent.name' => ['hasExpiry' => true, 'periodValue' => 1.5, 'periodUnit' => 'year'],
        ];
    }

    public function testLockedFieldsConfigHttpPatchReturns400OnInvalidConfig(): void
    {
        $seal = $this->createDraftSeal();
        $seal->name = 'Selo válido';
        $seal->shortDescription = 'Descrição válida';
        $seal->save();

        $request = $this->requestFactory->PATCH_entity($seal, [
            'lockedFieldsConfig' => [
                'agent.name' => ['hasExpiry' => true, 'periodValue' => 'abc', 'periodUnit' => 'year'],
            ],
        ]);

        $this->assertStatus400($request, 'PATCH com periodValue não numérico deve retornar HTTP 400');
    }

    public function testLegacyLockedFieldsStillSynchronizesConfig(): void
    {
        $seal = $this->createDraftSeal();
        $seal->name = 'Selo válido';
        $seal->shortDescription = 'Descrição válida';

        $seal->lockedFields = ['agent.name'];

        $config = $seal->lockedFieldsConfig;

        $this->assertArrayHasKey('agent.name', $config);
        $this->assertFalse($config['agent.name']['hasExpiry']);
        $this->assertNull($config['agent.name']['periodValue']);
        $this->assertNull($config['agent.name']['periodUnit']);
        $this->assertFalse($config['agent.name']['isInvalidator']);
    }
}
