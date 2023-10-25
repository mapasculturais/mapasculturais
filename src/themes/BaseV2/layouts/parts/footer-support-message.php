<?php

use MapasCulturais\i;

$message =  $app->config["footer.supportMessage"] ?? null;

if($message) {
    $message = str_replace('%supportLink%', "<a href=\"".$app->config['footer.supportLink']."\" class=\"primary__color bold\">".i::__('Clique aqui')."</a>", $message); 
    $message = str_replace('%supportEmail%', "<a href=\"".$app->config['footer.supportEmail']."\" class=\"primary__color bold\">".$app->config['footer.supportEmail']."</a>", $message);
}

?>
<div>
    <?php if ($message) : ?>
        <p class="main-footer__msgspt semibold"><?=$message?></p>
        
    <?php endif ?>
</div>
