<?php
$links = $entity->getMetaLists('links');


$template = "<li id='link-{{id}}' data-item-id='{{id}}' class='widget-list-item'>
                <a class='js-metalist-item-display' href='{{value}}'>{{title}}</a>
                <div class='botoes'>
                    <a class='edit js-open-editbox hltip'
                        data-target='#editbox-links'
                        data-dialog-callback='MapasCulturais.MetalistManager.updateDialog'
                        data-response-target='#link-{{id}}'
                        data-metalist-action='edit'
                        href='#' title='editar'></a>
                    <a class='icon close js-metalist-item-delete hltip js-remove-item' data-href='{{deleteUrl}}' data-target='#link-{{id}}' data-confirm-message='Excluir este link?' title='excluir'></a>
                </div>
            </li>";
?>

<?php if ($this->isEditable() || $links): ?>
    <div class="widget" ng-non-bindable>
        <h3>Links</h3>
        <?php if($this->isEditable()): ?>
            <a class="add js-open-editbox hltip" data-target="#editbox-links" href="#"
               data-dialog-callback="MapasCulturais.MetalistManager.updateDialog"
               data-response-target="ul.js-metalist"
               data-dialog-title="Adicionar Link"
               data-metalist-action='insert'
               data-response-template="<?php echo $template ?>"
               title="Clique para adicionar links">
            </a>
        <?php endif; ?>
        <ul class="js-metalist widget-list js-slimScroll">
            <?php if($links): foreach($links as $link): ?>
                <li id="link-<?php echo $link->id ?>" class="widget-list-item<?php if($this->isEditable()) echo ' is-editable'; ?>" >
                    <a class="js-metalist-item-display" href="<?php echo $link->value;?>"><span><?php echo $link->title;?></span></a>
                    <?php if($this->isEditable()): ?>
                        <div class="botoes">
                            <a class="edit js-open-editbox hltip"
                               data-target="#editbox-links"
                               data-dialog-callback="MapasCulturais.MetalistManager.updateDialog"
                               data-response-target="#link-<?php echo $link->id ?>"
                               data-metalist-action="edit"
                               data-item="<?php echo htmlentities(json_encode($link)) ?>"
                               href="#" title='editar'></a>
                           <a class='delete js-metalist-item-delete hltip js-remove-item' data-href='<?php echo $link->deleteUrl ?>' data-target="#link-<?php echo $link->id ?>" data-confirm-message="Excluir este link?" title='excluir'></a>
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
                <span class="js-dialog-disabled" data-message="Para adicionar links você primeiro deve salvar."></span>
            <?php else: ?>
                <?php $this->part('metalist-form-template'); ?>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
