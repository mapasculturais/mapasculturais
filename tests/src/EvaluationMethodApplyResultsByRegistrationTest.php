<?php

namespace Tests;

use MapasCulturais\Entities\Registration;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;

class EvaluationMethodApplyResultsByRegistrationTest extends TestCase
{
    use OpportunityBuilder,
        RegistrationDirector,
        RequestFactory,
        UserDirector;

    public static function evaluationMethods(): array
    {
        return [
            'simplificada' => [EvaluationMethods::simple, 'simple', 'applyEvaluationsSimple'],
            'documental' => [EvaluationMethods::documentary, 'documentary', 'applyEvaluationsDocumentary'],
            'contínua' => [EvaluationMethods::simple, 'continuous', 'applyEvaluationsContinuous'],
        ];
    }

    #[DataProvider('evaluationMethods')]
    public function testApplyResultsByRegistration(
        EvaluationMethods $evaluation_method,
        string $type,
        string $action
    ): void {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->addEvaluationPhase($evaluation_method)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->save()
                ->done()
            ->getInstance();

        if ($type === 'continuous') {
            $opportunity->evaluationMethodConfiguration->type = $type;
            $opportunity->evaluationMethodConfiguration->save(true);
        }

        $selected = $this->registrationDirector->createSentRegistration($opportunity, data: []);
        $not_selected = $this->registrationDirector->createSentRegistration($opportunity, data: []);

        $request = $this->requestFactory->POST(
            controller_id: 'opportunity',
            action: $action,
            url_params: [$opportunity->id],
            payload: [
                'tabSelected' => 'registration',
                'registrationNumbers' => [$selected->number, 'numero-inexistente'],
                'to' => Registration::STATUS_APPROVED,
            ]
        );

        $this->assertStatus200($request);
        $this->assertSame(Registration::STATUS_APPROVED, $selected->refreshed()->status);
        $this->assertSame(Registration::STATUS_SENT, $not_selected->refreshed()->status);
    }
}
