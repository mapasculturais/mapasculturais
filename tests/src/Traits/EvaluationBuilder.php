<?php

namespace Tests\Traits;

use Tests\Builders;

trait EvaluationBuilder {
    protected Builders\EvaluationBuilder $evaluationBuilder;

    function __initEvaluationBuilder() {
        $this->evaluationBuilder = new Builders\EvaluationBuilder;
    }
}