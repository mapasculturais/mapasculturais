<?php
/**
 * Este arquivo está incluso no componente opportunity-phase-config-evaluation
 * 
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import("
    qualification-evaluation-config
");
?>
<section class="col-12 evaluation-step__section">
    <div class="evaluation-step__section-header">
        <div class="evaluation-step__section-label">
            <h3><?= i::__('Configuração da avaliação') ?></h3>
        </div>
    </div>

    <div class="evaluation-step__section-content">
        <qualification-evaluation-config :entity="phase"></qualification-evaluation-config>
    </div>
</section>