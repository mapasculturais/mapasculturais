<?php
/**
 * Partial para campos de anexo do agente (type=file) usados como campo "@"
 * (agent-owner-field / agent-collective-field) no formulário de inscrição.
 *
 * O valor de entity[fieldName] é fornecido por fetchFromEntity() (Module.php):
 *   - { id, name, url, mimeType } quando há arquivo no FileGroup do agente;
 *   - null quando não há arquivo.
 *
 * Os anexos são gerenciados (upload/remoção) no perfil do agente; nesta tela
 * apenas exibimos o arquivo existente para conferência do proponente.
 */
?>
<div ng-if="entity[fieldName] && entity[fieldName].url" class="attachment-title">
    <a href="{{entity[fieldName].url}}" target="_blank" rel="noopener noreferrer">{{entity[fieldName].name}}</a>
</div>
<div ng-if="!entity[fieldName] || !entity[fieldName].url" class="attachment-description">
    <em><?php \MapasCulturais\i::_e('Nenhum arquivo anexado. Envie o documento pelo seu perfil de agente.'); ?></em>
</div>
