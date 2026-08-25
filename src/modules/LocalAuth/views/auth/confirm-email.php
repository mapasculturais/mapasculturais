<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * 
 */

use MapasCulturais\i;

$this->import('
    mc-card
');
?>

<div class="confirm-email">
    <mc-card class="no-title">
        <template #content>
            <div class="grid-12">
                <div class="col-12 header">
                    <label class="header__title"> <?= i::__('Alteração de senha') ?> </label>
                    <mc-icon name="circle-checked" class="header__icon"></mc-icon>
                    <label class="header__label"> <?= i::__('Enviamos as instruções de alteração de senha para seu e-mail.') ?> </label>
                </div>

                <a class="col-12 button button--primary button--large button--md" href="<?= $app->createUrl('auth') ?>" type="submit"> <?= i::__('Entrar na minha conta') ?> </a>
            </div>
        </template>
    </mc-card>
</div>