<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-chat
');

?>

<mc-chat v-if="thread" :thread="thread" anonymous-sender="<?= i::__('Avaliador') ?>">
    <template #message-payload="{ message }">
        <div class="mc-chat__reviewer-message field" :let="initMessage(message)">
            <label for="status"><?= i::__('Resultado da validação:') ?></label>
            <mc-select :default-value="message.payload.status" @change-option="message.payload.status = $event.value" id="status">
                <div v-for="(label, value) in statusList" :key="value" :value="value">
                    <mc-icon name="circle" :class="verifyState(value)"></mc-icon>
                    {{ label }}
                </div>
            </mc-select>
            
            <label for="agent-response"><?= i::__('Justificativa:') ?></label>
            <textarea 
                v-model="message.payload.message" 
                ref="textarea" 
                placeholder="<?= i::__('Digite sua mensagem') ?>" 
                id="agent-response" 
                class="mc-chat__textarea">
            </textarea>

            <div class="mc-chat__endChat field">
                <input type="checkbox" v-model="message.payload.endChat" id="endChat"></input>
                <label for="endChat"><?= i::__('Encerrar processo') ?></label>
            </div>
    
            <textarea 
                v-if="message.payload.endChat"
                v-model="message.payload.justification" 
                ref="textarea" 
                placeholder="<?= i::__('Digite sua justificativa para encerrar o processo') ?>" 
                id="endChat-response" 
                class="mc-chat__textarea">
            </textarea>
        </div>
    </template>
    <template #default="{ message }">
        <div v-if="typeof message.payload === 'string'">
            <p>{{ message.payload }}</p>
        </div>

        <div v-if="typeof message.payload === 'object'">
            <div class="mc-chat__evaluation">
                <div class="mc-chat__evaluation-status">
                    <h4 class="semibold"><?= i::__('Resultado da validação:') ?></h4>
                    <p>{{message.payload.status}}</p>
                </div>
                <div class="mc-chat__text">
                    <h4 class="semibold"><?= i::__('Justificativa ou observações:') ?></h4>
                    <p>{{message.payload.message}}</p>
                </div>
                <div class="mc-chat__evaluation-closed" v-if="message.payload.endChat">
                    <h4 class="semibold"><?= i::__('Justificativa para encerramento:') ?></h4>
                    <p>{{message.payload.justification}}</p>
                </div>
            </div>
        </div>
</template>
</mc-chat>