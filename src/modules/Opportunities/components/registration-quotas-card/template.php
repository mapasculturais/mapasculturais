<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-field
');
?>
<div class="card owner">
    <h3 class="card__title">
        <?= i::__('Vai concorrer às cotas?') ?>
    </h3>

    <div class="card__content">
        <entity-field :entity="entity" prop="appliedForQuota" :hide-label="true"></entity-field>
    </div>
</div>
