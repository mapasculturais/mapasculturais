<?php
/** @var MapasCulturais\Theme $this */

use Doctrine\ORM\Query\Expr\Select;
use MapasCulturais\i;

$this->import('mapas-card');

?>

    <entities type="notification" :query='query' #default='{entities}'>
        <mapas-card v-for="entity in entities" :key="entity.__objectId">
            <template #title>
                <div v-html='entity.message'></div>
            </template>    
                {{entity}}
        </mapas-card>
    </entities>