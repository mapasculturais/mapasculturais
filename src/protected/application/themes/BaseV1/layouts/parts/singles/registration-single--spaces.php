<?php 
    $spaceRelation = array_key_exists('useSpaceRelation', $project->metadata) ? $project->metadata['useSpaceRelation'] : '';

    if($spaceRelation == 'optional' || $spaceRelation == 'required'):
?>
    <div class="registration-fieldset">
        <h4><?php \MapasCulturais\i::_e("Espaços Vinculados"); ?></h4>        
        
            <div class="registration-list-item registration-edit-mode">
                <div class="registration-help">Selecione um espaço a ser vinculado à inscrição</div>

                <div id="registration-space" class="js-registration-agent registration-agent">
                    <p ng-if="data.entity.registrationSpace.status < 0" class="alert warning" style="display:block !important /* está oculto no scss */" ><?php \MapasCulturais\i::_e("Aguardando confirmação");?></p>
                    <div class="clearfix">
                        <img ng-src="{{data.entity.registrationSpace.space.avatarUrl}}" class="registration-space-avatar" />
                        <a ng-if="data.entity.registrationSpace" href="{{data.entity.registrationSpace.space.singleUrl}}">{{data.entity.registrationSpace.space.name}}</a>
                        <span ng-if="!data.entity.registrationSpace"><?php \MapasCulturais\i::_e("Não informado");?></span>
                    </div>
                </div>
            </div>
            <div ng-if="data.isEditable" class="btn-group">
                <span ng-if="data.entity.registrationSpace">
                    <a class="btn btn-data.entityault edit hltip" ng-click="openEditBox('editbox-select-registration-space-relation', $event)" 
                       title="<?php \MapasCulturais\i::esc_attr_e("Editar");?>"><?php \MapasCulturais\i::_e("Trocar Espaço");?>
                    </a>
                    <a class="btn btn-data.entityault delete hltip" ng-click="unsetRegistrationSpace(data.entity.registrationSpace.id, data.entity.registrationSpace)" 
                       title="<?php \MapasCulturais\i::esc_attr_e("Excluir");?> "><?php \MapasCulturais\i::esc_attr_e("Excluir");?>
                    </a>
                </span>
                <a class="btn btn-default add hltip ng-scope" ng-if="!data.entity.registrationSpace" 
                   ng-click="openEditBox('editbox-select-registration-space-relation', $event)" 
                   title="<?php \MapasCulturais\i::esc_attr_e("Adicionar");?>"><?php \MapasCulturais\i::_e("Adicionar");?></a>
            </div>

            <edit-box id="editbox-select-registration-space-relation" position="left" 
                      title="<?php \MapasCulturais\i::esc_attr_e("Selecionar Espaço");?>"
                      cancel-label="<?php \MapasCulturais\i::esc_attr_e("Cancelar");?>" close-on-cancel='true' spinner-condition="data.registrationSpinner">
                
                <find-entity id='find-entity-registration-space-relation' 
                             entity="space" 
                             no-results-text="<?php \MapasCulturais\i::esc_attr_e("Nenhum espaço encontrado");?>" 
                             select="setSpaceRelation" 
                             spinner-condition="data.registrationSpinner">
                </find-entity>
            </edit-box>
    </div>
    <?php endif; ?>
