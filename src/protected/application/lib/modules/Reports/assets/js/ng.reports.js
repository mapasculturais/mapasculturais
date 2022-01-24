(function (angular) {
    "use strict";
    var module = angular.module('ng.reports', []);
    
    module.config(['$httpProvider', function ($httpProvider) {
        $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
        $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';
        $httpProvider.defaults.transformRequest = function (data) {
            var result = angular.isObject(data) && String(data) !== '[object File]' ? $.param(data) : data;

            return result;
        };
    }]);

    module.controller('Reports',['$scope', 'ReportsService','$window', function($scope, ReportsService, $window){
        
        $scope.data = {
            dataForm: {},
            reportModal: false,
            graphicType: true,
            graphicData:false,            
            creatingGraph: false,
            dataDisplayA:[],
            dataDisplayB:[],
            state: {
                'owner': '(Agente Responsável)',
                'instituicao': '(Agente Instituição relacionada)',
                'coletivo': '(Agente Coletivo)',
                'space': '(Espaço)'
            },
            error: false,
            typeGraphicDictionary: {pie: "Pizza", bar: "Coluna", line: "Linha", table: "Tabela"},
            graphics:[],
            groupData: false
        };

        $scope.statuses = [
            {value : 'all', title : 'Mostrar todas'},
            {value : 'draft', title : 'Somente em rascunho'},
            {value : 'approved', title : 'Somente Aprovados'}
        ];

        $scope.reportFilter = MapasCulturais.reportStatus;


        ReportsService.findDataOpportunity().success(function (data, status, headers){
            var dataOpportunity = angular.copy(data);

            $scope.data.dataDisplayA =  dataOpportunity.map(function(index){
              
                if(index.label == "Estado"){
                    index.label = index.label+" " + $scope.data.state[index.source.type];
                    return index;
                }else{
                    return index
                }                
            });

            $scope.data.dataDisplayB =  dataOpportunity.map(function(index){
              
                if(index.label == "Estado"){
                    index.label = index.label+" " + $scope.data.state[index.source.type];
                    return index;
                }else{
                    return index
                }                
            });
        });

        $scope.setReportFilter = function(){

            var route = MapasCulturais.createUrl('opportunity', 'single', {id: MapasCulturais.entity.id, status: $scope.reportFilter});

            window.location = route+"#/tab=reports";
           
        }

        ReportsService.getData({opportunity_id: MapasCulturais.entity.id, status: $scope.reportFilter}).success(function (data, status, headers){

            var legendsToString = [];            
            data.forEach(function(item){
               
                if(item.typeGraphic != "pie"){
                    var total = $scope.sumSerie(item);
                    item.data.series.forEach(function(value, index){
                        var color = MapasCulturais.getChartColors();
                        value.colors = color[0];
                        legendsToString.push($scope.legendsToString(total, item, index));
                    });
                    item.data.tooltips = item.data.legends;
                    item.data.legends = legendsToString;
                }else{
                    item.data.data.forEach(function(value, index){
                        
                        legendsToString.push($scope.legendsToString(value, item, index));
                       
                    });
                    item.data.backgroundColor = MapasCulturais.getChartColors(item.data.data.length);
                    item.data.tooltips = item.data.labels;
                    item.data.labels = legendsToString;
                }
                
                legendsToString = [];
            });
            $scope.data.graphics = data;
            $scope.checkTableGraphic();
            $scope.graphicGenerate();
        });
        
        $scope.createGraphic = function() {            
            var indexA = $scope.data.dataForm.dataDisplayA;
            var indexB = $scope.data.dataForm.dataDisplayB; 
            var fieldA = indexA ? $scope.data.dataDisplayA[indexA].label : "";
            var fieldB = indexB ? " x " +$scope.data.dataDisplayB[indexB].label : "";        
            var config = {
                typeGraphic:$scope.data.dataForm.type,
                groupData:$scope.data.groupData,
                opportunity_id: MapasCulturais.entity.id,
                title: $scope.data.dataForm.title,
                description: $scope.data.dataForm.description,
                fields: fieldA + fieldB,
                columns:[
                    {
                        source: $scope.data.dataDisplayA[indexA].source,
                        value: $scope.data.dataDisplayA[indexA].value
                    },
                    {
                        source: indexB ? $scope.data.dataDisplayB[indexB].source : "",
                        value:  indexB ? $scope.data.dataDisplayB[indexB].value : ""
                    }
                ],
            }
            ReportsService.save(config, $scope.reportFilter).success(function (data, status, headers){
                
              
                if (data.error) {
                    $scope.clearModal();
                    MapasCulturais.Messages.error("Dados insuficientes para gerar a visualização desse gráfico");
                    $scope.data.error = data.error;
                    return;
                }
                
                $scope.data.graphics = $scope.data.graphics.filter(function (item) {
                    if (item.reportData.graphicId != data.graphicId) return item;
                });

                $scope.clearModal();
                $scope.data.creatingGraph = data;

            });

            ReportsService.getData({opportunity_id: MapasCulturais.entity.id, reportData:config, status: $scope.reportFilter}).success(function (data, status, headers){
                config.graphicId = $scope.data.creatingGraph.graphicId;
                var graphic = {
                    columns: config.columns,
                    data: data,
                    description: config.description,
                    fields: config.fields,
                    identifier: $scope.data.creatingGraph.identifier,
                    opportunity_id: MapasCulturais.entity.id,
                    reportData: config,
                    title: config.title,
                    typeGraphic: config.typeGraphic
                };
                
                var legendsToString = [];
                if(graphic.typeGraphic != "pie"){                    
                    graphic.data.series.forEach(function(value, index){
                        var color = MapasCulturais.getChartColors();
                        value.colors = color[0];
                        var total = $scope.sumSerie(graphic);
                        legendsToString.push($scope.legendsToString(total, graphic, index));
                    });
                    graphic.data.tooltips = graphic.data.legends;
                    graphic.data.legends = legendsToString;
                }else{
                    graphic.data.data.forEach(function(value, index){
                        legendsToString.push($scope.legendsToString(value, graphic, index));
                    });
                    graphic.data.backgroundColor = MapasCulturais.getChartColors(graphic.data.data.length);
                    graphic.data.tooltips = graphic.data.labels;
                    graphic.data.labels = legendsToString;
                }

                if (!$scope.data.error) {
                    $scope.data.graphics.push(graphic);
                    $scope.graphicGenerate();
                    $scope.checkTableGraphic();
                }
            });
        }
        
        $scope.nextStep = function () {
            var type = $scope.data.dataForm.type;
            $scope.data.graphic = $scope.data.typeGraphicDictionary[type];
        }

        $scope.createCsv = function (graphicId) {
           var url = MapasCulturais.createUrl('reports','csvDynamicGraphic', {graphicId: graphicId, opportunity_id:MapasCulturais.entity.id});
           document.location = url;
        }

        //Calcula a largura em porcentagem para o gráfico, baseado na quantidade de dados
        $scope.widthCalc = function(data){
            if(data < 30 || (data * 2) < 100){
                return  100;  
            }else{
               return (data * 2);
            }
        }

        // Checa se é necessário quebrar o grafico de tabela em varios gráficos
       $scope.checkTableGraphic = function(){   
           
            if(!MapasCulturais.isPrintReport){
                return;
            }
            
            var scopeGraphic = angular.copy($scope.data.graphics); 

            scopeGraphic.forEach(function(item, indexScope){             
                if(item.typeGraphic == "table"){
                    $scope.data.graphics.splice(indexScope, 1);
                    var limit = 10;
                    var soma = Math.floor( item.data.labels.length / limit );
                    var resto = ( ( item.data.labels.length % limit ) > 0 ) ? 1 : 0;
                    var qtdGraphic = soma + resto;
                    var newGraphics = [];

                    for ( var i = 0; i < qtdGraphic; i++ ) {
                        var copy = angular.copy(item);

                        if(i < (qtdGraphic -1)){
                            copy.data.legends = {}
                        }

                        newGraphics.push(copy);                                              
                    }

                    newGraphics.forEach(function(grafic, index){
                        grafic.identifier = grafic.identifier+"-"+index;
                        var min = index * limit
                        var max = (min) + limit;
                        var newLabels = grafic.data.labels.slice(min, max);
                        grafic.data.labels = newLabels;
                    }); 

                    newGraphics.forEach(function(item, index){
                        item.data.series.forEach(function(serie){
                            var min = index * limit
                            var max = (min) + limit;
                            var newData = serie.data.slice(min, max);
                            serie.data = newData;
                        });                      
                    }); 

                    newGraphics.forEach(function(item, index){
                        item.graphBreak = (qtdGraphic > 1) ? true : false;
                        item.totalBreak = qtdGraphic;
                        item.graphPart = (index+1);
                        $scope.data.graphics.push(item)
                    });
                   

                }

                
            });
       }        
        
        $scope.graphicGenerate = function() {
            var _datasets;
            $scope.data.graphics.forEach(function(item, index){
                if(item.typeGraphic == "table"){
                    var sumLines = [];
                    var sumColumns = [];
                    item.data.series.forEach(function(serie){
                        var sum = 0;
                        serie.data.forEach(function(value){
                            sum = sum+value;
                        });
                        sumLines.push(sum);
                    });
                    
                    item.data.series.forEach(function(serie, line){
                        if(line == 0){
                            for(var i=0; i< serie.data.length; i++){
                                sumColumns[i] = 0;
                            }
                        }
                       
                        serie.data.forEach(function(value, column){
                            sumColumns[column] = sumColumns[column]+serie.data[column];
                        });
                        
                    });
                    var total = sumColumns.reduce(function(ac, value) {
                        return ac + value;
                    });
                    
                    item.data.total = total;
                    item.data.sumLines = sumLines;
                    item.data.sumColumns = sumColumns;             
                    
                }
                if(item.typeGraphic != "pie"){
                    
                    $scope.data.graphics[index].countData = $scope.widthCalc(item.data.series[0].data.length);

                    _datasets = item.data.series.map(function (serie){
                       return {                             
                            label: serie.label,
                            backgroundColor: serie.colors,
                            borderColor: serie.colors,
                            type: serie.type,
                            fill: serie.fill,
                            data: serie.data
                        }
                    });
                }else{
                    _datasets = [{
                        label: '',
                        data: item.data.data,
                        backgroundColor: item.data.backgroundColor,
                        borderColor: item.data.backgroundColor,
                        borderWidth: false
                    }];

                }

                var stacked = false;
                if(item.groupData === "true" || item.reportData.groupData === true){
                    stacked = true;
                }
                
                if(item.typeGraphic != "table"){
                    var config = {
                        type: item.typeGraphic,
                        data: {
                            labels: item.data.labels,
                            datasets: _datasets
                        },
                        options: {
                            responsive: true,
                            scales: {
                                xAxes: [{
                                   stacked: stacked 
                                }],
                                yAxes: [{
                                   stacked: stacked,
                                }]
                             },
                            legend: false,
                            plugins: {
                                datalabels: {     
                                    display: false,
                                }
                            },
                        }
                    };

                    // Altera config para o gráfico de linhas
                    if (item.typeGraphic == "line") {

                        config.options.scales = {
                            xAxes: [{
                                gridLines: {
                                    display: false
                                }
                            }],
                            yAxes: [{
                                gridLines: {
                                    borderDash: [5, 5],
                                }
                            }]                                
                        };

                        config.options.tooltips = {
                        
                            // Desabilita o tooltip padrão
                            enabled: false,

                            // Adiciona o tooltip personalizado
                            custom: function (tooltipModel) {

                                // Tooltip wrap
                                var tooltipWrap = document.getElementById('chartjs-tooltip-dynamic');

                                // Cria o tooltip na primeira renderização
                                if (!tooltipWrap) {
                                    tooltipWrap = document.createElement('div');
                                    tooltipWrap.id = 'chartjs-tooltip-dynamic';
                                    tooltipWrap.innerHTML = '<section></section><div class="point-tooltip"></div>';
                                    document.body.appendChild(tooltipWrap);
                                }

                                // Exibe o tooltip apenas no hover
                                if (tooltipModel.opacity === 0) {
                                    tooltipWrap.style.opacity = 0;
                                    return;
                                }

                                // Retorna os itens do tooltip
                                function getBody(bodyItem) {
                                    return bodyItem.lines;
                                }

                                // Define o conteúdo do tooltip
                                if (tooltipModel.body) {

                                    var bodyLines = tooltipModel.body.map(getBody);

                                    var innerHtml = '<div class="custom-tooltip">';
                                    bodyLines.forEach(function (body, i) {
                                        innerHtml += '<span><b>' + body + '</b></span>';
                                    });
                                    innerHtml += '</div>';

                                    var tooltipContent = tooltipWrap.querySelector('section');
                                    tooltipContent.innerHTML = innerHtml;

                                }

                                // Section do tooltip
                                tooltipContent.style.backgroundColor = 'rgba(17,17,17,0.8)';
                                tooltipContent.style.padding = '15px';

                                // Seta inferior do tooltip
                                var pointTooltip = tooltipWrap.querySelector('.point-tooltip');
                                pointTooltip.style.width = 0;
                                pointTooltip.style.height = 0;
                                pointTooltip.style.borderLeft = '10px solid transparent';
                                pointTooltip.style.borderRight = '10px solid transparent';
                                pointTooltip.style.borderTop = '10px solid rgba(17,17,17,0.8)';
                                pointTooltip.style.margin = '0 auto';

                                // Posição do tooltip
                                var position = this._chart.canvas.getBoundingClientRect();

                                // Posicionamento e demais personalizações do tooltip
                                tooltipWrap.style.opacity = 1;
                                tooltipWrap.style.position = 'absolute';
                                tooltipWrap.style.left = ((position.left + window.pageXOffset + tooltipModel.caretX) - tooltipWrap.offsetWidth / 2) + 'px';
                                tooltipWrap.style.top = ((position.top + window.pageYOffset + tooltipModel.caretY) - tooltipWrap.offsetHeight - 25) + 'px';
                                tooltipWrap.style.fontSize = '14px';
                                tooltipWrap.style.color = '#ffffff';
                                tooltipWrap.style.pointerEvents = 'none';

                            }
                        
                        }

                    }

                    var divDynamic = !item.identifier ? "" : item.identifier;
                    setTimeout(function() {
                        var ctx = document.getElementById("dynamic-graphic-"+divDynamic).getContext('2d');
                        MapasCulturais.Charts.charts["dynamic-graphic"+divDynamic] = new Chart(ctx, config);
                    },2000);
                }
            });
        }

        $scope.deleteGraphic = function (id) {
            
            if (!confirm("Você tem certeza que deseja deletar esse gráfico?")) {
                return;
            }

            ReportsService.delete(id).success(function () {

                $scope.data.graphics = $scope.data.graphics.filter(function (item) {
                    if (item.reportData.graphicId != id) return item;
                });

                MapasCulturais.Messages.success("Gráfico deletado com sucesso");
            });
        }

        $scope.legendsToString = function(value,item, index){
            
            if(item.typeGraphic != "pie"){
                var sum = $scope.sumData(item.data.series[index]);
                return item.data.series[index].label +'\n'+ sum + " ("+ ((sum/value)*100).toFixed(2)+"%)";
            }else{
                var sum = $scope.sumData(item.data);
                return item.data.labels[index] +'\n'+ value + " ("+ ((value/sum)*100).toFixed(2)+"%)";
            }
        }

        $scope.sumData = function(grafic){

            var sum = 0;
            grafic.data.forEach(function(item){
                sum += item;
            })

            return sum;
        }

        $scope.sumSerie = function(grafic){
          
            var sum = 0;
            var total = 0;
            grafic.data.series.forEach(function(item){
                sum = $scope.sumData(item);
                        total = total + sum;
            });
            return total;
        }

        $scope.getLabelColor = function(graphic, index){
            if(graphic.typeGraphic != "pie"){               
                return graphic.data.series[index].colors;
            }else{
                return graphic.data.backgroundColor[index];
            }
        }

        $scope.clearModal = function() {
            $scope.data.reportModal = false;
            $scope.data.graphicData = false;
            $scope.data.graphicType = true;
            $scope.data.checked = false;
            $scope.data.groupData = false;
            $scope.data.dataForm.type = '';
            $scope.data.dataForm.title = '';
            $scope.data.dataForm.description = '';
            $scope.data.dataForm.dataDisplayA = '';
            $scope.data.dataForm.dataDisplayB = '';
        }

    }]);
    
    module.factory('ReportsService', ['$http', '$rootScope', 'UrlService', function ($http, $rootScope, UrlService) {  
        return {  
            findDataOpportunity: function (data) {
                var url = MapasCulturais.createUrl('reports', 'dataOpportunityReport', {opportunity_id: MapasCulturais.entity.id});

                return $http.get(url, data).
                success(function (data, status, headers) {
                    $rootScope.$emit('registration.create', {message: "Reports found", data: data, status: status});
                }).
                error(function (data, status) {
                    $rootScope.$emit('error', {message: "Reports not found for this opportunity", data: data, status: status});
                });
            },
            save: function (data, status) {
               
                var url = MapasCulturais.createUrl('reports', 'saveGraphic', {opportunity_id: MapasCulturais.entity.id, status: status});

                return $http.post(url, data).
                success(function (data, status, headers) {
                    $rootScope.$emit('registration.create', {message: "Reports found", data: data, status: status});
                }).
                error(function (data, status) {
                    $rootScope.$emit('error', {message: "Reports not found for this opportunity", data: data, status: status});
                });
            },
            getData: function (data) {

                var url = MapasCulturais.createUrl('reports', 'getGraphic', {opportunity_id: MapasCulturais.entity.id, status: data.status});

                return $http.get(url, {params:data}).
                success(function (data, status, headers) {
                    $rootScope.$emit('registration.create', {message: "Reports found", data: data, status: status});
                }).
                error(function (data, status) {
                    $rootScope.$emit('error', {message: "Reports not found for this opportunity", data: data, status: status});
                });
            },
            delete: function (data) {

                var url = MapasCulturais.createUrl('metalist', 'single', [data]);
                
                return $http.delete(url, data).
                    success(function (data, status, headers) {
                        $rootScope.$emit('reports.remove', { message: "Reports deleted", data: data, status: status });
                    }).error(function (data, status) {
                        $rootScope.$emit('error', { message: "Reports not deleted for this opportunity", data: data, status: status });
                    });
            },
        };
    }]);

})(angular);
