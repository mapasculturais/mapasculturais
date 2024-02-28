<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import("
    affirmative-policies--geo-quota-configuration
    affirmative-policies--quota-configuration
    entity-field
    mc-icon
    tiebreaker-criteria-configuration
");
?>
<div class="col-12">
    <h3><?= i::__('Critérios de avaliação') ?></h3>
    <entity-field :entity="phase" prop="enableViability" :autosave="3000"></entity-field>
</div>
<div class="col-12">
    <h3><?= i::__('Critérios de desempate') ?></h3>
    <tiebreaker-criteria-configuration :phase="phase"></tiebreaker-criteria-configuration>
</div>
<div class="col-12">
    <h3><?= i::__('Políticas afirmativas') ?></h3>
    <!-- cotas -->
    <affirmative-policies--quota-configuration :entity="phase"></affirmative-policies--quota-configuration>
    
    <!-- distribuição de vagas por território -->
    <affirmative-policies--geo-quota-configuration :phase="phase"></affirmative-policies--geo-quota-configuration>

</div>
<div class="col-12">
    <h3><?= i::__('Comissão de avaliação') ?></h3>
    
</div>