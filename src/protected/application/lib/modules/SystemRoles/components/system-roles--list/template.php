<?php 
use MapasCulturais\i;
$this->import('entities');
$this->import('panel--entity-actions');

?>

<entities v-slot="{entities}" :name="name" type='system-role' :query="query" select="id,status,name,slug,permissions">
    <div v-for="role in entities">
        <h4>{{role.id}} {{role.name}} <code>{{role.slug}}</code></h4>

        <panel--entity-actions :entity="role" buttons="delete,destroy,publish" publish="<?php i::esc_attr_e('Recuperar') ?>"></panel--entity-actions>

        <ul>
            <li v-for="permission in role.permissions">{{permission}}</li>
        </ul>
    </div>
</entities>