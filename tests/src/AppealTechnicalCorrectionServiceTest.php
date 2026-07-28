<?php

namespace Tests;

use OpportunityAppealPhase\Services\AppealTechnicalCorrectionService;

class AppealTechnicalCorrectionServiceTest extends Abstract\TestCase
{
    public function testServiceDoesNotExceedAuthorizedMethodCount(): void
    {
        $reflection = new \ReflectionClass(AppealTechnicalCorrectionService::class);
        $declaredMethods = array_filter(
            $reflection->getMethods(),
            fn(\ReflectionMethod $method) => $method->getDeclaringClass()->getName() === $reflection->getName()
        );

        $this->assertLessThanOrEqual(20, count($declaredMethods));
    }
}
