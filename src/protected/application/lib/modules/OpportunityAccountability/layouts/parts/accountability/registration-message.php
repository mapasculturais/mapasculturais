<?php
use MapasCulturais\i;
$senddate = $entity->sentTimestamp;
?>

<div class="registration-fieldset">
    <p class="registration-help">
        <?php i::_e("Prestação de contas enviada dia");?> <?=$senddate->format('d/m/y H:i:s')?>    
    </p>
</div>