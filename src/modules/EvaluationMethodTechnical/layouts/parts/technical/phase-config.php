<?php

/**
 * Este arquivo está incluso no componente opportunity-phase-config-evaluation
 * 
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import("
    affirmative-policies--geo-quota-configuration
    affirmative-policies--quota-configuration
    affirmative-policy--bonus-config
    entity-field
    mc-icon
    technical-assessment-section
    tiebreaker-criteria-configuration
");
?>
<section class="col-12 evaluation-step__section">
    <div class="evaluation-step__section-header">
        <div class="evaluation-step__section-label">
            <h3><?= i::__('Configuração da avaliação') ?></h3>
        </div>
    </div>

    <div class="evaluation-step__section-content">
        <technical-assessment-section :entity="phase"></technical-assessment-section>
        <entity-field :entity="phase" prop="enableViability" :autosave="3000"></entity-field>
        <tiebreaker-criteria-configuration :phase="phase"></tiebreaker-criteria-configuration>
    </div>
</section>

<section class="col-12 evaluation-step__section">
    <div class="evaluation-step__section-header">
        <div class="evaluation-step__section-label">
            <h3><?= i::__('Políticas Afirmativas') ?></h3>
        </div>
    </div>

    <div class="col-12 evaluation-step__section-content">
        <entity-field :entity="firstPhase" prop="enableQuotasQuestion" :autosave="3000"></entity-field>
        <affirmative-policies--quota-configuration :phase="phase"></affirmative-policies--quota-configuration>
        <affirmative-policies--geo-quota-configuration :phase="phase"></affirmative-policies--geo-quota-configuration>
        <affirmative-policy--bonus-config :entity="phase"></affirmative-policy--bonus-config>
    </div>
</section>