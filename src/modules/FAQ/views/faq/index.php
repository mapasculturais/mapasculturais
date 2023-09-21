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
    <div class="faq__frequent">
        <?php foreach ($faq as $section) : ?>
            <a href="<?= $this->controller->createUrl('index', [$section->slug]) ?>" class="faq__card bold faq__card--frequent primary__color">
                <?= $section->title ?> <mc-icon name="arrow-right-ios"></mc-icon>
            </a>
        <?php endforeach; ?>

        <!-- 
        <a class="faq__card-frequent-card">
            <mc-title tag="p" class="primary__color">teste</mc-title>
        </a> -->
    </div>

    <div class="faq__links">
        <?php foreach ($faq as $section) : ?>
            <a href="<?= $this->controller->createUrl('index', [$section->slug]) ?>" class="faq__card bold primary__color">
                <?= $section->title ?> <mc-icon name="arrow-right-ios"></mc-icon>
            </a>
        <?php endforeach; ?>
    </div>
</main>