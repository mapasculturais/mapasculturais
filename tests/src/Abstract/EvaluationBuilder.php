<?php

namespace Tests\Abstract;

use MapasCulturais\Entities\RegistrationEvaluation;
use Tests\Builders\EvaluationPhaseBuilder;

/** @property RegistrationEvaluation $instance */
abstract class EvaluationBuilder extends Builder
{
    protected RegistrationEvaluation $instance;

    function __construct(
        protected EvaluationPhaseBuilder $evaluationPhaseBuilder
    ) {
        parent::__construct();
    }

    final public function reset(RegistrationEvaluation $instance): self
    {
        $this->instance = $instance;

        return $this;
    }

    final public function getInstance(): RegistrationEvaluation
    {
        return $this->instance;
    }

    /** Não faz nada */
    public function fillRequiredProperties(): self
    {
        return $this;
    }

    public function send(bool $flush = true): self
    {
        $app = \MapasCulturais\App::i();
        $app->disableAccessControl();
        $this->instance->send($flush);
        $app->enableAccessControl();
        return $this;
    }

    final public function done(): EvaluationPhaseBuilder
    {
        return $this->evaluationPhaseBuilder;
    }
}
