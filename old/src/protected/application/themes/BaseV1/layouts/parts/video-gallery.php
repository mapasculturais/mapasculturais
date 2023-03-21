<?php
if($this->controller->action === 'create' || !is_object($entity))
    return;
?>
<?php if(!is_object($entity)):?>
    <div class="alert info"><?php i::__("Nenhum vídeo disponível");?></div>
    <?php return;?>
<?php endif;?>

<?php
$videos = $entity->getMetaLists('videos');
$spinner_url = $this->asset("img/spinner_192.gif", false);
$template = "<li id='video-{{id}}'>
                <a class='js-metalist-item-display' href='#video' data-videolink='{{value}}' rel='noopener noreferrer'>
                    <img src='{$spinner_url}' class='thumbnail_med_wide'/>
                    <h1 class='title'>{{title}}</h1>
                </a>
                <div class='btn btn-default'>
                    <a class='js-open-editbox edit hltip'
                        data-target='#editbox-videogallery'
                        data-dialog-callback='MapasCulturais.MetalistManager.updateDialog'
                        data-response-target='#video-{{id}}'
                        data-metalist-action='edit'
                        href='#' title='Editar'></a>
                    <a class='delete js-metalist-item-delete hltip js-remove-item'  data-href='{{deleteUrl}}'  data-target='#video-{{id}}'  data-confirm-messagem='Excluir este vídeo?' title='Excluir' rel='noopener noreferrer'></a>
                </div>
            </li>";
?>

<?php if(is_array($videos) && count($videos) <= 0 && $this->controller == 'registration'):?>
    <div class="alert info"><?php i::__("Nenhum vídeo disponível");?></div>
<?php endif;?>

<?php if ($this->isEditable() || $videos): ?>
    <h3><?php \MapasCulturais\i::_e("Vídeos");?></h3>
    <a name="video" rel='noopener noreferrer'></a>
    <div id="video-player" class="video" ng-non-bindable>
        <iframe id="video_display" width="100%" height="100%" src="" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
    </div>
    <ul class="clearfix js-videogallery" ng-non-bindable>
        <?php if($videos): foreach($videos as $video): ?>
            <li id="video-<?php echo $video->id ?>">
                <a class="js-metalist-item-display" data-videolink="<?php echo $video->value;?>" title="Cadastrado em <?php echo $video->createTimestamp->format('d/m/Y á\s H:i:s')?>">
                    <img src="<?php $this->asset('img/spinner_192.gif'); ?>" alt="" class="thumbnail_med_wide"/>
                    <h1 class="title"><?php echo $video->title;?></h1>
                </a>
                <?php if($this->isEditable()): ?>
                    <div class="btn btn-default">
                        <a class="js-open-editbox edit hltip"
                           data-dialog-title="<?php \MapasCulturais\i::esc_attr_e("Editar Vídeo");?>"
                           data-target="#editbox-videogallery"
                           data-dialog-callback="MapasCulturais.MetalistManager.updateDialog"
                           data-response-target="#video-<?php echo $video->id ?>"
                           data-metalist-action="edit"
                           data-item="<?php echo htmlentities(json_encode($video));?>"
                           href="#" title='<?php \MapasCulturais\i::_e("Editar");?>'></a>
                        <a class='delete js-metalist-item-delete hltip js-remove-item' data-href='<?php echo $video->deleteUrl ?>' data-target="#video-<?php echo $video->id ?>" data-confirm-messagem="<?php \MapasCulturais\i::esc_attr_e("Excluir este vídeo?");?>" title='<?php \MapasCulturais\i::_e("Excluir");?>'></a>
                    </div>
                <?php endif; ?>
            </li>
        <?php endforeach; endif;?>
    </ul>
<?php endif; ?>

<div id="editbox-videogallery" ng-non-bindable class="js-editbox mc-bottom" title="<?php \MapasCulturais\i::esc_attr_e("Editar Vídeo");?>"
     data-action-url="<?php echo $this->controller->createUrl('metalist', array('id' => $entity->id)) ?>"
     data-response-template="<?php echo $template; ?>"
     data-metalist-group="videos"
     data-metalist-title-label="<?php \MapasCulturais\i::esc_attr_e("Título");?>" data-metalist-value-label="<?php \MapasCulturais\i::esc_attr_e("Endereço do vídeo (Youtube ou Vimeo)");?>" data-metalist-description-label="<?php \MapasCulturais\i::esc_attr_e("Descrição");?>">
    <?php if($this->controller->action == 'create'): ?>
        <span class="js-dialog-disabled" data-message="<?php \MapasCulturais\i::esc_attr_e("Para adicionar vídeos você primeiro deve salvar.");?>"></span>
    <?php else: ?>
        <?php $this->part('metalist-form-template'); ?>
    <?php endif; ?>
</div>
<?php if($this->isEditable()): ?>
    <p class="gallery-footer" ng-non-bindable>
        <a class="btn btn-default add js-open-editbox" href="#"
           data-dialog-title="<?php \MapasCulturais\i::esc_attr_e("Adicionar Vídeo");?>"
           data-target="#editbox-videogallery"
           data-dialog-callback="MapasCulturais.MetalistManager.updateDialog"
           data-response-target="ul.js-videogallery"
           data-metalist-action="insert"
           data-response-template="<?php echo $template; ?>"><?php \MapasCulturais\i::_e("Adicionar vídeo");?></a>
    </p>
<?php endif; ?>
