<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
   mc-card
   mc-avatar
   mc-title
   mc-entities
');
?>

<div v-if="opportunities" class="opportunity-list">
    <mc-title tag="h4" class="bold"><?php i::esc_attr_e('Lista de oportunidades vinculadas'); ?></mc-title>
    
    <div class="opportunity-list__content">
        <ul  class="opportunity-list__list">
            <div class="col-12 opportunity-list__container">
                <li v-for="opp in opportunities">
                    <div class="col-12 opportunity-list__card">
                        <div class="col-12 opportunity-list__cardheader">
                            <mc-avatar :entity="opp" size="xsmall"></mc-avatar>
                            <p class="opportunity-list__name opportunity__color bold"> {{opp.name}}</p>
                        </div>
                        <div class="col-12 opportunity-list__cardlink primary__color">
                            <mc-link :entity="opp" class="opportunity-list__link primary__color bold"><?php i::esc_attr_e('Acessar') ?><mc-icon name="arrowPoint-right" class="opportunity-list__icon"></mc-icon></mc-link>
                        </div>
                    </div>
                </li>
            </div>
        </ul>
    </div>
</div>