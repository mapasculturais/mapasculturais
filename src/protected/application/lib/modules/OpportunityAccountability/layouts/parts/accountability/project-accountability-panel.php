<?php
use MapasCulturais\Entities\Project;
?>
<section id="projects-rascunho" class="panel-list">      
    <header>
        <?php foreach($projects as $project): ?> 
       
            <?php if(isset($project->opportunity->accountabilityPhase) && $project->status == Project::STATUS_DRAFT && (new DateTime()) < $project->opportunity->accountabilityPhase->registrationFrom && $project->isAccountability){?>          
                <h2><?php \MapasCulturais\i::_e("Projetos contemplados");?></h2>
                <?php break;?>
            <?php } ?>    
        <?php endforeach; ?>        
    </header>
    
    <?php foreach($projects as $project): ?> 
        <?php if(isset($project->opportunity->accountabilityPhase) && $project->status == Project::STATUS_DRAFT && (new DateTime()) < $project->opportunity->accountabilityPhase->registrationFrom && $project->isAccountability):?>
            <?php $this->part('accountability/panel-project', ['project' => $project]); ?>
        <?php endif;?>  
    <?php endforeach; ?>
</section>