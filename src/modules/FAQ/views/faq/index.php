<?php

use MapasCulturais\App;
use MapasCulturais\i;

$this->import('
    mc-title
    mc-icon
');
// var_dump($faq);
?>

<main class="faq__main">
    <div class="faq__suggestions">
        <h4 class="bold faq__principal"><?php i::_e('Principais sugestões') ?></h4>
        <div class="faq__frequent bold">
            <?php foreach ($faq as $section) : ?>

                <a href="<?= $this->controller->createUrl('index', [$section->slug]) ?>" class="faq__card bold faq__card--frequent primary__color">
                    <mc-icon name="help-outline" class="faq__helper"></mc-icon>
                    <?= $section->title ?>
                </a>
            <?php endforeach; ?>

        </div>

    </div>
    <div class="faq__listed">
        <p class="semibold faq__msgtitle"><?php i::_e('Confira todas as dúvidas mais frequentes agrupadas em categorias disponíveis para que você possa consultar.') ?></p>
        <div class="faq__links">
            <?php foreach ($faq as $section) : ?>
                <a href="<?= $this->controller->createUrl('index', [$section->slug]) ?>" class="faq__card bold primary__color">
                    <?= $section->title ?> <mc-icon name="arrow-right-ios"></mc-icon>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</main>