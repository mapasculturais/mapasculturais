<?php

use MapasCulturais\i;

$this->import('
    mc-link
');
?>


<div class="error-page error-403">
    <div class="error-card">
        <div class="content">
            <div class="left">
                <h1 class="left__title"><?= i::__('Erro 403') ?></h1>
                <label class="left__message"><?= i::__('Acesso Privado') ?></label>
                <div class="left__content">
                    <label><?= i::__(' A página que você está tentando acessar é particular.') ?></label>
                    <label> <br><?= i::__('Faça login com outra conta ou solicite acesso.') ?></br></label>
                </div>
                <div class="error-footer">
                    <div class="btn">
                        <mc-link route="panel/index" class="button button--primary btn-error"><label class="btn__label"><?= i::__('Voltar para a página inicial') ?></label></mc-link>
                    </div>
                </div>
            </div>
            <div class="right">
                <img src="<?php $this->asset('/img/error403.png', true, true) ?>">
            </div>
        </div>
    </div>

</div>