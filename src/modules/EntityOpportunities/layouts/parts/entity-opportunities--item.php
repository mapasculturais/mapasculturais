<?php 
use MapasCulturais\i;
use MapasCulturais\Entities\Opportunity;

$avatar = $opportunity->avatar ? $opportunity->avatar->transform('avatarSmall') : null;
    
$url = $this->isEditable() ? $opportunity->editUrl : $opportunity->singleUrl;
?>
<article class="objeto <?php if($avatar) echo 'has-avatar' ?>">
    <?php if($avatar): ?>
        <img src="<?php echo $avatar->url?>" >
    <?php endif; ?>
    <div class="entity-opportunity--content ">
        <a href="<?php echo $url ?>"><?php echo $opportunity->name ?></a>
        <?php if($opportunity->status == Opportunity::STATUS_DRAFT): ?>
            <em><?php i::_e('(Rascunho)') ?></em>
        <?php endif; ?>
        <br>
        <div class="objeto-meta">
            <?php $this->part('singles/opportunity-about--registration-dates', ['entity' => $opportunity, 'disable_editable' => true]) ?>
        </div>
    </div>
</article >