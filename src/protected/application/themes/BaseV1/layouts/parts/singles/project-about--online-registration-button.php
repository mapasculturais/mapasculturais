<?php if ($entity->useRegistrations && !$this->isEditable() ) : ?>
    <a ng-if="data.projectRegistrationsEnabled" class="btn btn-primary" href="#tab=inscricoes" onclick="$('#tab-inscricoes').click()"><?php \MapasCulturais\i::_e("Inscrições online");?></a>
<?php endif; ?>
<div class="clear" ng-if="data.projectRegistrationsEnabled && data.isEditable"><?php \MapasCulturais\i::_e("Inscrições online");?> <strong><span id="editable-use-registrations" class="js-editable clear" data-edit="useRegistrations" data-type="select" data-value="<?php echo $entity->useRegistrations ? '1' : '0' ?>"
        data-source="[{value: 0, text: '<?php \MapasCulturais\i::esc_attr_e("desativadas");?>'},{value: 1, text:'<?php \MapasCulturais\i::esc_attr_e("ativadas");?>'}]"></span></strong>
</div>
