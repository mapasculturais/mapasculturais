<?php
/** 
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV1\Theme $this 
 */

use MapasCulturais\i;

$props = $this->getLockedFieldsSeal();
$agent_taxonomies = $app->getRegisteredTaxonomies(MapasCulturais\Entities\Agent::class);
$space_taxonomies = $app->getRegisteredTaxonomies(MapasCulturais\Entities\Space::class);

?>
<div id="locked-fields">
    <p class="alert info"><?= i::__('Selecione abaixo os campos que devem ser bloqueados nos agentes e espaços que possuírem este selo') ?> </p>
    <input class="js-editable " style="display:none;" id="locked-fields-input" data-value="[]" type="hidden" data-edit="lockedFields" />
    <form class="js-locked-fields">
        <div class="fields">
            <h2><?php $this->dict('entities: Agents') ?></h2>
            <?php foreach ($props['agent'] as  $field => $values) : $field = $values['@select'] ?? $field; ?>
                <div>
                    <label>
                        <input type='checkbox' name='lockedFields[]' value="agent.<?= $field ?>" <?= in_array("agent.{$field}", $entity->lockedFields) ?  "checked" : "" ?>>
                        <?= $values['label'] ?>
                    </label>
                </div>
            <?php endforeach; ?>
            <h3><?= i::__('Taxonomias') ?></h3>
            <?php foreach($agent_taxonomies as $slug => $def): ?>
                <div>
                    <label>
                        <input type='checkbox' name='lockedFields[]' value="agent.terms:<?= $slug ?>" <?= in_array("agent.terms:{$slug}", $entity->lockedFields) ?  "checked" : "" ?>>
                        <?= $def->description ?: $slug ?>
                    </label>
                </div>
            <?php endforeach ?>
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
            <h3><?= i::__('Taxonomias') ?></h3>
            <?php foreach($space_taxonomies as $slug => $def): ?>
                <div>
                    <label>
                        <input type='checkbox' name='lockedFields[]' value="space.terms:<?= $slug ?>" <?= in_array("agent.terms:{$slug}", $entity->lockedFields) ?  "checked" : "" ?>>
                        <?= $def->description ?: $slug ?>
                    </label>
                </div>
            <?php endforeach ?>
        </div>
    </form>
</div>