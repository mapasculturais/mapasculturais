<?php if($project->registrationCategories): ?>
    <div class="registration-fieldset">
        <!-- selecionar categoria -->
        <h4><?php echo $project->registrationCategTitle ?></h4>
        <!-- <p class="registration-help"><?php echo $project->registrationCategDescription ?></p> -->
        <div>
            <?php echo $entity->category ?>
        </div>
    </div>
<?php endif; ?>