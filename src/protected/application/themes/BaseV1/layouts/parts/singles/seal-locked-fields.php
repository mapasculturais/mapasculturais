<?php

use MapasCulturais\i;

$props = $this->getLockedFieldsSeal();
?>
<div id="locked-fields">
    <p class="alert info"><?= i::__('Selecione abaixo os campos que devem ser bloqueados nos agentes e espaços que possuírem este selo') ?> </p>
    <input class="js-editable " style="display:none;" id="locked-fields-input" data-value="[]" type="hidden" data-edit="lockedFields" />
    <form class="js-locked-fields">
        <div class="fields">
            <h2><?php $this->dict('entities: Agents') ?></h2>
            <?php foreach ($props['agent'] as  $field => $values) : ?>
                <div>
                    <label>
                        <input type='checkbox' name='lockedFields[]' value="agent.<?= $field ?>" <?= in_array("agent.{$field}", $entity->lockedFields) ?  "checked" : "" ?>>
                        <?= $values['label'] ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="fields">
            <h2><?php $this->dict('entities: Spaces') ?></h2>
            <?php foreach ($props['space'] as  $field => $values) : ?>
                <div>
                    <label>
                        <input type='checkbox' name='lockedFields[]' value="space.<?= $field ?>" <?= in_array("space.{$field}", $entity->lockedFields) ?  "checked" : "" ?>>
                        <?= $values['label'] ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
    </form>
</div>