<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
$this->import('
    mc-link
    mc-icon
');

?>

<div class="entity-cards">
    <div class="entity-cards__panel">
        <div class="entity-cards__content">
            <div v-for="card in cards" class="entity-cards-cards" :class="{'entity-cards--double' : card.data.length > 1}">
                <div v-for="data in card.data" class="entity-cards-cards__content">
                    <div class="entity-cards-cards__info">
                        <strong class="entity-cards-cards__number" :class="lengthClass(data.value)">{{data.value}}</strong>
                        <label class="entity-cards-cards__label">{{data.label}}</label>                    
                    </div>
                    <div class="entity-cards-cards__icon"><mc-icon :class="card.iconClass" :name="data.icon"></mc-icon></div>
                </div>
            </div>
        </div>
    </div>
</div>