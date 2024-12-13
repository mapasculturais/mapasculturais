<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-confirm-button
    mc-popover
');
?>
<div v-if="entity.metalists.links || editable" :class="['entity-links', classes]">
    <h4 class="entity-links__title"> {{title}} </h4>

    <ul v-if="entity.metalists.links" class="entity-links__links">
        <li class="entity-links__links--item" v-for="metalist in entity.metalists.links">
            <a class="link" :class="{'editable': editable}" :href="metalist.value" target="_blank">
                <mc-icon name="link"></mc-icon>
                {{metalist.title}}
            </a>
        </li>
    </ul>

    <div class="entity-links__list" v-if="editable">
        <div class="entity-links__item" v-for="link in entity.metalists.links">
            <div class="grid-12">
                <div class="field col-6">
                    <label for=""><?= i::__('Título') ?></label>
                    <input v-model="link.title" type="text" name="linkTitle" @blur="link.save()" />
                </div>

                <div class="field col-6">
                    <label for=""><?= i::__('URL') ?></label>
                    <input v-model="link.value" type="url" name="link" @blur="link.save()" />
                </div>
            </div>

            <mc-confirm-button @confirm="link.delete()">
                <template #button="{open}">
                    <a @click="open()" class="entity-links__button--saved-remove entity-links__button--remove"><mc-icon name="trash"></mc-icon><?= i::__('remover link') ?></a>
                </template>
                <template #message="message">
                    <?php i::_e('Deseja remover o link?') ?>
                </template>
            </mc-confirm-button>
        </div>

        <div v-for="(link, index) in newLinks" class="entity-links__item">
            <div class="grid-12">
                <div class="field col-6">
                    <label for="">Título</label>
                    <input v-model="link.title" class="newLinkTitle" type="text" name="newLinkTitle" />
                </div>

                <div class="field col-6">
                    <label for="">URL</label>
                    <input v-model="link.value" type="url" name="newLink" />
                </div>
            </div>

            <div class="entity-links__buttons">
                <mc-confirm-button @confirm="delete(index)">
                    <template #button="{open}">
                        <a @click="open()" class="entity-links__button entity-links__button--remove"><mc-icon name="trash"></mc-icon><?= i::__('remover link') ?></a>
                    </template>
                    <template #message="message">
                        <?php i::_e('Deseja remover o link?') ?>
                    </template>
                </mc-confirm-button>

                <a @click="save(link, index)" class="entity-links__button entity-links__button--save"><mc-icon name="check"></mc-icon><?= i::__('salvar') ?></a>
            </div>
        </div>

        <div class="entity-links__add-button">
            <button class="entity-links__add-link button button--primary button--icon button--primary-outline" @click="addLink()">
                <mc-icon name="add"></mc-icon>
                <?php i::_e("Adicionar Link") ?>
            </button>
        </div>
    </div>
</div>



<!--<mc-popover v-if="editable" openside="down-right" title="<?php i::esc_attr_e('Adicionar Link') ?>">
    <template #button="popover">
        <a @click="popover.toggle()" class="button button--primary button--icon button--primary-outline">
            <mc-icon name="add"></mc-icon>
            <?php i::_e("Adicionar Link") ?>
        </a>
    </template>

    <template #default="popover">
        <form @submit="create(popover); $event.preventDefault();" class="entity-links__newLink">
            <div class="grid-12">
                <div class="col-12">
                    <div class="field">
                        <label><?php i::_e('Título do link') ?></label>
                        <input v-model="metalist.title" class="newLinkTitle" type="text" name="newLinkTitle" />
                    </div>
                </div>

                <div class="col-12">
                    <div class="field">
                        <label><?php i::_e('Link') ?></label>
                        <input v-model="metalist.value" class="newLink" type="url" name="newLink" />
                    </div>
                </div>

                <button class="col-6 button button--text" type="reset" @click="popover.close()"> <?php i::_e("Cancelar") ?> </button>
                <button class="col-6 button button--solid" type="submit"> <?php i::_e("Confirmar") ?> </button>
            </div>
        </form>
    </template>
</mc-popover> -->
<!-- </div> -->