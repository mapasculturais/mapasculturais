<?php
use MapasCulturais\i;

$this->layout = 'panel';

$this->bodyProperties['ng-app'] = "entity.app";
$this->bodyProperties['ng-controller'] = "EntityController";

?>
<div class="panel-main-content" ng-app="DeleteAccount" ng-controller="DeleteAccountController">
    <h1><?php i::_e('Apagar Conta') ?></h1>
    <p><?php i::_e('A remoção da conta fará com que a maioria de suas informações não sejam mais acessíveis publicamente. Algumas informações, como por exemplo as inscrições em editais continuarão acessíveis.<br>Você pode escolher por transferir suas entidades para outro usuário, que será questionado se deseja recebê-las. No caso do usuário se negar a receber as entidades, estas serão excluidas.')?></p>
    <div>
        <p>
            <?php i::_e('Se desejar escolha o usuário para receber suas entidades') ?>:
        
            <a id="delete-account--button" ng-click="editbox.open('delete-account--edit-box', $event)" rel='noopener noreferrer'>{{data.selectedAgent ? data.selectedAgent.name : "<?php i::_e('Selecione o Usuário') ?>"}}</a>

            <a ng-if="data.selectedAgent" ng-click="data.selectedAgent = null" class="delete" title="<?php i::_e("exluir usuário selecionado") ?>"></a>
        </p>
        
        <edit-box id="delete-account--edit-box" position="right" title="<?php i::esc_attr_e("Selecione o usuário que receberá suas entidades");?>" cancel-label="<?php i::esc_attr_e("Cancelar");?>" close-on-cancel='true' spinner-condition="data.spinner">
            <find-entity 
                id='delete-account--find-entity' 
                entity="agent" 
                no-results-text="<?php i::esc_attr_e("Nenhum agente encontrado");?>" 
                select="selectedAgent" 
                api-query='data.apiQuery' 
                spinner-condition="data.spinner" 
                on-repeat-done="adjustBoxPosition"></find-entity>
        </edit-box>
        <a href="<?php echo $this->controller->createUrl('index') ?>" class="btn btn-success"><?php i::_e('Cancelar') ?></a>
        <a ng-click='deleteAccount("<?php echo $app->user->deleteAccountToken ?>")' class="btn btn-danger"><?php i::_e('Apagar Conta') ?></a>
    </div>
</div>
