<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
?>
<div class="grid-12">
    <div class="col-12">
        <p class="opportunity__period--title">
          <?= i::__("Período de inscrições") ?>
        </p>
        <div class="opportunity__period--content">
            <p class="opportunity__period--description">
              <?= i::__("Inscrições abertas de 05/03/2022 a 21/03/2022 às 12:00") ?>
            </p>
        </div>
    </div>
    <div class="col-12">
        <p class="opportunity__subscription--title">
          <?= i::__("Inscreva-se") ?>
        </p>
        <p class="opportunity__subscription--description">
          <?= i::__("Você precisa acessar sua conta ou  criar uma cadastro na plataforma para poder se inscrever em editais ou oportunidades") ?>
        </p>
        <button class="button button--primary">
          <?= i::__("Fazer inscrição") ?>
        </button>
    </div>
</div>