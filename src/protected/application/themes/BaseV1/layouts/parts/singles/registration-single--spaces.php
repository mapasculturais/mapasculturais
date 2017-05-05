<?php 
    $spaceRelation = array_key_exists('useSpaceRelation', $project->metadata) ? $project->metadata['useSpaceRelation'] : '';

    if($spaceRelation == 'optional' || $spaceRelation == 'required'):
?>
    <div class="registration-fieldset">
        <!-- selecionar espaço -->
        <h4><?php \MapasCulturais\i::_e("Espaços Vinculados"); ?></h4>        
        <div>
            
        </div>
    </div>
    <?php endif; ?>
