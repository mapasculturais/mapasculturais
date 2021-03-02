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
            dataDisplayA:[],
            dataDisplayB:[],
            state: {
                'owner': '(Agente Responsável)',
                'instituicao': '(Agente Instituição relacionada)',
                'coletivo': '(Agente Coletivo)',
                'space': '(Espaço)'
            },
            typeGraficDictionary: {pie: "Pizza", bar: "Barras", line: "Linha", table: "Tabela"},
            loadingGrafics:[]
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
            
                    
                    var legends = data.labels.map(function(item,index){
                        return {
                            color: data.backgroundColor[index],
                            value: item
                        };
                    });
                    
                    $scope.data.loadingGrafics.push({
                        identifier: index.identifier,                           
                        legends: legends,
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

            var config = {
                type: reportData.typeGrafic,
                data: {
                    labels: reportData.labels,
                    datasets: [{
                        label: '# of Votes',
                        data: reportData.data,
                        backgroundColor: reportData.backgroundColor,
                        borderColor: reportData.backgroundColor,
                        borderWidth: false
                    }]
                },
                options: {
                    responsive: true,
                    legend: false,
                    layout: {
                        padding: {                            
                            top: 65,
                            bottom: 30
                        },
                    },
                    plugins: {
                        datalabels: {     
                        display: false,
                    }
                }
                }
            };
            
            var divDinamic = !identifier ? "-" : "-"+identifier
            var ctx = document.getElementById("dinamic-grafic"+divDinamic).getContext('2d');
            
            document.querySelector('.dinamic-grafic'+divDinamic).style.height = 'auto';
            
            
            ctx.canvas.width = 1000;
            ctx.canvas.height = 300;
        
            if(MapasCulturais.Charts.charts["dinamic-grafic"+divDinamic]){
                MapasCulturais.Charts.charts["dinamic-grafic"+divDinamic].destroy();
            }
            MapasCulturais.Charts.charts["dinamic-grafic"+divDinamic] = new Chart(ctx, config);
            $scope.data.reportModal = false;
            $scope.data.graficData = false;

            ReportsService.save({reportData: configGrafic}).success(function (data, status, headers){  
                $scope.clearModal();
            });
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