<?php
$links = $entity->getMetaLists('links');


$template = "<li id='link-{{id}}' data-item-id='{{id}}' class='li-dos-blocos'>
                <a class='js-metalist-item-display' href='{{value}}'>{{title}}</a>
                <div class='botoes'>
                    <a class='editar js-open-editbox hltip'
                        data-target='#editbox-links'
                        data-dialog-callback='MapasCulturais.MetalistManager.updateDialog'
                        data-response-target='#link-{{id}}'
                        data-metalist-action='edit'
                        href='#' title='editar'></a>
                    <a class='icone icon_close js-metalist-item-delete hltip js-remove-item' data-href='{{deleteUrl}}' data-target='#link-{{id}}' data-confirm-message='Excluir este link?' title='excluir'></a>
                </div>
            </li>";
?>

<?php if (is_editable() || $links): ?>
    <div class="bloco" ng-non-bindable>
        <h3 class="subtitulo">Links</h3>
        <?php if(is_editable()): ?>
            <a class="adicionar js-open-editbox hltip" data-target="#editbox-links" href="#"
               data-dialog-callback="MapasCulturais.MetalistManager.updateDialog"
               data-response-target="ul.js-metalist"
               data-dialog-title="Adicionar Link"
               data-metalist-action='insert'
               data-response-template="<?php echo $template ?>"
               title="Clique para adicionar links">
            </a>
        <?php endif; ?>
        <ul class="js-metalist ul-dos-blocos js-slimScroll">
            <?php if($links): foreach($links as $link): ?>
                <li id="link-<?php echo $link->id ?>" class="<?php if(is_editable()) echo 'li-dos-blocos'; ?>" >
                    <a class="js-metalist-item-display" href="<?php echo $link->value;?>"><?php echo $link->title;?></a>
                    <?php if(is_editable()): ?>
                        <div class="botoes">
                            <a class="editar js-open-editbox hltip"
                               data-target="#editbox-links"
                               data-dialog-callback="MapasCulturais.MetalistManager.updateDialog"
                               data-response-target="#link-<?php echo $link->id ?>"
                               data-metalist-action="edit"
                               data-item="<?php echo htmlentities(json_encode($link)) ?>"
                               href="#" title='editar'></a>
                           <a class='icone icon_close js-metalist-item-delete hltip js-remove-item' data-href='<?php echo $link->deleteUrl ?>' data-target="#link-<?php echo $link->id ?>" data-confirm-message="Excluir este link?" title='excluir'></a>
                        </div>
                    <?php endif; ?>
                </li>
            <?php endforeach; endif;?>
        </ul>

        <div id="editbox-links" ng-non-bindable class="js-editbox mc-left" title="Editar Link"
             data-action-url="<?php echo $this->controller->createUrl('metalist', array('id' => $entity->id)) ?>"
             data-response-template="<?php echo $template ?>"
             data-metalist-group="links"
             data-metalist-title-label="Título" data-metalist-value-label="Endereço (com http://)" data-metalist-description-label="Descrição">
            <?php if($this->controller->action == 'create'): ?>
                <span class="js-dialog-disabled" data-message="Primeiro Salve"></span>
            <?php else: $app->view->part('parts/metalist-form.template.html'); endif; ?>
        </div>
    </div>
<?php endif; ?>
