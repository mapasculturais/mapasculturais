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
<div class="opportunity-list">

    <mc-title tag="h4" class="bold"><?php i::esc_attr_e('Lista de oportunidades vinculadas'); ?></mc-title>

    <div class="opportunity-list__content">
        <mc-entities select="id,name,files.avatar" order="name ASC" :type="type" :query="query" #default="{entities}">
            <slot :entities="entities">
                <ul v-if="entities.length>0" class="opportunity-list__list">
                    <div class="col-12 opportunity-list__container">
                        <li v-for="entity in entities">

                            <div class="col-12 opportunity-list__card">
                                <div class="col-12 opportunity-list__cardheader">

                                    <mc-avatar :entity="entity" size="xsmall"></mc-avatar>
                                    <p class="opportunity-list__name opportunity__color bold"> {{entity.name}}</p>
                                </div>
                                <div class="col-12 opportunity-list__cardlink primary__color">
                                    <mc-link :entity="entity" class="opportunity-list__link primary__color"><label class="opportunity-list__label bold"><?php i::esc_attr_e('Acessar') ?></label><mc-icon name="arrowPoint-right" class="opportunity-list__icon"></mc-icon></mc-link>
                                </div>

                            </div>

                        </li>
                    </div>
                </ul>
            </slot>
        </mc-entities>
    </div>

</div>