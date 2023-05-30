<?php

use MapasCulturais\i;

?>

<div class="error-page error-404">
    <div class="error-card">
        <div class="content">
            <div class="left">
                <h1 class="left__title"><?= i::__('Erro 404') ?></h1>
                <label class="left__warning"><?= i::__(' Ops, página não encontrada.') ?></label>
            </div>
            <div class="right">
                <img src="<?php $this->asset('/img/404.png', true, true) ?>">
            </div>

        </div>
        <div class="error-footer">
            <div class="message-error">
                <label class="text"><br><?= i::__('Essa página não existe mais ou mudou de endereço.') ?></br></label>
                <label class="text">
                    <?= i::__('Mas não se preocupe, existem muitas outras páginas para conhecer aqui no Mapas.') ?></label>
                </label>
            </div>
            <div class="btn">

                <button class="button button--primary btn-error"><label class="btn__label"></label><?= i::__('Voltar para a página inicial') ?></label></button>
            </div>
        </div>
    </div>

</div>