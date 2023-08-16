<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-confirm-button
');
?>
<div class="registration-actions">
    <div class="registration-actions__primary">
        <div v-if="Object.keys(registration.__validationErrors).length > 0" class="errors">
            <span class="errors__title"> <?= i::__('Ops! Alguns erros foram identificados.') ?> </span>
            <span class="errors__subtitle"> <?= i::__('Para continuar, corrija os campos com os erros listados abaixo:') ?> </span>
    
            <div v-for="(error, index) in registration.__validationErrors" class="errors__error">
                <div class="errors__error--text">
                    <strong>{{fieldName(index)}}:</strong> <p v-for="text in error">{{text}}</p>
                </div>    
            </div>
        </div>

        <mc-confirm-button @confirm="send()" yes="<?= i::esc_attr__('Enviar agora') ?>" no="<?= i::esc_attr__('Cancelar') ?>" title="<?= i::esc_attr__('Quer enviar sua inscrição?') ?>">
            <template #button="modal">
                <button @click="modal.open()" class="button button--large button--xbg button--primary">
                    <?= i::__("Enviar") ?>
                </button>
            </template> 
            <template #message="message">
                <?php i::_e('Ao enviar sua inscrição você já estará participando da oportunidade.') ?>
            </template>
        </mc-confirm-button> 

        <!-- <button class="button button--large button--xbg button--primary" @click="send()"> <?= i::__('Enviar') ?> </button> -->
    </div>
    <div class="registration-actions__secondary">
        <button class="button button--large button--primary-outline" @click="validate()"> <?= i::__('Validar inscrição') ?> </button>
        <button @click="save();" class="button button--large button--primary-outline">
            <?= i::__("Salvar") ?>
        </button>
 
        <button @click="exit()" class="button button--large button--primary-outline">
            <?= i::__("Salvar e sair") ?>
        </button>
    </div>
</div>