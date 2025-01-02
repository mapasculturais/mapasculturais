<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-icon
');
?>

<component :is="tag" class="mc-chat">
    <header class="mc-chat__header">
        <h2 class="mc-chat__title">
            <?= i::__('Responder validação do relatório') ?>
            <span class="mc-chat__report-name">{{entity.name}}</span>
        </h2>
        <button class="mc-chat__close-btn" @click="closeModal">
            <mc-icon name="close"></mc-icon> 
            <span class="sr-only"><?= i::__('Fechar') ?></span>
        </button>
    </header>

    <main class="mc-chat__content">
        <section class="mc-chat__message">
            <div class="mc-chat__message-header">
                <h2 class="mc-chat__title"><?= i::__('Resultado da validação') ?></h2>
            </div>
            <p class="mc-chat__message-text"><?= i::__('Aprovado com ressalvas') ?></p>
        </section>

        <section class="mc-chat__message">
            <div class="mc-chat__message-header">
                <h2 class="mc-chat__title"><?= i::__('Justificativa ou observações') ?></h2>
            </div>
            <p class="mc-chat__message-text">
            </p>
            <time class="mc-chat__message-time"></time>
            <a href="#" class="mc-chat__message-attachment"></a>
        </section>

        <form class="mc-chat__form">
            <div class="mc-chat__form-group">
                <label for="agent-response" class="mc-chat__label">
                <?= i::__('Resposta do agente') ?> <span class="mc-chat__required">*</span>
                </label>
                <textarea id="agent-response" class="mc-chat__textarea"></textarea>
            </div>
            <div class="mc-chat__form-actions">
                <button type="button" class="mc-chat__add-doc-btn"><?= i::__('Adicionar documento') ?></button>
                <div class="mc-chat__action-buttons">
                    <button type="button" class="mc-chat__close-btn"><?= i::__('Fechar') ?></button>
                    <button type="submit" class="mc-chat__submit-btn"><?= i::__('Responder') ?></button>
                </div>
            </div>
        </form>
    </main>
</component>
