<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
use MapasCulturais\i;

$this->import('
    mc-avatar
');
?>
<?php $this->applyTemplateHook('opportunity-header', 'before'); ?>
<header class="opportunity-header">
    <?php $this->applyTemplateHook('opportunity-header', 'begin'); ?>
    <div class="opportunity-header__content">
        <div class="left">
            <div class="image">
               <mc-avatar :entity="firstPhase" size="medium"></mc-avatar>
            </div>
            <div class="title">
                <span class="title__title">
                    <a :href="firstPhase.getUrl('single')">{{firstPhase.name}}</a>
                </span>
                <div class="title__info">
                    <div class="data">
                        <div class="data__title"> <?= i::__('ID')?>: </div>
                        <div class="data__info "><strong>{{opportunity.id}}</strong></div>
                    </div>
                    <div class="data">
                        <div class="data__title"> <?= i::__('Tipo')?>: </div>
                        <div class="data__info opportunity__color"> {{firstPhase.type?.name}} </div>
                    </div>
                    <div v-if="firstPhase.ownerEntity?.name" class="data">
                        <div class="data__title"> {{type}}: </div>
                        <div class="data__info"> <mc-link :entity="firstPhase.ownerEntity"></mc-link> <!-- {{firstPhase.ownerEntity?.name}} --> </div>
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