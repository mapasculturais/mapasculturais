<?php

namespace Spreadsheets;

use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Event;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Project;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\RegistrationEvaluation;
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

        $query = $this->data['query'];

        unset($query['@limit']);
        unset($query['@page']);
        
        if($select = $this->data['@select']) {
            unset($query['@select']);
            $query['@select'] = $select;
        }
              
        $app->enqueueOrReplaceJob('entities-spreadsheets', [
            'owner' => $owner,
            'authenticatedUser' => $app->user,
            'extension' => $extension,
            'entityClassName' => $entity_class_name,
            'query' => $query
        ]);

        $this->json(true);
    }

    function POST_registrations()
    {
        $app = App::i();

        $extension = $this->getExtension();
        $owner = $this->getOwner();
        $owner_properties = $app->config['registration.reportOwnerProperties'];
        $owner_properties = implode(',', $owner_properties);
        
        $query = $this->data['query'];

        unset($query['@select']);
        unset($query['@limit']);
        unset($query['@page']);
        $query['@select'] = $this->data['@select'];

        $app->enqueueOrReplaceJob('registrations-spreadsheets', [
            'owner' => $owner,
            'authenticatedUser' => $app->user,
            'extension' => $extension,
            'entityClassName' => Registration::class,
            'query' => $query,
            'owner_properties' => $owner_properties
        ]);
        
        $this->json(true);
    }

    function POST_evaluations()
    {
        $app = App::i();
        $extension = $this->getExtension();
        
        $owner = $this->getOwner();

        $job_slug = "{$owner->evaluationMethod->slug}-spreadsheets";

        if($job_type = $app->getRegisteredJobType($job_slug)) {
            $app->enqueueOrReplaceJob($job_slug, [
                'owner' => $owner,
                'authenticatedUser' => $app->user,
                'extension' => $extension,
                'entityClassName' => RegistrationEvaluation::class,
                'query' => [
                    '@select' => $this->data['@select'] ?? 'id,name',
                ]
            ]);

            $this->json(true);
        } else {
            $this->errorJson(i::__('Método de avaliação inválido'));
        }
    }
}
