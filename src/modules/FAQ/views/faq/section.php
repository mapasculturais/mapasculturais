<?php

use MapasCulturais\App;
use MapasCulturais\i;

$this->import('
    faq-accordion
    mc-title
');

?>
<a href="<?= $this->controller->createUrl('index'); ?>" class="primary__color faq__back"><mc-icon name="arrow-left-ios"></mc-icon><mc-title tag="p" class="bold"><?= i::__('Voltar') ?></mc-title></a>
<div class="faq__content">
    <aside class="faq__aside">
        <?php foreach ($faq as $section) : ?>
            <a href="<?= $this->controller->createUrl('index', [$section->slug]) ?>" class="faq__btn <?= $section->slug == $active_section ? 'faq__btn--selected' : '' ?>">
                <?= $section->title ?> <mc-icon name="arrow-right-ios"></mc-icon>
            </a>
        <?php endforeach; ?>
        <div class="field faq__sections">
            <select name="faq-sections">
                <option data-default disabled selected hidden><p class="semibold"><?= i::__("NAVEGUE ENTRE AS CATEGORIAS"); ?></p></option>
                <?php foreach ($faq as $section) : ?>
                    <option value="<?= $this->controller->createUrl('index', [$section->slug]) ?>" class="faq__btn <?= $section->slug == $active_section ? 'faq__btn--selected' : '' ?>">
                        <?= $section->title ?> <mc-icon name="arrow-right-ios"></mc-icon>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </aside>
    <main>
        <faq-accordion></faq-accordion>
    </main>
</div>