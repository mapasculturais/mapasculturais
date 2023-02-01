<?php
use MapasCulturais\i;
//$this->import('');
?>

<div class="grid-12">
    <div class="col-12">
        <p class="controller-opportunity__period--title">
          <?= i::__("Período de inscrições") ?>
        </p>
        <div class="controller-opportunity__period--content">
            <p class="controller-opportunity__period--description">
              <?= i::__("Inscrições abertas de") ?> {{ dateStart }} <?= i::__(" a "); ?> {{ dateEnd }}
            </p>
        </div>
    </div>
    <div class="col-12">
        <p class="controller-opportunity__subscription--title">
          <?= i::__("Inscreva-se") ?>
        </p>
        <p class="controller-opportunity__subscription--description">
          <?= i::__("Você precisa acessar sua conta ou  criar uma cadastro na plataforma para poder se inscrever em editais ou oportunidades") ?>
        </p>
        <button class="controller-opportunity__full button button--primary w-100">
          <?= i::__("Fazer inscrição") ?>
        </button>
    </div>
</div>