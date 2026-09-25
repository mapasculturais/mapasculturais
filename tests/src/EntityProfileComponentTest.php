<?php

namespace Test;

use Tests\Abstract\TestCase;

class EntityProfileComponentTest extends TestCase
{
    public function testRequiredAvatarConfigurationUsesComponentEntityType(): void
    {
        $sourceRoot = realpath(__DIR__ . '/../../src') ?: realpath(__DIR__ . '/../src');
        $init = file_get_contents($sourceRoot . '/modules/Entities/components/entity-profile/init.php');
        $script = file_get_contents($sourceRoot . '/modules/Entities/components/entity-profile/script.js');

        $this->assertStringContainsString("['requiredAvatar'] ?? []", $init);
        $this->assertStringContainsString('requiredAvatarByEntityType', $init);
        $this->assertStringContainsString('config[this.entity.__objectType]', $script);
    }
}
