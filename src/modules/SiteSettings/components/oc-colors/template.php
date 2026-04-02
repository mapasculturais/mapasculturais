<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-icon
    oc-actions
')
?>

<div class="colors w100">
    <oc-dialog>
        <template #content>
            <?= i::__('Aqui é possível definir as cores que o Mapas Culturais terá. É possível configurar a cor principal, que será aplicada nos botões principais,') ?>
            <?= i::__('além de definir cores específicas para cada entidade: Oportunidades, Agentes, Eventos, Espaços e Projetos.') ?>
        </template>
    </oc-dialog>

    <div class="color-entities">
        <div class="left">

            <!-- primaria -->
            <div class="color">
                <div class="item">
                    <div class="name" :style="`color:${entity.primaryColor}`">
                        {{entity.primaryColor}}
                    </div>
                    <div class="content">
                        <div class="title">
                            <div class="point" :style="`background-color:${entity.primaryColor}`"></div>
                            <div class="label fbold" :style="`color:${entity.primaryColor}`"><?= i::__('Cor primária') ?></div>
                        </div>
                        <div class="color-field">
                            <input id="colorInput1" type="color" v-model="entity.primaryColor">
                            <label for="colorInput1">
                                <mc-icon name="one-click-edit" :style="`color:${entity.primaryColor}`"></mc-icon>
                            </label>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Secundária -->
            <div class="color">
                <div class="item">
                    <div class="name" :style="`color:${entity.secondaryColor}`">
                        {{entity.secondaryColor}}
                    </div>
                    <div class="content">
                        <div class="title">
                            <div class="point" :style="`background-color:${entity.secondaryColor}`"></div>
                            <div class="label fbold" :style="`color:${entity.secondaryColor}`"><?= i::__('Cor secundária') ?></div>
                        </div>
                        <div class="color-field">
                            <input id="colorInput2" type="color" v-model="entity.secondaryColor">
                            <label for="colorInput2">
                                <mc-icon name="one-click-edit" :style="`color:${entity.secondaryColor}`"></mc-icon>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Opportunidades -->
            <div class="color">
                <div class="item">
                    <div class="name" :style="`color:${entity.opportunitiesColor}`">
                        {{entity.opportunitiesColor}}
                    </div>
                    <div class="content">
                        <div class="title">
                            <div class="point" :style="`background-color:${entity.opportunitiesColor}`"></div>
                            <div class="label fbold" :style="`color:${entity.opportunitiesColor}`"><?= i::__('Oportunidades') ?></div>
                        </div>
                        <div class="color-field">
                            <input id="colorInput3" type="color" v-model="entity.opportunitiesColor">
                            <label for="colorInput3">
                                <mc-icon name="one-click-edit" :style="`color:${entity.opportunitiesColor}`"></mc-icon>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Agentes -->
            <div class="color">
                <div class="item">
                    <div class="name" :style="`color:${entity.agentsColor}`">
                        {{entity.agentsColor}}
                    </div>
                    <div class="content">
                        <div class="title">
                            <div class="point" :style="`background-color:${entity.agentsColor}`"></div>
                            <div class="label fbold" :style="`color:${entity.agentsColor}`"><?= i::__('Agentes') ?></div>
                        </div>
                        <div class="color-field">
                            <input id="colorInput4" type="color" v-model="entity.agentsColor">
                            <label for="colorInput4">
                                <mc-icon name="one-click-edit" :style="`color:${entity.agentsColor}`"></mc-icon>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="right">
            <!-- Eventos -->
            <div class="color">
                <div class="item">
                    <div class="name" :style="`color:${entity.eventsColor}`">
                        {{entity.eventsColor}}
                    </div>
                    <div class="content">
                        <div class="title">
                            <div class="point" :style="`background-color:${entity.eventsColor}`"></div>
                            <div class="label fbold" :style="`color:${entity.eventsColor}`"><?= i::__('Eventos') ?></div>
                        </div>
                        <div class="color-field">
                            <input id="colorInput5" type="color" v-model="entity.eventsColor">
                            <label for="colorInput5">
                                <mc-icon name="one-click-edit" :style="`color:${entity.eventsColor}`"></mc-icon>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

             <!-- Espaços -->
             <div class="color">
                <div class="item">
                    <div class="name" :style="`color:${entity.spacesColor}`">
                        {{entity.spacesColor}}
                    </div>
                    <div class="content">
                        <div class="title">
                            <div class="point" :style="`background-color:${entity.spacesColor}`"></div>
                            <div class="label fbold" :style="`color:${entity.spacesColor}`"><?= i::__('Espaços') ?></div>
                        </div>
                        <div class="color-field">
                            <input id="colorInput6" type="color" v-model="entity.spacesColor">
                            <label for="colorInput6">
                                <mc-icon name="one-click-edit" :style="`color:${entity.spacesColor}`"></mc-icon>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

              <!-- Projetos -->
              <div class="color">
                <div class="item">
                    <div class="name" :style="`color:${entity.projectsColor}`">
                        {{entity.projectsColor}}
                    </div>
                    <div class="content">
                        <div class="title">
                            <div class="point" :style="`background-color:${entity.projectsColor}`"></div>
                            <div class="label fbold" :style="`color:${entity.projectsColor}`"><?= i::__('Projetos') ?></div>
                        </div>
                        <div class="color-field">
                            <input id="colorInput7" type="color" v-model="entity.projectsColor">
                            <label for="colorInput7">
                                <mc-icon name="one-click-edit" :style="`color:${entity.projectsColor}`"></mc-icon>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

              <!-- Selos -->
              <div class="color">
                <div class="item">
                    <div class="name" :style="`color:${entity.sealsColor}`">
                        {{entity.sealsColor}}
                    </div>
                    <div class="content">
                        <div class="title">
                            <div class="point" :style="`background-color:${entity.sealsColor}`"></div>
                            <div class="label fbold" :style="`color:${entity.sealsColor}`"><?= i::__('Selos') ?></div>
                        </div>
                        <div class="color-field">
                            <input id="colorInput8" type="color" v-model="entity.sealsColor">
                            <label for="colorInput8">
                                <mc-icon name="one-click-edit" :style="`color:${entity.sealsColor}`"></mc-icon>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="btn-entity-actions">
        <oc-actions :entity="entity" editable clear-cache></oc-actions>
    </div>
</div>