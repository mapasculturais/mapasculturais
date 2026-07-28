<?php

namespace Tests;

class AppealTechnicalCorrectionSchemaTest extends Abstract\TestCase
{
    /**
     * @dataProvider auditedRegistrationForeignKeys
     */
    public function testAuditedRegistrationsCannotDeleteCorrectionHistory(string $column): void
    {
        $definition = $this->app->conn->fetchOne(
            <<<'SQL'
                SELECT pg_get_constraintdef(constraint_row.oid)
                FROM pg_constraint constraint_row
                JOIN pg_attribute column_row
                  ON column_row.attrelid = constraint_row.conrelid
                 AND column_row.attnum = ANY(constraint_row.conkey)
                WHERE constraint_row.conrelid = 'appeal_technical_correction'::regclass
                  AND constraint_row.contype = 'f'
                  AND column_row.attname = :column
                SQL,
            ['column' => $column]
        );

        $this->assertIsString($definition);
        $this->assertStringContainsString('ON DELETE RESTRICT', $definition);
    }

    public static function auditedRegistrationForeignKeys(): array
    {
        return [
            'inscrição do recurso' => ['appeal_registration_id'],
            'inscrição de origem' => ['source_registration_id'],
        ];
    }

    /**
     * @dataProvider auditForeignKeyIndexes
     */
    public function testAuditForeignKeyColumnsAreIndexed(string $table, string $index, string $column): void
    {
        $definition = $this->app->conn->fetchOne(
            <<<'SQL'
                SELECT indexdef
                FROM pg_indexes
                WHERE schemaname = current_schema()
                  AND tablename = :table
                  AND indexname = :index
                SQL,
            ['table' => $table, 'index' => $index]
        );

        $this->assertIsString($definition);
        $this->assertStringContainsString("({$column})", $definition);
    }

    public static function auditForeignKeyIndexes(): array
    {
        return [
            'avaliação do recurso' => [
                'appeal_technical_correction',
                'appeal_technical_correction_appeal_evaluation_idx',
                'appeal_evaluation_id',
            ],
            'relator' => [
                'appeal_technical_correction',
                'appeal_technical_correction_relator_idx',
                'relator_user_id',
            ],
            'avaliador original' => [
                'appeal_technical_correction_item',
                'appeal_technical_correction_item_original_valuer_idx',
                'original_valuer_user_id',
            ],
        ];
    }

    /**
     * @dataProvider auditTables
     */
    public function testSequenceGeneratedIdsDoNotHaveDatabaseDefaults(string $table): void
    {
        $default = $this->app->conn->fetchOne(
            <<<'SQL'
                SELECT column_default
                FROM information_schema.columns
                WHERE table_schema = current_schema()
                  AND table_name = :table
                  AND column_name = 'id'
                SQL,
            ['table' => $table]
        );

        $this->assertNull($default);
    }

    public static function auditTables(): array
    {
        return [
            'correção' => ['appeal_technical_correction'],
            'item da correção' => ['appeal_technical_correction_item'],
        ];
    }

    /**
     * @dataProvider ormManagedDefaults
     */
    public function testOrmManagedValuesDoNotHaveDatabaseDefaults(string $column): void
    {
        $default = $this->app->conn->fetchOne(
            <<<'SQL'
                SELECT column_default
                FROM information_schema.columns
                WHERE table_schema = current_schema()
                  AND table_name = 'appeal_technical_correction'
                  AND column_name = :column
                SQL,
            ['column' => $column]
        );

        $this->assertNull($default);
    }

    public static function ormManagedDefaults(): array
    {
        return [
            'status' => ['status'],
            'motivo' => ['reason'],
            'configuração dos critérios' => ['criteria_configuration_snapshot'],
        ];
    }

    /**
     * @dataProvider jsonAuditFields
     */
    public function testJsonAuditFieldsAreMappedAsJsonb(string $class, string $field, string $column): void
    {
        $mapping = $this->app->em->getClassMetadata($class)->getFieldMapping($field);
        $dataType = $this->app->conn->fetchOne(
            <<<'SQL'
                SELECT data_type
                FROM information_schema.columns
                WHERE table_schema = current_schema()
                  AND table_name = :table
                  AND column_name = :column
                SQL,
            [
                'table' => $this->app->em->getClassMetadata($class)->getTableName(),
                'column' => $column,
            ]
        );

        $this->assertSame('jsonb', $dataType);
        $this->assertTrue($mapping['options']['jsonb'] ?? false);
    }

    public static function jsonAuditFields(): array
    {
        return [
            'configuração dos critérios' => [
                \OpportunityAppealPhase\Entities\AppealTechnicalCorrection::class,
                'criteriaConfigurationSnapshot',
                'criteria_configuration_snapshot',
            ],
            'avaliação anterior' => [
                \OpportunityAppealPhase\Entities\AppealTechnicalCorrectionItem::class,
                'beforeEvaluationData',
                'before_evaluation_data',
            ],
            'avaliação posterior' => [
                \OpportunityAppealPhase\Entities\AppealTechnicalCorrectionItem::class,
                'afterEvaluationData',
                'after_evaluation_data',
            ],
            'critérios alterados' => [
                \OpportunityAppealPhase\Entities\AppealTechnicalCorrectionItem::class,
                'changedCriteria',
                'changed_criteria',
            ],
        ];
    }

    public function testOrmDeclaresTheActiveDraftPartialUniqueIndex(): void
    {
        $metadata = $this->app->em->getClassMetadata(
            \OpportunityAppealPhase\Entities\AppealTechnicalCorrection::class
        );
        $constraint = $metadata->table['uniqueConstraints']['appeal_technical_correction_active_draft_uidx'] ?? null;
        $definition = $this->app->conn->fetchOne(
            <<<'SQL'
                SELECT indexdef
                FROM pg_indexes
                WHERE schemaname = current_schema()
                  AND tablename = 'appeal_technical_correction'
                  AND indexname = 'appeal_technical_correction_active_draft_uidx'
                SQL
        );

        $this->assertIsArray($constraint);
        $this->assertSame(['appeal_registration_id'], $constraint['columns']);
        $this->assertSame('(status = 0)', $constraint['options']['where'] ?? null);
        $this->assertIsString($definition);
        $this->assertStringContainsString('UNIQUE INDEX', $definition);
        $this->assertStringContainsString('WHERE (status = 0)', $definition);
    }
}
