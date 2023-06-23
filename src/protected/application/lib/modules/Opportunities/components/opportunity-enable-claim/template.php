<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
mc-link
');
?>

<div class="opportunity-enable-claim">
    <h4 class="bold opportunity-enable-claim__title">Recurso</h4>
    <div class="opportunity-enable-claim__input ">

        <input type="checkbox" id="resource" />
        <label for="resource"><?= i::__("Habilitar Recurso") ?></label>
    </div>
    <div class="opportunity-enable-claim__email">
        <label for="input">
            <h5 clas="semibold opportunity-enable-claim__subtitle"><?= i::__("Insira o email que receberá as solicitações") ?></h5>
        </label>
        <div class="opportunity-enable-claim__save">
            <input type="text" #id="input" /> <button class="button-popover button button--primary button--primary-outline"><?= i::__("Salvar") ?></button>
        </div>

    </div>
</div>