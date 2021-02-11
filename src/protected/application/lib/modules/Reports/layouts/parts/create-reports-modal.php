<?php
use MapasCulturais\i;
?>

<div ng-class="{hidden:!data.reportModal}">
    <div class="grafic-type" ng-class="{hidden:!data.graficType}">
            <header>
                <h2 class="report-modal-title"><?php i::_e("Criar novo gráfico"); ?></h2>
                <a ng-click="data.reportModal=false"><i class="fas fa-times"></i></a>
            </header>        
        
            <h5><?php i::_e("Antes de definir os parâmetros, defina o tipo de gráfico que você deseja:"); ?></h5>
            <p><?php i::_e("Tipo de visualização"); ?></p>
            <div>
                <label><input type="radio"> <i class="fas fa-chart-pie"></i> <span><?php i::_e("Gráfico de pizza");?></span> </label>
            </div>

            <div>
                <label><input type="radio"> <i class="fas fa-chart-area"></i> <span><?php i::_e("Gráfico de linha");?></span> </label>
            </div>

            <div>
                <label><input type="radio"> <i class="far fa-chart-bar"></i> <span><?php i::_e("Gráfico de barra");?></span> </label>
            </div>

            <div>
                <label><input type="radio"> <i class="fas fa-th-list"></i> <span><?php i::_e("Gráfico de tabela");?></span> </label>
            </div>       
        
    </div> 

        <div class="grafic-data" ng-class="{hidden:!data.graficData}">
            <header>
                <h2 class="report-modal-title"><?php i::_e("Criar novo gráfico de pizza"); ?></h2>
                <a ng-click="data.reportModal=false"><i class="fas fa-times"></i></a>
            </header>        
        
            <h5><?php i::_e("Agora defina o título e dados exibido no gráfico"); ?></h5>
            
            <div>
                <div>
                    <label><?php i::_e("Título do gráfico"); ?></label>
                    <input type="text">
                </div> 

                <div>
                    <label><?php i::_e("Breve descrição"); ?></label>
                    <input type="text">
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
                <div>
                    <label><?php i::_e("Categoria da oportunidade"); ?></label>
                    <select>
                        <option>Todas</option>                                    
                    </select>
                </div> 

                <div>
                    <label><?php i::_e("Gênero"); ?></label>
                    <select>
                        <option>Todas</option>                                    
                    </select>
                </div> 

                <div>
                    <label><?php i::_e("Raça"); ?></label>
                    <select>
                        <option>Todas</option>                                    
                    </select>
                </div>

                <div>
                    <label><?php i::_e("Faixa etária"); ?></label>
                    <select>
                        <option>Todas</option>                                    
                    </select>
                </div>

                <div>
                    <label><?php i::_e("Orientação sexual"); ?></label>
                    <select>
                        <option>Todas</option>                                    
                    </select>
                </div>

                <div>
                    <label><?php i::_e("Estado"); ?></label>
                    <select>
                        <option>todos</option>                                    
                    </select>
                </div>

                <div>
                    <label><?php i::_e("Município"); ?></label>
                    <select>
                        <option>Todos</option>                                    
                    </select>
                </div>

                <div>
                    <label><?php i::_e("Bairro"); ?></label>
                    <select>
                        <option>Todos</option>                                    
                    </select>
                </div>
            </div>
               
        </div>
        <footer>
            <button class="btn btn-default" ng-click="data.graficData=false; data.graficType=true; data.reportModal=false" class="js-close"><?php i::_e("Cancelar"); ?></button>
            <button class="btn btn-default"  class="js-close"><?php i::_e("Salvar rascunho"); ?></button>
            <button class="btn btn-primary" ng-click="data.graficData=true; data.graficType=false"  class="js-close"><?php i::_e("Proxima etapa"); ?></button>
            <button class="btn btn-primary"  class="js-close"><?php i::_e("Gerar gráfico"); ?></button>
        </footer>

    </div>