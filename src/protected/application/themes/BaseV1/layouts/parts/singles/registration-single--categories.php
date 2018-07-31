<?php if($opportunity->registrationCategories): ?>
    <?php
        $category = $entity->category;
        $infos = $opportunity->evaluationMethodConfiguration->infos
    ?>
    <div class="registration-fieldset">
        <h4><?php echo $opportunity->registrationCategTitle; ?></h4>
        <p class="registration-help"><?php echo $opportunity->registrationCategDescription; ?></p>
        <div>
            <?php echo $category; ?>
            <p><?php echo (isset($infos->$category)) ? $infos->$category : "" ;?>
            </p>
        </div>
    </div>
<?php endif; ?>