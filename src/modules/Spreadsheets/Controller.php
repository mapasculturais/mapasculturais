<?php

namespace Spreadsheets;

use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Event;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Project;
use MapasCulturais\Entities\Space;
use MapasCulturais\Entities\User;
use MapasCulturais\i;

class Controller extends \MapasCulturais\Controller
{   
    function getExtension() {
        /**
         * @todo fazer a verificação das extensões (csv, xlsx, ods)
         */
        return $this->data['extension'] ?? 'csv';
    }

    function POST_entities()
    {
        $app = App::i();
        $extension = $this->getExtension();
        $entity_class_names = [
            'agent' => Agent::class,
            'space' => Space::class,
            'event' => Event::class,
            'project' => Project::class,
            'opportunity' => Opportunity::class,
        ];

        $entity_class_name = $entity_class_names[$this->data['entityType'] ?? 'invalida'] ?? false;

        if (!$entity_class_name) {
            $this->errorJson(i::__('Tipo de entidade inválida'));
        }
                
        try {
            $app->enqueueOrReplaceJob('entities-spreadsheets', [
                'owner' => $app->user,
                'authenticatedUser' => $app->user,
                'extension' => $extension,
                'entityClassName' => $entity_class_name,
                'query' => [
                    '@select' => $this->data['@select'] ?? 'id,name',
                    '@order' => $this->data['@order'] ?? 'id ASC'
                ] 
            ]);

            $this->json(true);
        } catch(\Throwable $e) {
            $this->errorJson($e, 500);
        }
    }
}
