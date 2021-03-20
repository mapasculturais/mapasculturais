<?php
namespace MapasCulturais\Definitions;

use MapasCulturais\App;
use MapasCulturais\Entities;

/**
 * This class defines an Evaluation Method
 *
 * @property-read string $name 
 * @property-read string $slug 
 * @property-read string $description 
 * @property-read bool $internal 
 * 
 * @property-read string $evaluationMethodClassName 
 * @property-read MapasCulturais\EvaluationMethod $evaluationMethod 
 */
class EvaluationMethod extends \MapasCulturais\Definition {

    /**
     * The Evaluation Method
     * @var \MapasCuturais\EvaluationMethod
     */
    protected $evaluationMethod;
    protected $evaluationMethodClassName;
    
    protected $slug;
    protected $name;
    protected $description;

    protected $internal = false;
    
    public function __construct(\MapasCulturais\EvaluationMethod $evaluation_method) {
        $this->evaluationMethod = $evaluation_method;
        $this->evaluationMethodClassName = get_class($evaluation_method);
        
        $this->slug = $evaluation_method->getSlug();
        $this->name = $evaluation_method->getName();
        $this->description = $evaluation_method->getDescription();

        $this->internal = $evaluation_method->internal ?? false;
    }
}
