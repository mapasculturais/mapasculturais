<?php 
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-entities
    panel--entity-actions
');
?>
<mc-entities v-slot="{entities}" :name="name" type='system-role' :query="query" select="id,status,name,slug,permissions">
    <div v-for="role in entities">
        <h4>{{role.id}} {{role.name}} <code>{{role.slug}}</code></h4>

        <panel--entity-actions :entity="role" buttons="delete,destroy,publish" publish="<?php i::esc_attr_e('Recuperar') ?>"></panel--entity-actions>

        <ul>
            <li v-for="permission in role.permissions">{{permission}}</li>
        </ul>
    </div>
</mc-entities>