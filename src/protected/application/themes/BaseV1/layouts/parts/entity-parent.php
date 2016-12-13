<?php if(!isset($disable_editable) && $this->isEditable() && $entity->canUser('modifyParent')): ?> 
    <?php if($child_entity_request): $par = $child_entity_request->destination; ?>
        <a href="<?php echo $par->singleUrl ?>"><?php echo $par->name ?></a>
    <?php else:?>
        <?php if($entity instanceof MapasCulturais\Entities\Project): ?>
            <span  class="js-search js-include-editable"
                   data-field-name='parentId'
                   data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Selecionar projeto pai");?>"
                   data-search-box-width="400px"
                   data-search-box-placeholder="<?php \MapasCulturais\i::esc_attr_e("Selecionar projeto pai");?>"
                   data-entity-controller="project"
                   data-search-result-template="#agent-search-result-template"
                   data-selection-template="#agent-response-template"
                   data-no-result-template="#agent-response-no-results-template"
                   data-selection-format="parentProject"
                   data-allow-clear="1"
                   title="<?php \MapasCulturais\i::esc_attr_e("Selecionar projeto pai");?>"
                   data-value="<?php if($entity->parent) echo $entity->parent->id; ?>"
                   data-value-name="<?php if($entity->parent) echo $entity->parent->name; ?>"
             ><?php if($entity->parent) echo $entity->parent->name; ?></span>

        <?php elseif($entity instanceof MapasCulturais\Entities\Space): ?>
            <span  class="js-search js-include-editable"
                    data-field-name='parentId'
                    data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Selecionar");?> <?php $this->dict('entities: parent space') ?>"
                    data-search-box-width="400px"
                    data-search-box-placeholder="<?php \MapasCulturais\i::esc_attr_e("Selecionar");?> <?php $this->dict('entities: parent space') ?>"
                    data-entity-controller="space"
                    data-search-result-template="#agent-search-result-template"
                    data-selection-template="#agent-response-template"
                    data-no-result-template="#agent-response-no-results-template"
                    data-selection-format="parentSpace"
                    data-allow-clear="1"
                    title="<?php \MapasCulturais\i::esc_attr_e("Selecionar");?> <?php $this->dict('entities: parent space') ?>"
                    data-value="<?php if($entity->parent) echo $entity->parent->id; ?>"
                    data-value-name="<?php if($entity->parent) echo $entity->parent->name; ?>"
              ><?php if($entity->parent) echo $entity->parent->name; ?></span>
        <?php endif; ?>
    <?php endif; ?>

    <span class="warning pending js-pending-parent hltip" data-hltip-classes="hltip-warning" hltitle="<?php \MapasCulturais\i::esc_attr_e("Aguardando confirmação");?>" <?php if(!$child_entity_request) echo 'style="display:none"'?> ></span>

<?php elseif($entity->parent): ?>
    <h4 class="entity-parent-title"><a href="<?php echo $entity->parent->singleUrl; ?>"><?php echo $entity->parent->name; ?></a></h4>
<?php endif; ?>