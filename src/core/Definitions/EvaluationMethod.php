<?php
namespace MapasCulturais\Definitions;

use MapasCulturais\App;
use MapasCulturais\Entities;

/**
 * Esta classe define um Método de Avaliação
 *
 * @property-read string $name Nome do método de avaliação
 * @property-read string $slug Slug do método de avaliação
 * @property-read string $description Descrição do método de avaliação
 * @property-read bool $internal Indica se é um método interno
 * 
 * @property-read string $evaluationMethodClassName Nome da classe do método de avaliação
 * @property-read \MapasCulturais\EvaluationMethod $evaluationMethod Instância do método de avaliação
 */
class EvaluationMethod extends \MapasCulturais\Definition {

    /**
     * Instância do método de avaliação
     * @var \MapasCulturais\EvaluationMethod
     */
    public $evaluationMethod;
    
    /**
     * Nome da classe do método de avaliação
     * @var string
     */
    public $evaluationMethodClassName;
    
    /**
     * Slug do método de avaliação
     * @var string
     */
    public $slug;
    
    /**
     * Nome do método de avaliação
     * @var string
     */
    public $name;
    
    /**
     * Descrição do método de avaliação
     * @var string
     */
    public $description;

    /**
     * Indica se é um método interno
     * @var bool
     */
    public $internal = false;
    
    /**
     * Construtor da classe
     * 
     * @param \MapasCulturais\EvaluationMethod $evaluation_method Instância do método de avaliação
     */
    public function __construct(\MapasCulturais\EvaluationMethod $evaluation_method) {
        $this->evaluationMethod = $evaluation_method;
        $this->evaluationMethodClassName = get_class($evaluation_method);
        
        $this->slug = $evaluation_method->getSlug();
        $this->name = $evaluation_method->getName();
        $this->description = $evaluation_method->getDescription();

        $this->internal = $evaluation_method->internal ?? false;
    }

    /**
     * Serializa o objeto para JSON
     * 
     * @return array
     */
    function jsonSerialize(): array {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
        ];
    }
}
