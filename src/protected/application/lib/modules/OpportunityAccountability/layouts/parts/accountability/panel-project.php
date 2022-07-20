
<?php

use MapasCulturais\Entities\Project;
$app = MapasCulturais\App::i();

$url = $app->createUrl("projeto", $project->id);
?>
<article class="objeto clearfix">
    <h1><a href="<?=$url?>"><?=$project->registration->number?>  <?=isset($project->name) ? " - ".$project->name : ""?></a></h1>
    <div class="objeto-meta">
        <?php if(isset($project->type->name)){?>
            <div><span class="label"><?php \MapasCulturais\i::esc_attr_e("Tipo:");?></span> <?=$project->type->name?></div> <br>
        <?php } ?>
        <div><span class="label"><?php \MapasCulturais\i::esc_attr_e("Oportunidade:");?></span> <?=$project->opportunity->name?></div> <br>
        <div><span class="label"><?php \MapasCulturais\i::esc_attr_e("Responsável:");?></span> <?=$project->owner->name?></div>           
    </div>
</article>