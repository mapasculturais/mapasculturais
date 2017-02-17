<?php

namespace MapasCulturais\Definitions;

/**
 * This class defines an Opportunity Evaluation Method
 *
 */
class OpportunityEvaluationMethod extends \MapasCulturais\Definition {

    protected $_config;
    protected $slug;
    protected $evaluationMethodClassName;
    protected $name;
    protected $description;
    
    public function __construct($slug, $evaluation_method_class_name, $name, $description) {
        $this->slug = $slug;
        $this->evaluationMethodClassName = $evaluation_method_class_name;
        $this->name = $name;
        $this->description;
    }
}
