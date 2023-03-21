<?php 
    // VERIFICAÇÃO SE TEM REGISTRO DE SOLICITAÇÃO DE ESPAÇO

    if(!empty($query) ){
        if( $query[0]->value == 'required' || $query[0]->value == 'optional') {
?>
    <div class="registration-fieldset">
        <h4 id="registration-space-title"><?php \MapasCulturais\i::_e("Espaço Vinculado"); ?>
        <span class="registration-label"></span></h4> 
            <div class="registration-help">Selecione um espaço a ser vinculado à inscrição</div>
                <div id="registration-space" class="registration-list-item registration-edit-mode ng-scope">
                    <p ng-if="data.entity.registrationSpace.status < 0" class="alert warning" 
                        style="display:block !important /* está oculto no scss */" ><?php \MapasCulturais\i::_e("Aguardando confirmação");?>
                    </p>
                    <div class="js-registration-agent registration-agent">
                        <!-- div para mostrar o espaço escolhido -->
                        <div class="clearfix">
                            <img ng-src="{{data.entity.registrationSpace.space.avatarUrl || data.assets.avatarSpace}}" class="registration-space-avatar" />
                            <div class="space-relation-message">
                                <a ng-if="data.entity.registrationSpace" href="{{data.entity.registrationSpace.space.singleUrl}}">{{data.entity.registrationSpace.space.name}}</a>
                                <span ng-if="!data.entity.registrationSpace"><?php \MapasCulturais\i::_e("Não informado");?></span>
                            </div>
                        </div>
                    </div>
                    <div ng-if="data.isEditable" class="btn-group">
                    
                        <span ng-if="data.entity.registrationSpace" class="space-edit-buttons">
                            <a class="btn btn-default edit hltip" 
                               ng-click="openEditBox('editbox-select-registration-space-relation', $event)" 
                               title="<?php \MapasCulturais\i::esc_attr_e("Editar");?>"><?php \MapasCulturais\i::_e("Trocar Espaço");?>
                            </a>
                            
                            <a class="btn btn-default delete hltip" 
                               ng-click="unsetRegistrationSpace(data.entity.registrationSpace, $event)" 
                               title="<?php \MapasCulturais\i::esc_attr_e("Excluir");?> "><?php \MapasCulturais\i::esc_attr_e("Excluir");?>
                            </a>
                        </span>
                        <div class="space-add-button">
                            <a class="btn btn-default add hltip" 
                                ng-if="!data.entity.registrationSpace" 
                                ng-click="openEditBox('editbox-select-registration-space-relation', $event)" 
                                title="<?php \MapasCulturais\i::esc_attr_e("Adicionar");?>"><?php \MapasCulturais\i::_e("Adicionar");?>
                            </a>
                        </div>
                    </div>
                </div>

        <edit-box id="editbox-select-registration-space-relation" 
                    position="left" 
                    title="<?php \MapasCulturais\i::esc_attr_e("Selecionar Espaço");?>"
                    cancel-label="<?php \MapasCulturais\i::esc_attr_e("Cancelar");?>" 
                    close-on-cancel='true' 
                    spinner-condition="data.registrationSpinner">
            
            <find-entity id='find-entity-registration-space-relation' 
            entity="space" 
            no-results-text="<?php \MapasCulturais\i::esc_attr_e("Nenhum espaço encontrado");?>" 
            select="setSpaceRelation" 
            spinner-condition="data.registrationSpinner">
            </find-entity>
        </edit-box>

        <div ng-if="data.errors.space" class="alert danger" style="margin-top:1em;">{{data.errors.space}}</div>
    </div>
    <?php 
        };
}; ?>