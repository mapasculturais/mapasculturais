<?php
$props = $this->getLockedFieldsSeal();
?>

<div id="seal-config">
    <span class="js-editable " style="display:none;" id="locked-fields" type="text" data-edit="lockedFields"></span>
    <form class="js-locked-fields">
        <div class="fields">
            <h2> Agente </h2>
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
            <h2> Espaço</h2>
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