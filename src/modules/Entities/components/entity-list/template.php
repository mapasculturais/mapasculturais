<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    mc-avatar
    mc-entities
    mc-link
');
?>

<?php $this->applyTemplateHook('entity-list','before'); ?>
<div v-if="this.ids.length>0" class="entity-list">
    <?php $this->applyTemplateHook('entity-list','begin'); ?>
    <label class="col-12 entity-list__title"> {{title}} </label>
    <mc-entities select="id,name,files.avatar" order="name ASC" :type="type" :query="query" #default="{entities}">
        <slot :entities="entities">
            <ul v-if="entities.length>0" class="entity-list__list">
                <li v-for="entity in entities" class="col-12 entity-list__list-item">
                    <mc-link class="entity-list__list-item-link" :entity="entity">
                        <div class="entity-list__list-item-img">
                           <mc-avatar :entity="entity" size="xsmall"></mc-avatar>
                        </div>
                        <div class="entity-list__list-item"> {{showContent(entity.name)}} </div>
                    </mc-link>
                </li>
            </ul>
        </slot>
    </mc-entities>
    <?php $this->applyTemplateHook('entity-list','end'); ?>
</div>
<?php $this->applyTemplateHook('entity-list','after'); ?>