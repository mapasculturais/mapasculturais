<?php

use MapasCulturais\i;

$this->layout = "default";

$this->import('
    mc-link
');
?>


<div class="error-page">
    <div class="error-card">
        <div class="content">
            <div class="left">
                <h1 class="left__title"><?= i::__('Erro 500') ?></h1>
                <label class="left__message">Erro na página</label>
                <div class="error-footer">
                    <div class="message-error">
                        <label class="text"><br><?= i::__('Identificamos um problema no servidor aqui do nosso lado, mas não se preocupe. ') ?></br></label>
                        <label class="text">
                            <?= i::__('Já estamos investigando o que aconteceu. Tente novamente após alguns minutos..') ?></label>
                        </label>
                    </div>
                    <div class="btn">
                        <mc-link route="panel/index" class="button button--primary btn-error"><label class="btn__label"><?= i::__('Voltar para a página inicial') ?></label></mc-link>
                    </div>
                </div>
            </div>
            <div class="right">
                <img src="<?php $this->asset('/img/error-500.png', true, true) ?>">
            </div>

        </div>
        <?php if($display_details): ?>
            <pre style="font-size: 12px;"><code><?= $exception ?? '' ?></code>
        <?php endif ?>
        </pre>
    </div>

</div>