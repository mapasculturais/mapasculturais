<?php
$registration = ['from' => $entity->registrationFrom, 'to'=> $entity->registrationTo];
$registration_dates = [];

if($registration['from'] instanceof DateTime) {
    $registration_dates['from'] = $registration['from']->format('d/m/Y');
}

if($registration['to'] instanceof DateTime) {
    $registration_dates['to'] = $registration['to']->format('d/m/Y');
}

?>

<?php $this->applyTemplateHook('panel-evaluation', 'before', [$entity]); ?>
<article class="objeto clearfix">
    <?php $this->applyTemplateHook('panel-evaluation', 'begin', [$entity]); ?>

    <?php if($avatar = $entity->avatar): ?>
        <div class="thumb" style="background-image: url(<?php echo $avatar->transform('avatarSmall')->url; ?>)"></div>
    <?php else: ?>
        <div class="thumb"></div>
    <?php endif; ?>

    <?php $this->applyTemplateHook('panel-evaluation-title', 'before', [$entity]); ?>    
    <h1>
        <?php $this->applyTemplateHook('panel-evaluation-title', 'begin', [$entity]); ?>

        <a href="<?= $entity->singleUrl ?>"><?php echo $entity->name; ?></a>
        <?php $this->applyTemplateHook('panel-evaluation-title', 'end', [$entity]); ?>
    </h1>
    <?php $this->applyTemplateHook('panel-evaluation-title', 'after', [$entity]); ?>    
    
    <?php $this->applyTemplateHook('panel-evaluation-meta', 'before', [$entity]); ?>    
    <div class="objeto-meta">
        <?php $this->applyTemplateHook('panel-evaluation-meta', 'begin', [$entity]); ?>    
        <div> <span class="label">Tipo:</span> <?php echo $entity->type->name?> </div>
        <?php if( is_array($registration) && ( $registration['from'] || $registration['to'] ) ): ?>
            <div>
                <span class="label"> <?php \MapasCulturais\i::_e("Inscrições:");?> </span>

                <?php
                if($entity->isRegistrationOpen())
                    \MapasCulturais\i::_e("Abertas ");

                if($registration['from'] && !$registration['to'])
                    echo \MapasCulturais\i::__("a partir de ") . $registration_dates['from'];
                elseif(!$registration['from'] && $registration['to'])
                    echo \MapasCulturais\i::__(' até ') . $registration_dates['to'];
                else
                    echo \MapasCulturais\i::__('de ') . $registration_dates['from'] .\MapasCulturais\i::__(' a '). $registration_dates['to'];
                ?>
            </div>
        <?php endif; ?>

        <div><span class="label"><?php \MapasCulturais\i::_e("Organização:");?></span> <?php echo $entity->owner->name; ?></div>
        <?php if($entity->originSiteUrl): ?>
            <div><span class="label">Url: </span> <?php echo $entity->originSiteUrl;?></div>
        <?php endif; ?>
        <?php $this->applyTemplateHook('panel-evaluation-meta', 'end', [$entity]); ?>    
    </div>
    <?php $this->applyTemplateHook('panel-evaluation-meta', 'after', [$entity]); ?>    

    <?php $this->applyTemplateHook('panel-evaluation-actions', 'before', [$entity]); ?>    
    <div class="entity-actions">
        <a class="btn btn-small btn-primary" href="<?php echo $entity->singleUrl; ?>#/tab=evaluations">
            <?php
                \MapasCulturais\i::_e("Visualizar Inscritos");
            ?>
        </a>
    </div>
    <?php $this->applyTemplateHook('panel-evaluation-actions', 'after', [$entity]); ?>    

    <?php $this->applyTemplateHook('panel-evaluation', 'end', [$entity]); ?>
</article>
<?php $this->applyTemplateHook('panel-evaluation', 'after', [$entity]); ?>
