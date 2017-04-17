<?php

namespace MapasCulturais\Controllers;

use MapasCulturais\App;
use MapasCulturais\Traits;
use MapasCulturais\Entities;

/**
 * Seal Controller
 *
 * By default this controller is registered with the id 'seal'.
 *
 *  @property-read \MapasCulturais\Entities\Seal $requestedEntity The Requested Entity
 *
 */
class Seal extends EntityController {
    use
    	Traits\ControllerUploads,
    	Traits\ControllerTypes,
        Traits\ControllerMetaLists,
        Traits\ControllerAgentRelation,
        Traits\ControllerSoftDelete,
        Traits\ControllerDraft,
        Traits\ControllerArchive,
        Traits\ControllerAPI;

	/**
     * Creates a new Seal
     *
     * This action requires authentication and outputs the json with the new event or with an array of errors.
     *
     * <code>
     * // creates the url to this action
     * $url = $app->createUrl(seal');
     * </code>
     */
    public function POST_index($data = null) {
        $app = App::i();

        $app->hook('entity(seal).insert:before', function() use($app) {
            $this->owner = $app->user->profile;
        });
        parent::POST_index($data);
    }

    public function GET_sealRelation(){
    	$app = App::i();
    	$id = $this->data['id'];
    	$relation = $app->repo('SealRelation')->find($id);
        $expirationDate = $this->VerifySealValidity($relation);
        $mensagemPrintSealRelation = $this->PrintSealRelation($relation, $app, $expirationDate['date']);
        
    	$this->render('sealrelation', ['relation'=>$relation, 'expirationDate'=>$expirationDate, 'printSeal'=>$mensagemPrintSealRelation]);
    }

    public function GET_printSealRelation(){
        $app = App::i();

    	$id = $this->data['id'];
    	$rel = $app->repo('SealRelation')->find($id);
        
        if(!$rel){
            $app->pass();
        }

        $rel->checkPermission('print');
        
    	$this->render('printsealrelation', ['relation' => $rel]);

    }

    /**
     * Verifica a validade do selo a ser exibido
     * 
     * @param [entity] $relation - entity com a relacao doador/receptor do selo
     * @return Array or Boolean
     */
    private function VerifySealValidity($relation){
        if($relation->seal->validPeriod > 0){
            $today = new \DateTime();
            $expirationDate = date_add($relation->seal->createTimestamp, date_interval_create_from_date_string($relation->seal->validPeriod . " months"));
            $expired = $expirationDate < $today;
            $date = array('expired'=>$expired, 'date'=>$expirationDate);

            return $date;
        }        

        return false;
    }

    /**
     * Retorna a mensagem de impressão do certificado
     *
     * @param entity $relation
     * @param entity $app
     * @param expirationDate - obj com info da data de expiração do selo
     * @return mensagem de impressão ou falso
     */
    private function PrintSealRelation($relation, $app, $expirationDate){
        $mensagem = $relation->seal->certificateText;
        $entity = $relation->seal;
        $dateInicio = $relation->createTimestamp->format("d/m/Y");
        $seloExpira = isset($expirationDate);
        
        if($seloExpira){
            $dateFim = $expirationDate->format('d-m-Y');
        }

        if(!empty($mensagem)){
            $mensagem = str_replace("\t","&nbsp;&nbsp;&nbsp;&nbsp",$mensagem);
            $mensagem = str_replace("[sealName]",$relation->seal->name,$mensagem);
            $mensagem = str_replace("[sealOwner]",$relation->seal->owner->name,$mensagem);
            $mensagem = str_replace("[sealShortDescription]",$relation->seal->shortDescription,$mensagem);
            $mensagem = str_replace("[sealRelationLink]",$app->createUrl('seal','printsealrelation',[$relation->id]),$mensagem);
            $mensagem = str_replace("[entityDefinition]",$relation->owner->entityTypeLabel,$mensagem);
            $mensagem = str_replace("[entityName]",$relation->owner->name,$mensagem);
            $mensagem = str_replace("[dateIni]",$dateInicio,$mensagem);

            if($seloExpira){
                $mensagem = str_replace("[dateFin]",$dateFim,$mensagem);
            }
        }
        else{
            $mensagem = \MapasCulturais\i::__('Nome do Selo') . ': ' . $relation->seal->name .'<br/>';
            $mensagem = $mensagem . \MapasCulturais\i::__('Dono do Selo') . ': ' . $relation->seal->name .'<br/>';
            $mensagem = $mensagem . \MapasCulturais\i::__('Descri&ccedil;&atilde;o Curta') . ': ' . $relation->seal->shortDescription .'<br/>';
            $mensagem = $mensagem . \MapasCulturais\i::__('Tipo de Entidade') . ': ' . $relation->owner->entityTypeLabel .'<br/>';
            $mensagem = $mensagem . \MapasCulturais\i::__('Nome da Entidade') . ': ' . $relation->owner->name .'<br/>';
            $mensagem = $mensagem . \MapasCulturais\i::__('Data de Cria&ccedil;&atilde;o') . ': ' . $dateInicio .'<br/>';

            if($seloExpira){
                $mensagem = $mensagem . \MapasCulturais\i::__('Data de Expira&ccedil;&atilde;o') . ': ' . $dateFim .'<br/>';
            }
        }

        return $mensagem;
    }
}
