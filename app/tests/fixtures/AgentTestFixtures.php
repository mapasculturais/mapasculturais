<?php

declare(strict_types=1);

namespace App\Tests\fixtures;

final class AgentTestFixtures extends AbstractTestFixtures implements TestFixtures
{
    public static function partial(): self
    {
        return new self([
            'name' => 'Agent Test',
            'shortDescription' => 'A test agent.',
            'longDescription' => 'A test agent for the Mapas Culturais platform.',
            'site' => 'https://mapasculturais.org',
            'telefonePublico' => '(85) 99999-9999',
            'emailPublico' => 'agent@gmail.com',
            'type' => 2,
            'escolaridade' => 'Superior Completo',
            'instagram' => 'agent',
            'linkedin' => 'agent',
            'twitter' => 'agent',
            'vimeo' => 'agent',
            'youtube' => 'agent',
            'spotify' => 'agent',
            'pinterest' => 'agent',
        ]);
    }

    public static function complete(): array
    {
        return [];
    }
}
