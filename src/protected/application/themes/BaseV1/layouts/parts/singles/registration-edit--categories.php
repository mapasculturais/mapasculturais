<?php if($opportunity->registrationCategories): ?>
    <div class="registration-fieldset">
        <!-- selecionar categoria -->
        <h4><?php echo $opportunity->registrationCategTitle ?></h4>
        <p class="registration-help"><?php echo $opportunity->registrationCategDescription ?></p>
        <p>
            <span class='js-editable-registrationCategory' data-original-title="<?php \MapasCulturais\i::esc_attr_e("Opção");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Selecione uma opção");?>" data-value="<?php echo htmlentities($entity->category) ?>"><?php echo $entity->category ?></span>
        </p>
    </div>
<?php endif; ?>