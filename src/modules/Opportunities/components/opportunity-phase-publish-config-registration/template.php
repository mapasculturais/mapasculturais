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
                <?= i::__('Fique atento! A publicação do resultado é opcional. <strong>Esta ação deixará público o nome e o número de inscrição das pessoas inscritas.</strong>') ?>
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
                        <div v-if="!isOpenPhase && !phase.publishedRegistrations" class="col-4">
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
                        <div v-if="phase.publishedRegistrations && (!firstPhase.isContinuousFlow || (firstPhase.isContinuousFlow && firstPhase.hasEndDate))" class="msg-auto-pub col-4">
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
                            <p v-if="phase.publishedRegistrations && (!firstPhase.isContinuousFlow || (firstPhase.isContinuousFlow && firstPhase.hasEndDate))"class="bold"><?= i::__("A publicação do resultado é opcional.") ?></p>
                        </div>
                </div>
            </div>
            <!-- R02/#65 (CA-14/CA-15 + override #7 2026-09-22): ações de publicação em
                 dois estágios, lado a lado e centralizadas, na largura plena do bloco
                 (col-12; fora da coluna col-6/__unpublishedlast do layout original).
                 Slot preliminar: "Publicar resultado preliminar" dá lugar ao
                 "Despublicar resultado preliminar" (mutuamente exclusivos); o
                 "Publicar resultado final" (com selos) NÃO é mais sempre disponível
                 (override #7): só com o final não publicado. Guard de instância
                 (mainPhaseOnly/isAppealPhase) mantido em todos os botões de final.
                 Na fase de recurso este bloco não renderiza (ver bloco seguinte). -->
            <div v-if="!isAppealInstance && (!phase.publishedRegistrations || showFinalPublishButton)" class="col-12 opportunity-phase-publish-config-registration__actions">
                <div v-if="!phase.publishedRegistrations">
                    <mc-confirm-button v-if="!phase.publishedPreliminaryRegistrations" yes="<?= i::__('Publicar resultado preliminar')?>" @confirm="publishPreliminaryRegistration()">
                        <template #button="modal">
                            <button :class="['button', 'button--primary', {'button--bg': phase.isLastPhase}]" @click="modal.open()">
                                <?= i::__("Publicar resultado preliminar") ?>
                            </button>
                        </template>
                        <template #message="message">
                            <h3 class="bold"><?= i::__("Deseja publicar o resultado preliminar?")?></h3>
                            <p class="message"><strong>
                                <?= i::__("Antes de publicar o resultado preliminar, verifique cuidadosamente se todas as inscrições foram avaliadas e aplicadas.") ?></strong>
                                <?= i::__("Com essa ação o resultado <strong>preliminar</strong> da fase ficará público, sem aplicar os selos.")?>
                            </p>
                        </template>
                    </mc-confirm-button>
                    <mc-confirm-button v-else :message="text('despublicar_preliminar')" @confirm="unpublishPreliminaryRegistration()">
                        <template #button="modal">
                            <button :class="['button', 'button--primary-outline']" @click="modal.open()">
                                <?= i::__("Despublicar resultado preliminar") ?>
                            </button>
                        </template>
                    </mc-confirm-button>
                </div>
                <!-- R02 (#72): publicar final somente após o término da fase (phaseEnded; sem registrationTo → true) -->
                <div v-if="!phase.publishedRegistrations && showFinalPublishButton && phaseEnded">
                    <mc-confirm-button yes="<?= i::__('Publicar resultado final')?>" @confirm="publishRegistration()">
                        <template #button="modal">
                            <button :class="['button', 'button--primary', {'button--bg': phase.isLastPhase}]" @click="modal.open()">
                                <?= i::__("Publicar resultado final") ?>
                            </button>
                        </template>
                        <template #message="message">
                            <h3 class="bold"><?= i::__("Deseja publicar o resultado final?")?></h3>
                            <p class="message"><strong>
                                <?= i::__("Antes de publicar o resultado final, verifique cuidadosamente se todas as inscrições foram avaliadas e aplicadas.") ?></strong>
                                <?= i::__("Com essa ação o resultado final da fase ficará público e os selos serão aplicados às inscrições selecionadas.")?>
                            </p>
                            <p class="message">
                                <?= i::__("Atenção: se o resultado final já estiver publicado, ele será <strong>publicado novamente</strong> e os selos serão <strong>reaplicados</strong>, sobrescrevendo ajustes de selos feitos manualmente após a publicação.")?>
                            </p>
                            <mc-alert v-if="appealPhaseUnpublished" type="warning">
                                <?= i::__("<strong>O resultado da fase de recurso ainda não foi publicado.</strong> Ao publicar o resultado final, as notas alteradas por correções de recurso ficarão visíveis ao proponente, mas a resposta do recurso (deferimento e parecer) continuará oculta até que o resultado seja publicado na seção \"Resposta do recurso\".")?>
                            </mc-alert>
                        </template>
                    </mc-confirm-button>
                </div>
                <!-- R02/#65: o "Despublicar" do resultado FINAL entra no container de
                     ações (lado a lado com o "Publicar resultado final"). Condição
                     original + guard D3 pelo v-if do próprio container (a instância
                     da fase de recurso não renderiza este container com pr=true). -->
                <div v-if="phase.publishedRegistrations && (!firstPhase?.isContinuousFlow || (firstPhase?.isContinuousFlow && firstPhase?.hasEndDate))">
                    <mc-confirm-button yes="<?= i::__('Despublicar resultado final')?>" :message="text('despublicar_final')" @confirm="unpublishRegistration()">
                        <template #button="modal">
                            <button :class="['button', 'button--primary-outline']" @click="modal.open()">
                                <?= i::__("Despublicar resultado final") ?>
                            </button>
                        </template>
                    </mc-confirm-button>
                </div>
            </div>
            <!--
                R03 (#76): fase de recurso — estágio único (sem preliminar).
                Mesma ação do publicar/despublicar final (POST publishRegistrations
                / unpublishRegistrations, com selos), copy próprio. Gate phaseEnded
                (#72) no publicar; despublicar sempre que já publicado.
            -->
            <div v-if="isAppealInstance && (phaseEnded || phase.publishedRegistrations)" class="col-12 opportunity-phase-publish-config-registration__actions">
                <div v-if="!phase.publishedRegistrations && phaseEnded">
                    <mc-confirm-button yes="<?= i::__('Publicar resultado do recurso')?>" @confirm="publishAppealRegistration()">
                        <template #button="modal">
                            <button :class="['button', 'button--primary', {'button--bg': phase.isLastPhase}]" @click="modal.open()">
                                <?= i::__("Publicar resultado do recurso") ?>
                            </button>
                        </template>
                        <template #message="message">
                            <h3 class="bold"><?= i::__("Deseja publicar o resultado do recurso?")?></h3>
                            <p class="message"><strong>
                                <?= i::__("Antes de publicar o resultado do recurso, verifique cuidadosamente se todos os recursos foram avaliados e aplicados.") ?></strong>
                                <?= i::__("Com essa ação o resultado do recurso ficará público e os selos serão aplicados às inscrições selecionadas.")?>
                            </p>
                            <p class="message">
                                <?= i::__("Atenção: se o resultado do recurso já estiver publicado, ele será <strong>publicado novamente</strong> e os selos serão <strong>reaplicados</strong>, sobrescrevendo ajustes de selos feitos manualmente após a publicação.")?>
                            </p>
                        </template>
                    </mc-confirm-button>
                </div>
                <div v-if="phase.publishedRegistrations">
                    <mc-confirm-button yes="<?= i::__('Despublicar resultado do recurso')?>" :message="text('despublicar_recurso')" @confirm="unpublishAppealRegistration()">
                        <template #button="modal">
                            <button class="button button--primary-outline" @click="modal.open()">
                                <?= i::__("Despublicar resultado do recurso") ?>
                            </button>
                        </template>
                    </mc-confirm-button>
                </div>
            </div>
        </div>
    </div>
</div>