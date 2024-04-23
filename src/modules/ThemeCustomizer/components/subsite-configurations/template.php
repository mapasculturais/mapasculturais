<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-field
    mc-multiselect
    mc-tag-list
    entity-seals
');

?>

<div class="subsite-configurations">
    <fieldset class="subsite-configurations__section">
        <legend class="subsite-configurations__section-legend">
            <h3> <?= i::__('Configurações do site') ?> </h3>
        </legend>

        <entity-field :entity="subsite" prop="name" @change="subsite.save()"></entity-field>

        <entity-field :entity="subsite" prop="shortDescription" @change="subsite.save()"></entity-field>

        <mc-multiselect :model="subsite.lang_config" :items="langs" title="<?= i::esc_attr__('Selecione os tipos: ') ?>" @selected="subsite.save()" @removed="sibsite.save()" hide-filter hide-button>
            <template #default="{popover, setFilter, filter}">
                <div class="field">
                    <label><?= i::__('Idiomas') ?></label>
                    <input class="mc-multiselect--input" @keyup="setFilter($event.target.value)" @focus="popover.open()" placeholder="<?= i::esc_attr__('Selecione os idiomas') ?>" />
                    <mc-tag-list classes="primary__background" :tags="subsite.lang_config" :labels="langs" @remove="subsite.save()" editable></mc-tag-list>
                </div>
            </template>
        </mc-multiselect>

        <div class="entity-seals">
            <h4 class="entity-seals__title bold"> <?= i::__("Selos verificadores") ?> </h4>

            <div class="entity-seals__seals">
                <div class="entity-seals__seals--seal" v-for="seal in seals">
                    <div class="seal-icon">
                        <a :href="seal.singleUrl" class="link ">
                            <div v-if="seal.files?.avatar" class="image">
                                <mc-avatar :entity="seal" size="small" square></mc-avatar>
                            </div>
                            <div v-if="!(seal.files?.avatar)">
                                <mc-icon name="seal"></mc-icon>
                            </div>
                        </a>

                        <div class="icon">
                            <mc-confirm-button @confirm="removeSeal(seal)">
                                <template #button="modal">
                                    <mc-icon @click="modal.open()" name="delete"></mc-icon>
                                </template>
                                <template #message="message">
                                    <?php i::_e('Remover selo?') ?>
                                </template>
                            </mc-confirm-button>
                        </div>
                    </div>
                    <span class="seal-label">{{seal.name}}</span>
                </div>

                <select-entity type="seal" :query="selectEntityQuery" @select="addSeal($event)">
                    <template #button="{ toggle }">
                        <div class="entity-seals__seals--addSeal" @click="toggle()">
                            <mc-icon name="add"></mc-icon>
                        </div>
                    </template>
                </select-entity>
            </div>
        </div>
    </fieldset>
</div>