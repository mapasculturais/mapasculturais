<?php if ($entity->shortDescription || $this->isEditable()): ?>

    <div id="intro-das-inscricoes" ng-class="{'registration-fieldset': data.isEditable}">
        <h4 ng-if="data.isEditable"><?php \MapasCulturais\i::_e("Introdução");?></h4>
        <p class="registration-help" ng-if="data.isEditable"><?php \MapasCulturais\i::_e("Crie um texto de introdução.");?></p>
        <p class="js-editable" data-edit="shortDescription" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Introdução da inscrição");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Insira o texto.");?>" data-placeholder="<?php \MapasCulturais\i::esc_attr_e("Insira o texto.");?>" data-showButtons="bottom" data-placement="bottom"><?php echo $this->isEditable() ? $entity->shortDescription : nl2br($entity->shortDescription); ?></p>
    </div>
    <!-- #intro-das-inscricoes -->
<?php endif; ?>