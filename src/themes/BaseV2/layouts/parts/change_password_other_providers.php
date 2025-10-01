<?php

use MapasCulturais\i;

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('mc-icon');
?>

<div class="change-password">
    <div class="change_password_other_providers">
        <div class="change_password_other_providers--fakePassword">
            <div v-for="n in 12" class="dot"></div>
        </div>
        
        <a href="<?= $change_password_url ?>" target="_blank"><mc-icon name="edit"></mc-icon> <?= i::__('Alterar a senha') ?></a>
    </div>
</div>