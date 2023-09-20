<?php

use MapasCulturais\App;
use MapasCulturais\i;

$this->import('
    faq-accordion
');
?>
<aside class="faq__aside">
    <?php foreach($faq as $section): ?>
        <a href="<?=$this->controller->createUrl('index', [$section->slug])?>" class="faq__btn <?= $section->slug == $active_section ? 'faq__btn--selected' : '' ?>">
            <?=$section->title?> <mc-icon name="arrow-right-ios"></mc-icon>
        </a>
    <?php endforeach; ?>

</aside>
<main>
    <faq-accordion></faq-accordion>
</main>