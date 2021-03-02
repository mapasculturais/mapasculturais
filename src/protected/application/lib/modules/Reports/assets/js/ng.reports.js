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

            ReportsService.loading({opportunity: MapasCulturais.entity.id}).success(function (data, status, headers){
                
                var dataDisplayA = $scope.data.dataDisplayA;
                var dataDisplayB = $scope.data.dataDisplayB;
                
                var reportData = data.map(function(item, index){
                    return {
                        data:dataDisplayA[index],
                        graficType: item.reportData.typeGrafic
                    }
                });
                console.log(reportData)
                reportData.forEach(function(index){
                   
                });
                
            });
            
        })
        
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
                    }
                ]
            }

            ReportsService.create({reportData: reportData}).success(function (data, status, headers){    
                // console.log(data);            
                $scope.graficGenerate(data, configGrafic);
                $scope.data.reportData.type = '';
                $scope.data.reportData.title = '';
                $scope.data.reportData.description = '';
                $scope.data.reportData.dataDisplayA = '';
                $scope.data.reportData.dataDisplayB = '';
            });
        }

        $scope.nextStep = function () {
            var type = $scope.data.reportData.type;
            $scope.data.grafic = $scope.data.typeGraficDictionary[type];
            
        }

        $scope.graficGenerate = function(reportData, configGrafic) {
            
            $scope.data.reportData.titleDinamicGrafic = $scope.data.reportData.title;

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
                        display: function(context, ctx) {
                        },           
                        formatter: (value, ctx) => {
                            let sum = 0;
                            let dataArr = ctx.chart.data.datasets[0].data;
                            dataArr.map(data => {
                                sum += data;
                            });
    
                            let percentage = (value*100 / sum).toFixed(2)+"%";
                            
                            return value + " "+"("+percentage+") \n\n";
                        },
                        anchor:"end",
                        align: "end",                        
                    
                    }
                }
                }
            };
    
           
            var ctx = document.getElementById("dinamic-grafic").getContext('2d');
            
            document.querySelector('.dinamic-grafic').style.height = 'auto';
            
            ctx.canvas.width = 1000;
            ctx.canvas.height = 300;
        
            if(MapasCulturais.Charts.charts["dinamic-grafic"]){
                MapasCulturais.Charts.charts["dinamic-grafic"].destroy();
            }
            MapasCulturais.Charts.charts["dinamic-grafic"] = new Chart(ctx, config);
            $scope.data.reportModal = false;
            $scope.data.graficData = false;

            
            ReportsService.save({reportData: configGrafic}).success(function (data, status, headers){  
                 console.log(data);
            });
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