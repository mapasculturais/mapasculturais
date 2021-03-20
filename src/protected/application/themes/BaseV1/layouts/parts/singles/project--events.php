<?php
use MapasCulturais\App;
use MapasCulturais\i;
$app = App::i();
$project = $this->controller->requestedEntity;
$class = ""; 
if(!($events = $app->repo('Event')->findBy(['project' => $project, 'status' => 1]))){
    $events = [(object)['id'=> null ,'name' => i::__("Não foram encontrados eventos")]];  
    $class = "no-event";
} 
?>
<?php $this->applyTemplateHook('project-event', 'before' )?>
<div class="event-link registration-fieldset clearfix">  
    <?php $this->applyTemplateHook('project-event', 'begin' )?>  
    <header>
        <div class="title">
            <?php i::_e("Eventos vinculados a este projeto"); ?>
            <?php $this->renderModalFor('event', false, i::__("Adicionar Evento"), "btn add-event add");?>
        </div>
    </header>

    <div class="event-status <?=$class?>"> 
           <ul class="js-event-list">
                <?php foreach ($events as $event){?>  
                    <?php $url = $event->id ? $app->createUrl('evento', $event->id) : "#";?>
                    <li>
                        <div><span class="icon icon-event"></span></div>
                        <div><a href="<?=$url?>"><?=$event->name?></a></div> 
                    </li>
                <?php } ?>
           </ul>
    </div>
    <?php $this->applyTemplateHook('project-event', 'end' )?>  
</div>
<?php $this->applyTemplateHook('project-event', 'after' )?> 

<script>
    $(document).on('createEvent', function(e,event){
        let url = MapasCulturais.createUrl('evento', '', [event.id]);
        if($('.event-status').hasClass('no-event')){
            $('.event-status').removeClass('no-event');
            $(".js-event-list").html("");
        }
        let newEvent = $(
        '<li>' +
            '<div><span class="icon icon-event"></span></div>' +
            '<div><a href="'+url+'">'+event.name+'</a></div>' +
        '</li>'
        );
        $('.js-event-list').append(newEvent);
    });
</script>

<style>
    #blockdiv {
        position:fixed !important;
    }

     #dialog-event-occurrence {
        margin-left: -403px !important;
        width: 40% !important;
        z-index: 1901 !important;
    }
    .tip-yellowsimple{
        z-index: 1901 !important;      
    }
</style>
