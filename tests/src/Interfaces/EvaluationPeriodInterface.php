<?php
namespace Tests\Interfaces;

use MapasCulturais\DateTime;
use MapasCulturais\Entities\Opportunity;

interface EvaluationPeriodInterface {
    public function getEvaluationFrom(Opportunity $phase): DateTime;
    public function getEvaluationTo(Opportunity $phase): DateTime;
}