<?php 
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->layout = 'entity'; 

$this->import('
    mc-card
');
?>
<div class="space-card">
    <mc-card class="space-card__card">
        <template #profile>
        <div class="profile">
            <mc-icon name="space"></mc-icon>
        </div>
        </template>
        <template #title>
                <h3 class="card-event__title--title"><?php i::_e("Nome do Evento")?></h3>
                <p class="card-event__title--description"><?php i::_e("Evento")?></p>
        </template>
        <template #content></template>
    </mc-card>
</div>