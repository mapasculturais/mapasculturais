<?php

use MapasCulturais\i;

$message =  $app->config["footer.supportMessage"] ?? null;

?>
<div>
    <?php if ($message) : ?>
        <p class="main-footer__msgspt semibold"><?=$message?></p>
        
    <?php endif ?>
</div>
