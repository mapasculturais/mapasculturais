<?php

class OpportunityWorkplanMonitoringTemplateTest extends \PHPUnit\Framework\TestCase
{
    public function testMonitoringDeliveryTemplateUsesNormalizedPlannedValues(): void
    {
        $template = file_get_contents(__DIR__ . '/../../src/modules/OpportunityWorkplan/components/registration-workplan-form-delivery/template.php');
        $script = file_get_contents(__DIR__ . '/../../src/modules/OpportunityWorkplan/components/registration-workplan-form-delivery/script.js');

        $this->assertStringContainsString('plannedRevenueType', $template);
        $this->assertStringContainsString('plannedPaidStaffByRole', $template);
        $this->assertStringContainsString('plannedTeamCompositionGender', $template);
        $this->assertStringContainsString('plannedTeamCompositionRace', $template);
        $this->assertStringContainsString('plannedCommunicationChannels', $template);
        $this->assertStringContainsString('plannedExpectedAccessibilityMeasures', $template);
        $this->assertStringContainsString('plannedInnovationTypes', $template);
        $this->assertStringContainsString('plannedDocumentationTypes', $template);

        $this->assertStringContainsString('plannedRevenueType ()', $script);
        $this->assertStringContainsString('plannedPaidStaffByRole ()', $script);
        $this->assertStringContainsString('plannedTeamCompositionGender ()', $script);
        $this->assertStringContainsString('plannedTeamCompositionRace ()', $script);
        $this->assertStringContainsString('plannedCommunicationChannels ()', $script);
        $this->assertStringContainsString('plannedExpectedAccessibilityMeasures ()', $script);
        $this->assertStringContainsString('plannedInnovationTypes ()', $script);
        $this->assertStringContainsString('plannedDocumentationTypes ()', $script);
    }
}
