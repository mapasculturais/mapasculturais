<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-field
    mc-confirm-button
    mc-alert
    mc-link
');
?>
<div :class="[{'col-12': !phase.isLastPhase}, {'opportunity-phase-publish-config-registration--published': phase.publishedRegistrations},'opportunity-phase-publish-config-registration']">
    
    <div :class="[{'grid-12': !tab=='registration' && phase.isLastPhase}, 'opportunity-phase-publish-config-registration__content' ]">
        <div :class="[{'col-12 grid-12 opportunity-phase-publish-config-registration__lastphase': phase.isLastPhase}, {'col-12 grid-12': !phase.isLastPhase}]">
            <mc-alert v-if="!phase.publishedRegistrations && !phase.isLastPhase"  class="col-12" type="warning">
                <?= i::__('Fique atento! A publicação do resultado é opcional e só pode ser feita após o término da fase. <strong>Esta ação deixará público o nome e o número de inscrição das pessoas inscritas.</strong>') ?>
            </mc-alert>
            <div v-if="!phase.isLastPhase" :class="[{'col-5 opportunity-phase-publish-config-registration__left': !phase.isLastPhase}]">
                    <div v-if="phase.publishedRegistrations && (!phase.isContinuousFlow || (phase.isContinuousFlow && phase.hasEndDate))" class="msg-auto-pub col-4">
                        <p class="bold"><?= i::__('O resultado já foi publicado') ?></p>
                    </div>
                    <div v-else-if="phase.publishTimestamp" class="msgpub-date" :class="[{'col-4': !phase.isLastPhase},]">
                        <p class="bold" v-if="phase.autoPublish">
                            <?= sprintf(
                                i::__("O resultado será publicado automaticamente no dia %s às %s"),
                                "{{phase.publishTimestamp.date('2-digit year')}}",
                                "{{phase.publishTimestamp.time('2-digit')}}"
                            ) ?>
                        </p>
                        <p class="bold" v-else>
                            <?= sprintf(
                                i::__("O resultado será publicado no dia %s às %s"),
                                "{{phase.publishTimestamp.date('2-digit year')}}",
                                "{{phase.publishTimestamp.time('2-digit')}}"
                            ) ?>
                        </p>
                    </div>
                    <div v-else-if="phase.autoPublish" class="msg-auto-pub col-4">
                        <p class="bold"><?= i::__('O resultado será publicado automaticamente') ?></p>
                    </div>
                    <div v-else>
                        <div v-if="!isOpenPhase" class="col-4">
                            <p class="bold"><?= i::__("Você pode publicar o resultado manualmente a qualquer momento utilizando o botão ao lado.") ?></p>
                        </div>
                    </div>
            </div>
            <div class="opportunity-phase-publish-config-registration__unpublishedlast" :class="[{'col-6':!phase.isLastPhase}, {'col-12 grid-12' : phase.isLastPhase}]">
                <div v-if="tab=='registrations' && phase.isLastPhase" class="opportunity-phase-publish-config-registration__registrationList col-12">
                    <div class="opportunity-phase-list-registrations__status col-6">
                        <h4 class="bold"><?php i::_e("Status das inscrições") ?></h4>
                        <p v-if="phase.summary.registrations"><?= i::__("Quantidade de inscrições:") ?> <strong>{{phase.summary.registrations}}</strong><strong> <?= i::__('inscrições') ?></strong></p>
                        <p v-if="phase.summary?.sent"><?= i::__("Quantidade de inscrições <strong>enviadas/pendentes</strong>:") ?> <strong>{{phase.summary.sent}}</strong> <strong><?= i::__('inscrições') ?></strong></p>
                        <p v-if="phase.summary?.Draft"><?= i::__("Quantidade de inscrições <strong>rascunho</strong>:") ?> <strong>{{phase.summary.Draft}}</strong> <strong><?= i::__('inscrições') ?></strong></p>
                        <p v-if="phase.summary?.Approved"><?= i::__("Quantidade de inscrições <strong>selecionadas</strong>:") ?> <strong>{{phase.summary.Approved}}</strong> <strong><?= i::__('inscrições') ?></strong></p>
                        <p v-if="phase.summary?.Notapproved"><?= i::__("Quantidade de inscrições <strong>não selecionadas</strong>:") ?> <strong>{{phase.summary.Notapproved}}</strong> <strong><?= i::__('inscrições') ?></strong></p>
                        <p v-if="phase.summary?.Waitlist"><?= i::__("Quantidade de inscrições <strong>suplentes</strong>:") ?> <strong>{{phase.summary.Waitlist}}</strong> <strong><?= i::__('inscrições') ?></strong></p>
                        <p v-if="phase.summary?.Invalid"><?= i::__("Quantidade de inscrições <strong>inválida</strong>:") ?> <strong>{{phase.summary.Invalid}}</strong> <strong><?= i::__('inscrições') ?></strong></p>
    
                    </div>
                    <h5 class="bold col-12"><?= i::__("A lista de inscrições pode ser acessada utilizando o botão abaixo")?></h5>
                    <mc-link  :entity="phase" class="button button--primary button--icon opportunity-phase-publish-config-registration__unpublishedbtn" route="registrations" right-icon>
                        <h4 class="semibold"><?= i::__("Conferir lista de inscrições") ?></h4><mc-icon name="external"></mc-icon>
                    </mc-link>

                </div> 
                
                <div v-if="phase.isLastPhase" :class="[{'col-12': phase.isLastPhase}]">
                        <div v-if="phase.publishedRegistrations" class="msg-auto-pub col-4">
                            <p class="bold"><?= i::__('O resultado já foi publicado') ?></p>
                        </div>
                        <div v-else-if="phase.publishTimestamp" class="msgpub-date" :class="[{'col-4': !phase.isLastPhase},]">
                            <p class="bold" v-if="phase.autoPublish">
                                <?= sprintf(
                                    i::__("O resultado será publicado automaticamente no dia %s às %s"),
                                    "{{phase.publishTimestamp.date('2-digit year')}}",
                                    "{{phase.publishTimestamp.time('2-digit')}}"
                                ) ?>
                            </p>
                            <p class="bold" v-else>
                                <?= sprintf(
                                    i::__("O resultado será publicado no dia %s às %s"),
                                    "{{phase.publishTimestamp.date('2-digit year')}}",
                                    "{{phase.publishTimestamp.time('2-digit')}}"
                                ) ?>
                            </p>
                        </div>
                        <div v-else-if="phase.autoPublish" class="msg-auto-pub col-4">
                            <p class="bold"><?= i::__('O resultado será publicado automaticamente') ?></p>
                        </div>
                        <div v-else class="col-4">
                            <p class="bold"><?= i::__("A publicação do resultado é opcional.") ?></p>
                        </div>
                </div>
                <div v-if="!phase.publishedRegistrations" :class="[{'col-12 grid-12': !phase.isLastPhase}, {'opportunity-phase-publish-config-registration__unpublishlist col-6': phase.isLastPhase}]">
                    <div class="opportunity-phase-publish-config-registration__button " :class="{'col-6': !phase.isLastPhase}">
                        <mc-confirm-button  yes="<?= i::__('Publicar Resultado')?>" @confirm="publishRegistration()">
                            <template #button="modal">
                                <button  :class="['button', 'button--primary', {'button--large col-6': !phase.isLastPhase}, {'disabled': !firstPhase.status >0}, {'button--bg': phase.isLastPhase}, {'disabled': isOpenPhase}]" @click="modal.open()">
                                    <?= i::__("Publicar Resultados") ?>
                                </button>
                            </template>
                            <template #message="message">
                                <h3 class="bold"><?= i::__("Deseja publicar os resultados?")?></h3>
                                <p class="message"><strong>
                                    <?= i::__("Antes de publicar os resultados, verifique cuidadosamente se todas as inscrições foram avaliadas e aplicadas.") ?></strong>
                                    <?= i::__("Com essa ação o resultado da fase ficará público.")?>
                                </p>
                            </template> 
                        </mc-confirm-button>
                    </div>
                </div>
                <div v-if="phase.publishedRegistrations && (!phase.isContinuousFlow || (phase.isContinuousFlow && phase.hasEndDate))" class="published">
                    <div class="col-4">
                        <mc-confirm-button :message="text('despublicar')" @confirm="unpublishRegistration()">
                            <template #button="modal">
                                <button class="button button--primary-outline" @click="modal.open()">
                                    <?= i::__("Despublicar") ?>
                                </button>
                            </template>
                        </mc-confirm-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>