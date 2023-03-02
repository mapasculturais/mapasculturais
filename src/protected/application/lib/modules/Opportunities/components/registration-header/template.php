<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * 
 */

use MapasCulturais\i;
?>

<?php $this->applyTemplateHook('registration-header', 'before'); ?>
<header class="registration-header">
    <?php $this->applyTemplateHook('registration-header', 'begin'); ?>

    <div class="registration-header__content">
        <div class="image">
            <img v-if="registration.opportunity.files?.avatar" :src="registration.opportunity.files?.avatar?.transformations?.avatarMedium?.url" />
            <mc-icon v-if="!registration.opportunity.files?.avatar" name="image"></mc-icon>
        </div>
        <div class="title">
            <span class="title__title">{{registration.opportunity.name}}</span>
            <div class="title__info">
                <div class="data">
                    <div class="data__title"> <?= i::__('Tipo') ?>: </div>
                    <div class="data__info opportunity__color"> {{registration.opportunity.type.name}} </div>
                </div>
                <div class="data">
                    <div class="data__title"> <?= i::__('Projeto') ?>: </div>
                    <div class="data__info"> {{registration.opportunity.ownerEntity.name}} </div>
                </div>
            </div>
        </div>
    </div>

    <?php $this->applyTemplateHook('registration-header', 'end'); ?>
</header>
<?php $this->applyTemplateHook('registration-header', 'after'); ?>