<?php

namespace MapasCulturais\Definitions;

use Closure;
use InvalidArgumentException;
use MapasCulturais\App;
use MapasCulturais\Entities\Job;

/**
 * Classe abstrata que define um Tipo de Job
 * 
 * @property-read string $slug Identificador único do tipo de job
 * @property-read Closure $handleFunction Função de execução do job
 * @property-read Closure $idGeneratorFunction Função de geração de ID do job
 * 
 * @package MapasCulturais\Definitions
 */
abstract class JobType extends \MapasCulturais\Definition {
    
    /**
     * Identificador único do tipo de job
     * @var string
     */
    public $slug;

    /**
     * Construtor da classe
     * 
     * @param string $slug Identificador único do tipo de job
     */
    function __construct(string $slug) {
        $this->slug = $slug;
    }

    /**
     * Gera um ID único para o job
     * 
     * @param array $data Dados do job
     * @param string $start_string String de início do agendamento
     * @param string $interval_string String de intervalo do agendamento
     * @param int $iterations Número de iterações
     * @return string ID único gerado
     */
    function generateId(array $data, string $start_string, string $interval_string, int $iterations) {
        $id = $this->_generateId($data, $start_string, $interval_string, $iterations);

        return md5("{$this->slug}:{$id}");
    }

    /**
     * Executa o job
     * 
     * @param Job $job Instância do job a ser executado
     * @return bool Resultado da execução
     */
    function execute(Job $job) {
        $app = App::i();

        $app->applyHookBoundTo($job, "job({$this->slug}).execute:before");
        
        $result = $this->_execute($job);

        $app->applyHookBoundTo($job, "job({$this->slug}).execute:after", [&$result]);

        return $result;
    }

    /**
     * Método abstrato para geração de ID do job
     * 
     * @param array $data Dados do job
     * @param string $start_string String de início do agendamento
     * @param string $interval_string String de intervalo do agendamento
     * @param int $iterations Número de iterações
     * @return string ID único gerado
     */
    abstract protected function _generateId(array $data, string $start_string, string $interval_string, int $iterations);


    /**
     * Método abstrato para execução do job
     * 
     * @param Job $job Instância do job a ser executado
     * @return bool Resultado da execução
     */
    abstract protected function _execute(Job $job);

}