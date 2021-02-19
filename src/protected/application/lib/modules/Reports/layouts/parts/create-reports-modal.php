<?php
use MapasCulturais\i;
?>

<div ng-class="{hidden:!data.reportModal}" class="bg-reports-modal" id="reportsModal">
<div class="reports-modal">
    <div class="grafic-type" ng-class="{hidden:!data.graficType}">
            <header>
                <h2 class="report-modal-title"><?php i::_e("Criar novo gráfico"); ?></h2>
                <a ng-click="data.reportModal=false"><i class="fas fa-times close-modal"></i></a>
            </header>        
        
            <h5><?php i::_e("Antes de definir os parâmetros, defina o tipo de gráfico que você deseja:"); ?></h5>
            <p><?php i::_e("Tipo de visualização"); ?></p>
            <div>
                <label><input ng-model="data.reportsData.type"  value="pie" type="radio"> <i class="fas fa-chart-pie"></i> <span><?php i::_e("Gráfico de pizza");?></span> </label>
            </div>

            <div>
                <label><input ng-model="data.reportsData.type"  value="line" type="radio"> <i class="fas fa-chart-area"></i> <span><?php i::_e("Gráfico de linha");?></span> </label>
            </div>

            <div>
                <label><input ng-model="data.reportsData.type"  value="bar" type="radio">  <i class="far fa-chart-bar"></i> <span><?php i::_e("Gráfico de barra");?></span> </label>
            </div>

            <div>
                <label><input ng-model="data.reportsData.type"  value="table" type="radio"> <i class="fas fa-th-list"></i> <span><?php i::_e("Gráfico de tabela");?></span> </label>
            </div>       
        
    </div> 

        <div class="grafic-data">
        <!-- <div class="grafic-data" ng-class="{hidden:!data.graficData}"> -->
            <header>
                <h2 class="report-modal-title"><?php i::_e("Criar novo gráfico de pizza"); ?></h2>
                <a ng-click="data.reportModal=false"><i class="fas fa-times close-modal"></i></a>
            </header>        
        
            <h5><?php i::_e("Agora defina o título e dados exibido no gráfico"); ?></h5>
            
            <div>
                <div>
                    <label><?php i::_e("Título do gráfico"); ?></label>
                    <input type="text" ng-model="data.reportsData.title">
                </div> 

                <div>
                    <label><?php i::_e("Breve descrição"); ?></label>
                    <input type="text" ng-model="data.reportsData.description">
                </div> 

                <div>
                    <label><?php i::_e("Dados a serem exibidos"); ?></label>
                    <select>
                        <option>Seleciona um dado</option>                                    
                    </select>
                </div> 
            </div>

            <hr>
           
            <div>
                <div ng-if="data.reportsData.categories.length > 0">
                    <label><?php i::_e("Categoria da oportunidade"); ?></label>
                    <select>
                        <option value=""><?php i::_e("Todas"); ?></option>
                        <option ng-repeat="categories in  data.reportsData.categories" >{{categories}}</option>                                    
                    </select>
                </div> 

                <div >
                    <label><?php i::_e("Gênero"); ?></label>
                    <select>
                        <option value=""><?php i::_e("Todos"); ?></option>
                        <option ng-repeat="genero in  data.reportsData.agentSelectFields.genero" >{{genero}}</option>                                    
                    </select>
                </div> 

                <div>
                    <label><?php i::_e("Raça"); ?></label>
                    <select>
                        <option value=""><?php i::_e("Todas"); ?></option>
                        <option ng-repeat="raca in  data.reportsData.agentSelectFields.raca" >{{raca}}</option>                                    
                    </select>
                </div>

                <div>
                
                    <label><?php i::_e("Faixa etária"); ?></label>
                    <select ng-model="data.reportsData.breed">
                    <option value=""><?php i::_e("Todas"); ?></option>
                        <option ng-repeat="ageRange in  data.ageRange" >{{ageRange.range}}</option>                                      
                    </select>
                </div>

                <div>
                    <label><?php i::_e("Orientação sexual"); ?></label>
                    <select ng-model="data.reportsData.sesualOrientation">
                        <option value=""><?php i::_e("Todas"); ?></option>
                        <option value="{{fIndex}}" ng-repeat="orientacaoSexual in  data.reportsData.agentSelectFields.orientacaoSexual" >{{orientacaoSexual}}</option>                                    
                    </select>
                </div>

                <div>
                
                    <label><?php i::_e("Estado"); ?></label>
                    <select ng-model="data.reportsData.state">
                    <option value=""><?php i::_e("Todos"); ?></option>
                    <option ng-repeat="En_Estado in  data.reportsData.agentSelectFields.En_Estado" >{{En_Estado}}</option>                                    
                    </select>
                </div>

                <div>
                    <label><?php i::_e("Município"); ?></label>
                    <select ng-model="data.reportsData.county">
                        <option value=""><?php i::_e("Todos"); ?></option>                                  
                    </select>
                </div>

                <div>
                    <label><?php i::_e("Bairro"); ?></label>
                    <select ng-model="data.reportsData.neighborhood">
                        <option value=""><?php i::_e("Todos"); ?></option>                                
                    </select>
                </div>
            </div>
               
        </div>
        <footer>
            <button class="btn btn-default close-modal" ng-click="data.graficData=false; data.graficType=true; data.reportModal=false"><?php i::_e("Cancelar"); ?></button>
            <button class="btn btn-default"  class="js-close"><?php i::_e("Salvar rascunho"); ?></button>
            <button class="btn btn-primary" ng-if="data.graficType == true" ng-click="data.graficData=true; data.graficType=false;nextStep()"  class="js-close"><?php i::_e("Proxima etapa"); ?></button>
            <button class="btn btn-primary" ng-if="data.graficData == true"><?php i::_e("Gerar gráfico"); ?></button>
        </footer>

    </div>

</div><!-- /.bg-reports-modal -->