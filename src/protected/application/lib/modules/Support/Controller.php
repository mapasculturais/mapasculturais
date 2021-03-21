<?php
namespace Support;
use MapasCulturais\App;
use MapasCulturais\Traits;
class Controller extends \MapasCulturais\Controller
{
   use Traits\ControllerAPI;

    function GET_registration()
    {
        $this->requireAuthentication();
        $app = App::i();
        $registration = $app->repo('Registration')->find($this->data['id']);
        $relation = $app->repo('AgentRelation')->findOneBy(['agent' => $app->user->profile, 'objectId' => $registration->opportunity->id, 'group' => '@support' ]);
        if ($registration && $registration->canUser('support') ){
            $registration->registerFieldsMetadata();
            $this->render('registration', [
                'entity' => $registration,
                'userAllowedFields' => $relation->metadata
            ]);
        }

    }

   /**
    * Pega os agentes relacionados do grupo de @suporte
    */
   public function GET_getAgentsRelation()
   {
      $opportunity = $this->getOpportunity();      
      $agents = $opportunity->getAgentRelationsGrouped('@support');
      $this->apiResponse($agents);
   }

   
   public function PATCH_setPermissonFields()
   {
    
   }
   
   /**
    * Pega a oportunidade
    */
   private function getOpportunity()
   {
      $this->requireAuthentication();
      $request = $this->data;
      return App::i()->repo("Opportunity")->find($request["opportunity_id"]);
      
   }
}
