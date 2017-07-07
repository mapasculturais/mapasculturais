<?php
namespace MapasCulturais;

abstract class EvaluationMethod extends Plugin implements \JsonSerializable{
    abstract protected function _register();

    abstract function enqueueScriptsAndStyles();

    abstract function getSlug();
    abstract function getName();
    abstract function getDescription();

    abstract protected function _getConsolidatedResult(Entities\Registration $registration);
    abstract function getEvaluationResult(Entities\RegistrationEvaluation $evaluation);

    abstract function valueToString($value);

    function evaluationToString(Entities\RegistrationEvaluation $evaluation){
        return $this->valueToString($evaluation->result);
    }
    
    function fetchRegistrations(){
        return false;
    }

    function getConsolidatedResult(Entities\Registration $registration){
        $registration->checkPermission('viewConsolidatedResult');

        return $this->_getConsolidatedResult($registration);
    }

    public function canUserEvaluateRegistration(Entities\Registration $registration, $user){
        if($user->is('guest')){
            return false;
        }
        
        $config = $registration->getEvaluationMethodConfiguration();
        
        $can = $config->canUser('@control');
        
        if($can && $this->fetchRegistrations()){
            
            $fetch = []; 
            if(!is_null($config->fetch)){
                foreach($config->fetch as $id => $val){
                    $fetch [(int)$id] = $val;
                }
            }
            if(isset($fetch[$user->id])){
                $ufetch = $fetch[$user->id];
                if(preg_match("#([0-9]+) *[-] *([0-9]+)*#", $ufetch, $matches)){
                    $s1 = $matches[1];
                    $s2 = $matches[2];
                    
                    $len = max([strlen($s1), strlen($s2)]);
                    
                    $fin = substr($registration->id, -$len);
                    
                    if(intval($s2) == 0){ // "00" => "100"
                        $s2 = "1$s2";
                    }
                    if($fin < $s1 || $fin > $s2){
                        return false;
                    }
//                }else {
//                    $vals = explode(',', $ufetch);
//                    $ok = false;
//                    foreach($vals as $v){
//                        $len = strlen($v);
//                        $fin = substr($registration->id, -$len);
//                        
//                        if($fin == $v){
//                            $ok = true;
//                        }
//                    }
//                    
//                    
//                    if(!$ok) {
//                        return false;
//                    }
                }
                
            } 
        }
        
        return $can;
    }

    function canUserViewConsolidatedResult(Entities\Registration $registration){
        $opp = $registration->opportunity;

        if($opp->publishedRegistrations || $opp->canUser('@control')){
            return true;
        } else {
            return false;
        }
    }

    function getEvaluationFormPartName(){
        $slug = $this->getSlug();

        return "$slug--evaluation-form";
    }

    function getEvaluationViewPartName(){
        $slug = $this->getSlug();

        return "$slug--evaluation-view";
    }

    function getEvaluationFormInfoPartName(){
        $slug = $this->getSlug();

        return "$slug--evaluation-info";
    }
    
    function getConfigurationFormPartName(){
        $slug = $this->getSlug();

        return "$slug--configuration-form";
    }

    function register(){
        $app = App::i();

        $def = new Definitions\EvaluationMethod($this);

        $app->registerEvaluationMethod($def);

        $type = new Definitions\EntityType('MapasCulturais\Entities\EvaluationMethodConfiguration', $this->getSlug(), $this->getName());

        $app->registerEntityType($type);

        $this->_register();

        $self = $this;

        $app->hook('view.includeAngularEntityAssets:after', function() use($self){
            $self->enqueueScriptsAndStyles();
        });
        
        if($this->fetchRegistrations()){
            $this->registerEvaluationMethodConfigurationMetadata('fetch', [
                'label' => \MapasCulturais\i::__('Configuração do fatiamento das inscrições entre os avaliadores'),
                'serialize' => function ($val) {
                    return json_encode($val);
                },
                'unserialize' => function($val) {
                    return json_decode($val);
                }
            ]);
        }

    }
    
    function registerEvaluationMethodConfigurationMetadata($key, array $config){
        $app = App::i();

        $metadata = new Definitions\Metadata($key, $config);

        $app->registerMetadata($metadata, 'MapasCulturais\Entities\EvaluationMethodConfiguration', $this->getSlug());
    }

    function usesEvaluationCommittee(){
        return true;
    }

    public function jsonSerialize() {
        return null;
    }
}
