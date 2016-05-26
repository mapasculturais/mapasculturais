<?php
if($this->controller->action === 'create')
    return;


$videos = $entity->getMetaLists('videos');
$spinner_url = $this->asset("img/spinner_192.gif", false);
$template = "<li id='video-{{id}}'>
                <a class='js-metalist-item-display' href='#video' data-videolink='{{value}}'>
                    <img src='{$spinner_url}' class='thumbnail_med_wide'/>
                    <h1 class='title'>{{title}}</h1>
                </a>
                <div class='botoes-de-edicao'>
                    <a class='js-open-editbox edit hltip'
                        data-target='#editbox-videogallery'
                        data-dialog-callback='MapasCulturais.MetalistManager.updateDialog'
                        data-response-target='#video-{{id}}'
                        data-metalist-action='edit'
                        href='#' title='Editar'></a>
                    <a class='icon icon-close_alt js-metalist-item-delete hltip js-remove-item' data-href='{{deleteUrl}}' data-target='#video-{{id}}' data-confirm-message='Excluir este video?' title='Excluir'></a>
                </div>
            </li>";
?>
<?php if ($this->isEditable() || $videos): ?>
    <h3>videos</h3>
    <a name="video"></a>
    <div id="video-player" class="video" ng-non-bindable>
        <iframe id="video_display" width="100%" height="100%" src="" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
    </div>
    <ul class="clearfix js-videogallery" ng-non-bindable>
        <?php if($videos): foreach($videos as $video): ?>
            <li id="video-<?php echo $video->id ?>">
                <a class="js-metalist-item-display" data-videolink="<?php echo $video->value;?>" title="<?php echo $video->title;?>">
                    <img src="<?php $this->asset('img/spinner_192.gif'); ?>" alt="" class="thumbnail_med_wide"/>
                    <h1 class="title"><?php echo $video->title;?></h1>
                </a>
                <?php if($this->isEditable()): ?>
                    <div class="btn btn-default">
                        <a class="js-open-editbox edit hltip"
                           data-dialog-title="Editar video"
                           data-target="#editbox-videogallery"
                           data-dialog-callback="MapasCulturais.MetalistManager.updateDialog"
                           data-response-target="#video-<?php echo $video->id ?>"
                           data-metalist-action="edit"
                           data-item="<?php echo htmlentities(json_encode($video));?>"
                           href="#" title='Editar'></a>
                           <a class='delete js-metalist-item-delete hltip js-remove-item' data-href='<?php echo $video->deleteUrl ?>' data-target="#video-<?php echo $video->id ?>" data-confirm-messagem="Eliminar este video?" title='Excluir'></a>
                    </div>
                <?php endif; ?>
            </li>
        <?php endforeach; endif;?>
    </ul>
<?php endif; ?>

<div id="editbox-videogallery" ng-non-bindable class="js-editbox mc-bottom" title="Editar video"
     data-action-url="<?php echo $this->controller->createUrl('metalist', array('id' => $entity->id)) ?>"
     data-response-template="<?php echo $template; ?>"
     data-metalist-group="videos"
     data-metalist-title-label="Título" data-metalist-value-label="Dirección del video (Youtube o Vimeo)" data-metalist-description-label="Descripción">
    <?php if($this->controller->action == 'create'): ?>
        <span class="js-dialog-disabled" data-message="Para agregar videos primero debe salvar."></span>
    <?php else: ?>
        <?php $this->part('metalist-form-template'); ?>
    <?php endif; ?>
</div>
<?php if($this->isEditable()): ?>
    <p class="gallery-footer" ng-non-bindable>
        <a class="btn btn-default add js-open-editbox" href="#"
           data-dialog-title="Agregar video"
           data-target="#editbox-videogallery"
           data-dialog-callback="MapasCulturais.MetalistManager.updateDialog"
           data-response-target="ul.js-videogallery"
           data-metalist-action="insert"
           data-response-template="<?php echo $template; ?>"
                        >Agregar video</a>
    </p>
<?php endif; ?>
