<?php

use MapasCulturais\i;
$this->import('
error-display
');
?>


<div class="error-404">
    <div class="content">
        <div class="left">
            <h1 class="left__title"><?= i::__('Erro 404') ?></h1>
            <label class="left__message"><?= i::__('Acesso Privado') ?></label>
            <label class="left__content"><?= i::__(' A página que você está tentando acessar é particular. 
            Faça login com outra conta ou solicite acesso.') ?></label>
        </div>
        <div class="right">
            <img src="<?php $this->asset('/img/error403.png', true, true) ?>">
        </div>
    </div>
    <div class="btn">
        <button class="button button--primary btn-error"><label class="btn__label"></label><?= i::__('Voltar para a página inicial') ?></label></button>
    </div>
</div>