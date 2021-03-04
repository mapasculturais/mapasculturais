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
            reportData: {},
            reportModal: false,
            graficType: true,
            graficData:false,            
            creatingGraph: false,
            dataDisplayA:[],
            dataDisplayB:[],
            state: {
                'owner': '(Agente Responsável)',
                'instituicao': '(Agente Instituição relacionada)',
                'coletivo': '(Agente Coletivo)',
                'space': '(Espaço)'
            },
            typeGraficDictionary: {pie: "Pizza", bar: "Barras", line: "Linha", table: "Tabela"},
            graphicColors: [],
            legends: [],
            type: ""
        };

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
        
        ReportsService.loading({opportunity_id: MapasCulturais.entity.id}).success(function (data, status, headers){                     

            var reportData = data.map(function(item, index){
                return {                        
                    identifier: item.value.identifier,
                    configGrafic:{
                        graficId:item.id,
                        title: item.value.reportData.title,
                        description: item.value.reportData.description,
                        opportunity_id: item.value.reportData.opportunity_id,
                    },
                    reportData:{                           
                        graficType: item.value.reportData.typeGrafic,
                        dataA: item.value.reportData.columns[0], 
                        dataB: item.value.reportData.columns[1],                            
                    },
                }
            });
            
            reportData.forEach(function(index){
                ReportsService.create({reportData: index.reportData}).success(function (data, status, headers){                    
                    $scope.graficGenerate(data, index.configGrafic, index.identifier);
                });
            });
        });
        
        $scope.createGrafic = function() {

            if(!($scope.data.reportData.title) || !($scope.data.reportData.description)){
                MapasCulturais.Messages.error("Defina um título e uma descrição para esse gráfico");                
                return;
            }
           
            var indexA = $scope.data.reportData.dataDisplayA;
            var indexB = $scope.data.reportData.dataDisplayB;
            
            var reportData = {
                graficType: $scope.data.reportData.type,
                dataA: $scope.data.dataDisplayA[indexA],
                dataB: $scope.data.dataDisplayB[indexB]
            };
            
            var configGrafic = {
                typeGrafic:$scope.data.reportData.type,
                opportunity_id: MapasCulturais.entity.id,
                title: $scope.data.reportData.title,
                description: $scope.data.reportData.description,
                columns:[
                    {
                        source: $scope.data.dataDisplayA[indexA].source,
                        value: $scope.data.dataDisplayA[indexA].value
                    },
                    {
                        source: indexB ? $scope.data.dataDisplayB[indexB].source : "",
                        value:  indexB ? $scope.data.dataDisplayB[indexB].value : ""
                    }
                ]
            }

            ReportsService.create({reportData: reportData}).success(function (data, status, headers){
                $scope.data.reportData.titleDinamicGrafic =  $scope.data.reportData.title;
                $scope.graficGenerate(data, configGrafic);
            });
        }

        $scope.nextStep = function () {
            var type = $scope.data.reportData.type;
            $scope.data.grafic = $scope.data.typeGraficDictionary[type];
            
        }

        $scope.graficGenerate = function(reportData, configGrafic = false, identifier = false) {
            if(reportData.typeGrafic == "line"){
                var serie = reportData.series.map(function (item, index){
                    return {
                        label: item.label,
                        backgroundColor: item.colors,
                        borderColor:item.colors,
                        type: item.type,
                        fill: item.fill,
                        data: item.data
                    }
                })

                var _datasets = serie;               
            }else{
                var _datasets = [{
                    label: '',
                    data: reportData.data,
                    backgroundColor: reportData.backgroundColor,
                    borderColor: reportData.backgroundColor,
                    borderWidth: false
                }];
            }

            $scope.data.type = reportData.typeGrafic;
            var config = {
                type: reportData.typeGrafic,
                data: {
                    labels: reportData.labels,
                    datasets: _datasets
                },
                options: {
                    responsive: true,
                    legend: false,
                    plugins: {
                        datalabels: {     
                            display: false,
                        }
                    }
                }
            };
            $scope.data.legends = [];

            var divDinamic = !identifier ? "-"+Math.random().toString(36).substr(2, 9) : "-"+identifier;

            $scope.createHtmlGraphic("new-grafic", reportData, divDinamic, configGrafic);
            
            $scope.createHtmlLegends(reportData, divDinamic);

            document.getElementById("dinamic-legends"+divDinamic);
            
            var ctx = document.getElementById("dinamic-grafic"+divDinamic).getContext('2d');
        
            MapasCulturais.Charts.charts["dinamic-grafic"+divDinamic] = new Chart(ctx, config);
            $scope.data.reportModal = false;
            $scope.data.graficData = false;

            if(!identifier){
                ReportsService.save({reportData: configGrafic}).success(function (data, status, headers){  
                    $scope.clearModal();
                    $scope.data.creatingGraph = true;
                });
            }
        }

        $scope.sumData = function(reportData){
            var sum = 0;
            reportData.data.forEach(function(item){
                sum += item;
            });
            
            return sum;
        }

        $scope.clearModal = function() {

            $scope.data.reportModal = false;
            $scope.data.graficData = false;
            $scope.data.reportData.type = '';
            $scope.data.reportData.title = '';
            $scope.data.reportData.description = '';
            $scope.data.reportData.dataDisplayA = '';
            $scope.data.reportData.dataDisplayB = '';

        }

        $scope.createHtmlLegends = function(reportData, divDinamic){
            var legends = document.getElementById("dinamic-legends"+divDinamic);            
            if(reportData.typeGrafic == "line"){
                reportData.series.map(function(item,index){
                    var span = document.createElement("span");
                    var p = document.createElement("p");
                    var each = document.createElement("div");

                    span.style.backgroundColor = item.colors;
                    p.textContent = item.label;
                    
                    span.classList.add("dot");
                    each.classList.add("each");
                    legends.appendChild(each);
                    each.appendChild(span);
                    each.appendChild(p);
                });

            }else{
                var sum = 0;
                reportData.labels.map(function(item,index){
                    var sum = $scope.sumData(reportData);
                    var value = reportData.data[index];
                    var span = document.createElement("span");
                    var p = document.createElement("p");
                    var each = document.createElement("div");

                    span.style.backgroundColor = reportData.backgroundColor[index];
                    p.innerHTML += item +'<br>'+ value + " ("+ ((value/sum)*100).toFixed(2)+"%)";
                    
                    span.classList.add("dot");
                    each.classList.add("each");
                    legends.appendChild(each);
                    each.appendChild(span);
                    each.appendChild(p);
                });
            }
           
        }

        $scope.deleteGraphic = function(data) {

            if (!confirm("Você tem certeza que deseja deletar esse gráfico?")) {
                return;
            }

            ReportsService.remove(data).success(function(data){

                $scope.data.loadingGrafics = $scope.data.loadingGrafics.filter(function(item) {
                    if (item.graficId != data) return item;
                });

                MapasCulturais.Messages.success("Gráfico deletado com sucesso");
                
            });
            
        }

        $scope.sumData = function(reportData){
            var sum = 0;
            reportData.data.forEach(function(item){
                sum += item;
            })

            return sum;
           
        }

        $scope.createHtmlGraphic = function(element, reportData, identifier, configGrafic){
            
            var newGrafic = document.getElementById(element);
            /**
             * Cria os elementos
             */
            var chartWrap = document.createElement("div");            
            var header = document.createElement("header");
            var title = document.createElement("h3");
            var button = document.createElement("a");
            var chartContainer = document.createElement("div");
            var canvas = document.createElement("canvas");                
            var footer = document.createElement("footer");
            var legendsChats = document.createElement("div");

            /**
             * Nível 1
             */
            chartWrap.appendChild(header);
            chartWrap.appendChild(chartContainer);
            chartWrap.appendChild(footer);

            /**
             * Nível 2
             */
            header.appendChild(title);
            header.appendChild(button);
            chartContainer.appendChild(canvas);
            footer.appendChild(legendsChats);

            /**
             * Atributos
             */
            chartWrap.style.height = "auto";
            chartWrap.classList.add("chart-wrap")
            button.classList.add('hltip','download');
            chartContainer.classList.add('chart-container','chart-'+reportData.typeGrafic);
            chartContainer.style.position = "relative";
            canvas.id = "dinamic-grafic"+identifier;
            legendsChats.classList.add("legends-chats");
            legendsChats.id = "dinamic-legends"+identifier;
            title.textContent = configGrafic.title;
            
            /**
             * Condicionais
             */  

            chartContainer.style.width = (reportData.typeGrafic == "pie") ? "60%" : "100%";
            
            newGrafic.appendChild(chartWrap);
        }
       
    }]);
    
    module.factory('ReportsService', ['$http', '$rootScope', 'UrlService', function ($http, $rootScope, UrlService) {  
        return {  
            findDataOpportunity: function (data) {
               
                var url = MapasCulturais.createUrl('reports', 'dataOpportunityReport', {opportunity_id: MapasCulturais.entity.id});

                return $http.post(url, data).
                success(function (data, status, headers) {
                    $rootScope.$emit('registration.create', {message: "Reports found", data: data, status: status});
                }).
                error(function (data, status) {
                    $rootScope.$emit('error', {message: "Reports not found for this opportunity", data: data, status: status});
                });
            },          
            create: function (data) {
               
                var url = MapasCulturais.createUrl('reports', 'createGrafic', {opportunity_id: MapasCulturais.entity.id});

                return $http.post(url, data).
                success(function (data, status, headers) {
                    $rootScope.$emit('registration.create', {message: "Reports found", data: data, status: status});
                }).
                error(function (data, status) {
                    $rootScope.$emit('error', {message: "Reports not found for this opportunity", data: data, status: status});
                });
            },
            save: function (data) {
               
                var url = MapasCulturais.createUrl('reports', 'saveGrafic', {});

                return $http.post(url, data).
                success(function (data, status, headers) {
                    $rootScope.$emit('registration.create', {message: "Reports found", data: data, status: status});
                }).
                error(function (data, status) {
                    $rootScope.$emit('error', {message: "Reports not found for this opportunity", data: data, status: status});
                });
            },
            loading: function (data) {
               
                var url = MapasCulturais.createUrl('reports', 'loadingGrafic', {});

                return $http.post(url, data).
                success(function (data, status, headers) {
                    $rootScope.$emit('registration.create', {message: "Reports found", data: data, status: status});
                }).
                error(function (data, status) {
                    $rootScope.$emit('error', {message: "Reports not found for this opportunity", data: data, status: status});
                });
            },
            remove: function (data) {

                var url = MapasCulturais.createUrl('reports', 'deleteGraphic', { opportunity_id: MapasCulturais.entity.id, graphic_id: data });
                
                return $http.delete(url).
                    success(function (data, status, headers) {
                        $rootScope.$emit('reports.remove', { message: "Reports deleted", data: data, status: status });
                    }).error(function (data, status) {
                        $rootScope.$emit('error', { message: "Reports not deleted for this opportunity", data: data, status: status });
                    });

            }
            
        };
    }]);

})(angular);