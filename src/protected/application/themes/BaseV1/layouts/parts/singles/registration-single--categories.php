<?php if($opportunity->registrationCategories): ?>
    <div class="registration-fieldset">
        <!-- selecionar categoria -->
        <h4><?php echo $opportunity->registrationCategTitle ?></h4>
        <!-- <p class="registration-help"><?php echo $opportunity->registrationCategDescription ?></p> -->
        <div>
            <?php echo $entity->category ?>
        </div>
    </div>
<?php endif; ?>