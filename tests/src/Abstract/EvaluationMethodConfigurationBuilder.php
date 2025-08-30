<?php

namespace Tests\Abstract;

use MapasCulturais\App;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entity;
use Tests\Builders\EvaluationPhaseBuilder;
use Tests\Builders\OpportunityBuilder;

/** @property Entity $instance */
abstract class EvaluationMethodConfigurationBuilder extends Builder
{
    protected EvaluationMethodConfiguration $instance;

    function __construct(
        protected EvaluationPhaseBuilder $evaluationPhaseBuilder,
        protected OpportunityBuilder $opportunityBuilder
    ) {
        parent::__construct();
    }

    final public function reset(EvaluationMethodConfiguration $instance): self
    {
        $this->instance = $instance;

        return $this;
    }

    final public function getInstance(): EvaluationMethodConfiguration
    {
        return $this->instance;
    }

    /** Não faz nada */
    public function fillRequiredProperties(): self
    {
        return $this;
    }

    final public function done(): EvaluationPhaseBuilder
    {
        return $this->evaluationPhaseBuilder;
    }
}
