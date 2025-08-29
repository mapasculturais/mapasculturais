<?php 
namespace Tests\Enums;

use Tests\Abstract\EvaluationMethodConfigurationBuilder;
use Tests\Builders\EvaluationMethodDocumentaryBuilder;
use Tests\Builders\EvaluationMethodQualificationBuilder;
use Tests\Builders\EvaluationMethodSimpleBuilder;
use Tests\Builders\EvaluationMethodTechnicalBuilder;
use Tests\Builders\EvaluationPhaseBuilder;
use Tests\Builders\OpportunityBuilder;

enum EvaluationMethods: string
{
    case simple = EvaluationMethodSimpleBuilder::class;
    case technical = EvaluationMethodTechnicalBuilder::class;
    case documentary = EvaluationMethodDocumentaryBuilder::class;
    case qualification = EvaluationMethodQualificationBuilder::class;
    case continuous = EvaluationMethodQualificationBuilder::class;

    function builder(EvaluationPhaseBuilder $evaluation_phase_builder, OpportunityBuilder $opportunity_builder): EvaluationMethodConfigurationBuilder
    {
        $class = $this->value;
        return new $class($evaluation_phase_builder, $opportunity_builder);
    }
}