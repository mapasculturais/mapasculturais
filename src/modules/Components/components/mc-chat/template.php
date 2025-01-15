<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
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
            select="createTimestamp,payload,user.profile.{name,files.avatar}" 
            order="createTimestamp DESC"
            :limit="5">
            <template #default="{ entities }">
                <template v-for="message in entities">
                    <slot :message="message" :sender-name="senderName(message)">
                        <slot v-if="isMine(message)" name="my-message" :message="message" :sender-name="senderName(message)">
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
                        <slot v-if="!isMine(message)" name="other-message" :message="message" :sender-name="senderName(message)">
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
                    </slot>
                </template>
            </template>
        </mc-entities>
    </main>

    <div class="mc-chat__actions">
        <textarea 
            v-model="message" 
            ref="textarea" 
            placeholder="<?= i::__('Digite sua mensagem') ?>" 
            id="agent-response" 
            class="mc-chat__textarea">
        </textarea>

        <button type="button" class="button button--primary" @click="sendMessage" :disabled="processing"><?= i::__('Responder') ?></button>
    </div>
</div>
