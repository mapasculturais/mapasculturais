<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * 
 */

use MapasCulturais\i;
?>

<?php $this->applyTemplateHook('opportunity-header', 'before'); ?>
<header class="opportunity-header">
    <?php $this->applyTemplateHook('opportunity-header', 'begin'); ?>
    <div class="opportunity-header__content">
        <div class="left">
            <div class="image">
                <img v-if="opportunity?.files?.avatar" :src="opportunity.files?.avatar?.transformations?.avatarMedium?.url" />
                <mc-icon v-if="!opportunity?.files?.avatar" name="image"></mc-icon>
            </div>
            <div class="title">
                <span class="title__title">
                    <a :href="opportunity.getUrl('single')">{{opportunity?.name}}</a>
                </span>
                <div class="title__info">
                    <div class="data">
                        <div class="data__title"> <?= i::__('Tipo')?>: </div>
                        <div class="data__info opportunity__color"> {{opportunity?.type?.name}} </div>
                    </div>
                    <div v-if="opportunity.ownerEntity?.name" class="data">
                        <div class="data__title"> {{type}}: </div>
                        <div class="data__info"> <mc-link :entity="opportunity?.ownerEntity"></mc-link> <!-- {{opportunity?.ownerEntity?.name}} --> </div>
                    </div>
                </div>
                <div>
                    <slot name="opportunity-header-info-end"></slot>
                </div>
            </div>
        </div>
        <div class="right">
            <slot name="button"></slot>
        </div>
        
    </div>
    <div class="">
        <slot name="footer"></slot>
    </div>
    <?php $this->applyTemplateHook('opportunity-header', 'end'); ?>
</header>
<?php $this->applyTemplateHook('opportunity-header', 'after'); ?>