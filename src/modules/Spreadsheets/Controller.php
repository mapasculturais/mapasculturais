<?php

namespace Spreadsheets;

use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use MapasCulturais\i;

class Controller extends \MapasCulturais\Controller
{
    function GET_exportSpreadsheets()
    {
        $app = App::i();

        //$extension = $this->data['extension'] ?? 'xlsx';
                
        $app->enqueueOrReplaceJob('entities-spreadsheets', [
            'owner' => $app->user,
            'authenticatedUser' => $app->user,
            'extension' => 'xlsx',
            'entityClassName' => Agent::class,
            'query' => [
                '@select' => $this->data['@select'] ?? 'id,name',
                '@order' => $this->data['@order'] ?? 'id ASC'
            ] 
        ]);
    }
}
