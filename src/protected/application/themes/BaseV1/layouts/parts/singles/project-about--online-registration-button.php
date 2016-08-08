<?php if ($entity->useRegistrations && !$this->isEditable() ) : ?>
    <a ng-if="data.projectRegistrationsEnabled" class="btn btn-primary" href="#tab=inscricoes" onclick="$('#tab-inscricoes').click()">Inscrições online</a>
<?php endif; ?>
<div class="clear" ng-if="data.projectRegistrationsEnabled && data.isEditable">Inscrições online <strong><span id="editable-use-registrations" class="js-editable clear" data-edit="useRegistrations" data-type="select" data-value="<?php echo $entity->useRegistrations ? '1' : '0' ?>"
        data-source="[{value: 0, text: 'desativadas'},{value: 1, text:'ativadas'}]"></span></strong>
</div>
