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
    protected array $entity_class_names = [
        'agent' => Agent::class,
        'space' => Space::class,
        'event' => Event::class,
        'project' => Project::class,
        'opportunity' => Opportunity::class,
        'user' => User::class,
    ];

    function getExtension() {
        $extension = strtolower($this->data['extension'] ?? 'csv');
        $extensions = ['xlsx', 'csv', 'ods'];

        if (!in_array($extension, $extensions)) {
            $this->errorJson(i::__('Extensão não suportada'));
        }
        
        return $extension;
    }

    function getOwner() {
        $app = App::i();

        $owner_class_name = $this->entity_class_names[$this->data['ownerType'] ?? 'invalida'] ?? false;

        $owner_id = $this->data['ownerId'] ?? false;
        if (!$owner_id || !$owner_class_name) {
            $this->errorJson(i::__('É preciso enviar os parâmetros ownerType e ownerId'));
        }

        $owner = $app->repo($owner_class_name)->find($owner_id);
        if (!$owner) {
            $this->errorJson(i::__('O proprietário do arquivo não foi encontrado'));
        }

        return $owner;
    }

    function POST_entities()
    {
        $app = App::i();
        $extension = $this->getExtension();

        $entity_class_name = $this->entity_class_names[$this->data['entityType'] ?? 'invalida'] ?? false;
        if (!$entity_class_name) {
            $this->errorJson(i::__('Tipo de entidade inválida'));
        }

        $owner = $this->getOwner();
                
        $app->enqueueOrReplaceJob('entities-spreadsheets', [
            'owner' => $owner,
            'authenticatedUser' => $app->user,
            'extension' => $extension,
            'entityClassName' => $entity_class_name,
            'query' => [
                '@select' => $this->data['@select'] ?? 'id,name',
                '@order' => $this->data['@order'] ?? 'id ASC'
            ] 
        ]);

        $this->json(true);
    }
}
