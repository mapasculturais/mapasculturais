<?php

/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * 
 * 
 * @todo renomear componente
 */

use MapasCulturais\i;

$this->import('
    entity-field
    mc-card
');
?>
<mc-card v-if="useProjectRelation !== 'dontUse'">
    <template #content>
        <div class="registration-related-entity">
            <entity-field :entity="registration" prop="projectName" :autosave="60000"></entity-field>
        </div>
    </template>
</mc-card>