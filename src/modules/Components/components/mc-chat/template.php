<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-file
    mc-avatar
    mc-entities
    mc-icon
');
?>

<div class="mc-chat">
    <header :v-if="thread?.description && thread.description.trim()" class="mc-chat__header">
        <h2 class="mc-chat__title">
            <span class="mc-chat__report-name">{{thread.description}}</span>
        </h2>
    </header>

    <main class="mc-chat__content" style="display: flex; flex-direction: column-reverse;">
        <mc-entities
            v-if="query"
            ref="chatMessages"
            type="chatmessage"
            :query="query"
            select="createTimestamp,payload,user.profile.{name,files.avatar},files" 
            order="createTimestamp DESC"
            :limit="5">
            <template #empty>
                <div class="mc-chat__empty">
                </div>
            </template>
            <template #default="{ entities }">
                <template v-for="message in entities">
                    <template v-if="entities.length" v-once>
                        {{ handleEntitiesUpdate(entities) }}
                    </template>
                    <slot :message="message" :sender-name="senderName(message)">
                        <slot v-if="isMine(message) && message.payload != '@attachment'" name="my-message" :message="message" :sender-name="senderName(message)">
                            <article
                                class="mc-chat__message mc-chat__owner"
                                :key="message.id"
                                >
                                <div class="mc-chat__avatar">
                                    <mc-avatar :entity="message.user.profile" size="small"></mc-avatar>
                                </div>
                                <div class="mc-chat__details">
                                    <div class="mc-chat__metadata">
                                        <span class="mc-chat__name">
                                            {{ senderName(message) }}
                                        </span>
                                        <span class="mc-chat__timestamp">{{ message.createTimestamp?.date('numeric year') }} - {{ message.createTimestamp?.time() }}</span>
                                    </div>
                                    <div class="mc-chat__text">
                                        <p>{{ message.payload }}</p>
                                    </div>
                                </div>
                            </article>
                        </slot>
                        <slot v-if="isMine(message) && message.payload == '@attachment'" name="my-attachment" :message="message" :sender-name="senderName(message)">
                            <article
                                class="mc-chat__message mc-chat__owner"
                                :key="message.id">
                                <div class="mc-chat__avatar">
                                    <mc-avatar :entity="message.user.profile" size="small"></mc-avatar>
                                </div>
                                <div class="mc-chat__details">
                                    <div class="mc-chat__metadata">
                                        <span class="mc-chat__name">
                                            {{ senderName(message) }}
                                        </span>
                                        <span class="mc-chat__timestamp">{{ message.createTimestamp?.date('numeric year') }} - {{ message.createTimestamp?.time() }}</span>
                                    </div>
                                    <div class="mc-chat__attachment">
                                        <entity-file
                                            :entity="message"
                                            group-name="chatAttachment"
                                            classes="col-12"
                                            ></entity-file>
                                    </div>
                                </div>
                            </article>
                        </slot>

                        <slot v-if="!isMine(message) && message.payload != '@attachment'" name="other-message" :message="message" :sender-name="senderName(message)">
                            <article
                                class="mc-chat__message"
                                :key="message.id"
                                >
                                <div class="mc-chat__avatar">
                                    <mc-avatar :entity="message.user.profile" size="small"></mc-avatar>
                                </div>
                                <div class="mc-chat__details">
                                    <div class="mc-chat__metadata">
                                        <span class="mc-chat__name">
                                            {{ senderName(message) }}
                                        </span>
                                        <span class="mc-chat__timestamp">{{ message.createTimestamp?.date('numeric year') }} - {{ message.createTimestamp?.time() }}</span>
                                    </div>
                                    <div class="mc-chat__text">
                                        <p>{{ message.payload }}</p>
                                    </div>
                                </div>
                            </article>
                        </slot>
                        <slot v-if="!isMine(message) && message.payload == '@attachment'" :message="message" :sender-name="senderName(message)">
                            <article
                                class="mc-chat__message"
                                :key="message.id">
                                <div class="mc-chat__avatar">
                                    <mc-avatar :entity="message.user.profile" size="small"></mc-avatar>
                                </div>
                                <div class="mc-chat__details">
                                    <div class="mc-chat__metadata">
                                        <span class="mc-chat__name">
                                            {{ senderName(message) }}
                                        </span>
                                        <span class="mc-chat__timestamp">{{ message.createTimestamp?.date('numeric year') }} - {{ message.createTimestamp?.time() }}</span>
                                    </div>
                                    <div class="mc-chat__attachment">
                                        <entity-file
                                            :entity="message"
                                            group-name="chatAttachment"
                                            classes="col-12"
                                            ></entity-file>
                                    </div>
                                </div>
                            </article>
                        </slot>
                    </slot>
                </template>
            </template>
        </mc-entities>
    </main>

    <div v-if="!isClosed() && (!pingPong || (pingPong && !lastMessageIsMine))" class="mc-chat__actions">
        <slot name="message-form"
            :message="message"
            :send-message="sendMessage"
            :processing="processing"
            >
            <slot name="message-payload"
                :message="message"
                :lastMessageIsMine="lastMessageIsMine"
                >
                <textarea 
                    v-model="message.payload" 
                    ref="textarea" 
                    placeholder="<?= i::__('Digite sua mensagem') ?>" 
                    id="agent-response" 
                    class="mc-chat__textarea">
                </textarea>
            </slot>
    
            <slot name="message-upload" 
                :message="message"
                >
                <entity-file
                    ref="attachment"
                    :entity="message"
                    group-name="chatAttachment"
                    title-modal="<?php i::_e('Adicionar anexo') ?>"
                    classes="col-12"
                    title="<?php i::esc_attr_e('Adicionar anexo'); ?>"
                    editable
                    :upload-on-submit="false"></entity-file>
            </slot>

            <slot name="message-send-button"
                :send-message="sendMessage"
                :processing="processing"
                >
                <button type="button" class="button button--primary" @click="sendMessage" :disabled="processing"><?= i::__('Enviar mensagem') ?></button>
            </slot>
        </slot>
    </div>
</div>
