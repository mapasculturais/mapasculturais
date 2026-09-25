<?php

class OpportunityWorkplanSubmitSaveTest extends \PHPUnit\Framework\TestCase
{
    public function testRegistrationSaveWaitsForWorkplanWithoutDedicatedButton(): void
    {
        $sourceRoot = realpath(__DIR__ . '/../../src') ?: realpath(__DIR__ . '/../src');

        $registrationActions = file_get_contents(
            $sourceRoot . '/modules/Opportunities/components/registration-actions/script.js'
        );
        $workplan = file_get_contents(
            $sourceRoot . '/modules/OpportunityWorkplan/components/registration-workplan/script.js'
        );
        $template = file_get_contents(
            $sourceRoot . '/modules/OpportunityWorkplan/components/registration-workplan/template.php'
        );

        $this->assertStringContainsString('await this.saveRelatedFormData();', $registrationActions);
        $this->assertStringContainsString("new CustomEvent('registration.beforeSave'", $registrationActions);
        $this->assertStringContainsString("addEventListener('registration.beforeSave'", $workplan);
        $this->assertStringContainsString('event.detail.promises.push(this.save_(false, false));', $workplan);
        $this->assertStringContainsString('const response = await api.POST(`save`, data);', $workplan);
        $this->assertStringNotContainsString('button-registration-workplan__save-goal', $template);
        $this->assertStringNotContainsString('button-registration-workplan__save-goal', $workplan);
    }
}
