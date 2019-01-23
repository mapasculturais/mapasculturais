<?php if($opportunity->registrationCategories): ?>
    <?php
        $category = $entity->category;
        $infos = $opportunity->evaluationMethodConfiguration->infos
    ?>
    <ul class="registration-list">
        <li class="registration-fieldset js-field registration-list-item" id="registration-field-category" data-field-id="category">
            <h4><?php echo $opportunity->registrationCategTitle; ?></h4>
            <p class="registration-help"><?php echo $opportunity->registrationCategDescription; ?></p>
            <label><?php echo $opportunity->registrationCategTitle . ': ' .  $category; ?></label>
            <p><?php echo (isset($infos->$category)) ? $infos->$category : "" ;?></p>
        </li>
    </ul>
<?php endif; ?>