<?php

use MapasCulturais\App;
use MapasCulturais\i;

$this->import('
    faq-accordion
    faq-search-results
    mc-title
');

?>
<a href="<?= $this->controller->createUrl('index'); ?>" class="primary__color faq__back"><mc-icon name="arrow-left-ios"></mc-icon><mc-title tag="p" class="bold"><?= i::__('Voltar') ?></mc-title></a>
<div class="faq__content">
    <aside class="faq__aside">
        <div class="faq__btn-aside">
            <?php foreach ($faq as $section) : ?>
                <a href="<?= $this->controller->createUrl('index', [$section->slug]) ?>" class="faq__btn <?= $section->slug == $active_section ? 'faq__btn--selected' : '' ?>">
                    <?= $section->title ?> <mc-icon name="arrow-right-ios"></mc-icon>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="field faq__sections">
            <p for="faq-sections" class="semibold"><?= i::__("Navegue entra as categorias:"); ?></p>
            <select id="faq-sections" name="faq-sections" onchange="location=this.value;" class="primary__color bold">
                <?php foreach ($faq as $section) : ?>
                    <option value="<?= $this->controller->createUrl('index', [$section->slug]) ?>" <?= $section->slug == $active_section ? 'selected' : '' ?> class="semibold">
                        <?= $section->title ?> <mc-icon name="arrow-right-ios"></mc-icon>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </aside>
    <main>
        <faq-accordion v-if="!global.faqSearch"></faq-accordion>
        <h3 v-if="global.faqSearch" class="bold"><?= i::__('Resultados da pesquisa')?> '{{global.faqSearch}}'</h3>
        <faq-search-results v-if="global.faqSearch"></faq-search-results>
    </main>
</div>