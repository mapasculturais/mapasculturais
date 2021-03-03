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
            loadingGrafics:[],
            graphicColors: [],
            legends: []
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

        ReportsService.createHtmlLegends({opportunity_id: MapasCulturais.entity.id}).success(function (data, status, headers){
           

            var reportData = data.map(function(item, index){
                return {                        
                    identifier: item.identifier,
                    configGrafic:{
                        title: item.reportData.title,
                        description: item.reportData.description,
                        opportunity_id: item.reportData.opportunity_id,
                    },
                    reportData:{                           
                        graficType: item.reportData.typeGrafic,
                        dataA: item.reportData.columns[0], 
                        dataB: item.reportData.columns[1],                             
                    },
                }
            });
            reportData.forEach(function(index){
                ReportsService.create({reportData: index.reportData}).success(function (data, status, headers){ 
                    $scope.data.loadingGrafics.push({
                        title:index.configGrafic.title,
                        description: index.configGrafic.description,
                        identifier: index.identifier,
                        type: data.typeGrafic,                           
                    });
                });
            });

        });

        ReportsService.loading({opportunity_id: MapasCulturais.entity.id}).success(function (data, status, headers){

            var reportData = data.map(function(item, index){
                return {                        
                    identifier: item.identifier,
                    configGrafic:{
                        title: item.reportData.title,
                        description: item.reportData.description,
                        opportunity_id: item.reportData.opportunity_id,
                    },
                    reportData:{                           
                        graficType: item.reportData.typeGrafic,
                        dataA: item.reportData.columns[0], 
                        dataB: item.reportData.columns[1],                            
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
                $scope.graficGenerate(data, configGrafic);
            });
        }

        $scope.nextStep = function () {
            var type = $scope.data.reportData.type;
            $scope.data.grafic = $scope.data.typeGraficDictionary[type];
            
        }

        $scope.graficGenerate = function(reportData, configGrafic = false, identifier = false) {  
            $scope.data.reportData.titleDinamicGrafic =  configGrafic.title ?? $scope.data.reportData.title;
            
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
                    label: '# of Votes',
                    data: reportData.data,
                    backgroundColor: reportData.backgroundColor,
                    borderColor: reportData.backgroundColor,
                    borderWidth: false
                }];
            }

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

            var divDinamic = !identifier ? "-" : "-"+identifier

            
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
                reportData.labels.map(function(item,index){
                    var span = document.createElement("span");
                    var p = document.createElement("p");
                    var each = document.createElement("div");

                    span.style.backgroundColor = reportData.backgroundColor[index];
                    p.textContent = item;
                    
                    span.classList.add("dot");
                    each.classList.add("each");
                    legends.appendChild(each);
                    each.appendChild(span);
                    each.appendChild(p);
                });
            }

            var ctx = document.getElementById("dinamic-grafic"+divDinamic).getContext('2d');
        
            if(MapasCulturais.Charts.charts["dinamic-grafic"+divDinamic]){
                MapasCulturais.Charts.charts["dinamic-grafic"+divDinamic].destroy();
            }
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

        $scope.clearModal = function() {

            $scope.data.reportModal = false;
            $scope.data.graficData = false;
            $scope.data.reportData.type = '';
            $scope.data.reportData.title = '';
            $scope.data.reportData.description = '';
            $scope.data.reportData.dataDisplayA = '';
            $scope.data.reportData.dataDisplayB = '';

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
            createHtmlLegends: function (data) {
               
                var url = MapasCulturais.createUrl('reports', 'loadingGrafic', {});

                return $http.post(url, data).
                success(function (data, status, headers) {
                    $rootScope.$emit('registration.create', {message: "Reports found", data: data, status: status});
                }).
                error(function (data, status) {
                    $rootScope.$emit('error', {message: "Reports not found for this opportunity", data: data, status: status});
                });
            }
        };
    }]);

})(angular);


function openDropdown(dropId) {
    if ($("#drop-" + dropId.name).hasClass('active')) {
        $("#drop-" + dropId.name).removeClass('active');
    } else {
        $(".dropdown-content.active").removeClass('active');
        $("#drop-" + dropId.name).addClass('active');
    }
}