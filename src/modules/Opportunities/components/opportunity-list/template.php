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

<mc-card>
    <template #title>
        <mc-title tag="h4"><?php i::esc_attr_e('Lista de oportunidades vinculadas'); ?></mc-title>
    </template>
    <template #content>
        <div class="opportunity-list">
            <mc-entities select="id,name,files.avatar" order="name ASC" :type="type" :query="query" #default="{entities}">
                <slot :entities="entities">
                    <ul v-if="entities.length>0" class="opportunity-list__list">
                        <li v-for="entity in entities">
                            <div class="col-12 opportunity-list__background">

                                <div class="col-12 opportunity-list__card">
                                    <div class="col-12 opportunity-list__cardheader">

                                        <mc-avatar :entity="entity" size="xsmall"></mc-avatar>
                                        <p class="opportunity-list__name"> {{entity.name}}
                                    </div>
                                    <div class="col-12 opportunity-list__cardlink">
                                        <div class="col-8"></div>
                                        <mc-link :entity="entity" class="opportunity-list__link col-4"><label class="opportunity-list__label">Acessar</label><mc-icon name="arrowPoint-right" class="opportunity-list__icon"></mc-icon></mc-link>
                                    </div>

                                </div>

                            </div>
                        </li>
                    </ul>
                </slot>
            </mc-entities>
        </div>
    </template>
</mc-card>