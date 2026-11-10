  
  <?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-admins
    entity-related-agents
    entity-owner
    mc-card
');
?>
  <mc-card>
       <template #content>
           <div class="grid-12">
               <?php $this->applyTemplateHook('tab-entity-info', 'before'); ?>
               <entity-admins :entity="entity" classes="col-12" editable></entity-admins>
               <entity-owner :entity="entity" classes="col-12" title="Publicado por" editable></entity-owner>
               <entity-related-agents :entity="entity" classes="col-12" editable></entity-related-agents>
               <?php $this->applyTemplateHook('tab-entity-info', 'after'); ?>
           </div>
       </template>
   </mc-card>