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
            typeGraphicDictionary: {pie: "Pizza", bar: "Barras", line: "Linha", table: "Tabela"},
            graphics:[]          
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
                
            var legendsToString = [];            
            data.forEach(function(item){
                if(item.reportData.typeGraphic != "pie"){
                    var total = $scope.sumSerie(item);
                    item.data.series.forEach(function(value, index){
                        legendsToString.push($scope.legendsToString(total, item, index));
                    });
                    item.data.tooltips = item.data.legends;
                    item.data.legends = legendsToString;
                }else{
                    item.data.data.forEach(function(value, index){
                        legendsToString.push($scope.legendsToString(value, item, index));
                    });
                    item.data.tooltips = item.data.labels;
                    item.data.labels = legendsToString;
                }
                
                legendsToString = [];
            });
            
            $scope.data.graphics = data;            
            $scope.graphicGenerate();
        });
        
        $scope.createGraphic = function() {            
            var indexA = $scope.data.reportData.dataDisplayA;
            var indexB = $scope.data.reportData.dataDisplayB;            
            var reportData = {
                typeGraphic:$scope.data.reportData.type,
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
                ],
            }

            ReportsService.save({reportData:reportData}).success(function (data, status, headers){

                $scope.data.graphics = $scope.data.graphics.filter(function (item) {
                    if (item.reportData.graphicId != data.graphicId) return item;
                });

                $scope.clearModal();
                $scope.data.creatingGraph = data;

            });
            
            ReportsService.loading({opportunity_id: MapasCulturais.entity.id, reportData:reportData}).success(function (data, status, headers){

                reportData.graphicId = $scope.data.creatingGraph.graphicId;

                var graphic = {
                    reportData: reportData,
                    identifier: $scope.data.creatingGraph.identifier,
                    data: data
                };
                
                var legendsToString = [];
                if(graphic.reportData.typeGraphic != "pie"){
                    graphic.data.series.forEach(function(value, index){
                        var total = $scope.sumSerie(graphic);
                        legendsToString.push($scope.legendsToString(total, graphic, index));
                    });
                    graphic.data.legends = legendsToString;
                }else{
                    graphic.data.data.forEach(function(value, index){
                        legendsToString.push($scope.legendsToString(value, graphic, index));
                    });
                    graphic.data.labels = legendsToString;
                }
              
                $scope.data.graphics.push(graphic);
                $scope.graphicGenerate();
                
                
            });
        }
        
        $scope.nextStep = function () {
            var type = $scope.data.reportData.type;
            $scope.data.graphic = $scope.data.typeGraphicDictionary[type];
        }

        $scope.createCsv = function (graphicId) {
           var url = MapasCulturais.createUrl('reports','csvDynamicGraphic', {graphicId: graphicId, opportunity_id:MapasCulturais.entity.id});
           document.location = url;
        }
        
        $scope.graphicGenerate = function() {
            var _datasets;
            $scope.data.graphics.forEach(function(item){  
                if(item.reportData.typeGraphic != "pie"){
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
                if(item.reportData.typeGraphic != "table"){
                    var config = {
                        type: item.reportData.typeGraphic,
                        data: {
                            labels: item.data.labels,
                            datasets: _datasets
                        },
                        options: {
                            responsive: true,
                            legend: false,
                            plugins: {
                                datalabels: {     
                                    display: false,
                                }
                            },
                            tooltips: {
                                callbacks: {
                                    title: function(tooltipItem, data) {
                                        return item.data.tooltips[tooltipItem[0].index];
                                    },
                                    label: function(tooltipItem, data) {
                                        console.log(tooltipItem)
                                        let value = data.datasets[0].data[tooltipItem.index];
                                        let sum = 0;
                                        let dataset = data['datasets'][0].data;
                                        dataset.map(data => {
                                            sum += data;
                                        });

                                        let percentage = (value*100 / sum).toFixed(2)+"%";
                                        return value + "\n"+"("+percentage+")";
                                    }
                                }
                            }
                        }
                    };
    
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
            
            if(item.reportData.typeGraphic != "pie"){
                var sum = $scope.sumData(item.data.series[index]);
                return item.data.series[index].label +'\n'+ sum + " ("+ ((sum/value)*100).toFixed(2)+"%)";
            }else{
                var sum = $scope.sumData(item.data);
                return item.data.labels[index] +'\n'+ value + " ("+ ((value/sum)*100).toFixed(2)+"%)";
            }
        }

        $scope.sumData = function(reportData){

            var sum = 0;
            reportData.data.forEach(function(item){
                sum += item;
            })

            return sum;
        }

        $scope.sumSerie = function(reportData){
          
            var sum = 0;
            var total = 0;
            reportData.data.series.forEach(function(item){
                sum = $scope.sumData(item);
                        total = total + sum;
            });
            return total;
        }

        $scope.getLabelColor = function(graphic, index){
            if(graphic.reportData.typeGraphic != "pie"){               
                return graphic.data.series[index].colors;
            }else{
                return graphic.data.backgroundColor[index];
            }
        }

        $scope.clearModal = function() {
            $scope.data.reportModal = false;
            $scope.data.graphicData = false;
            $scope.data.graphicType = true;
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
            save: function (data) {
               
                var url = MapasCulturais.createUrl('reports', 'saveGraphic', {});

                return $http.post(url, data).
                success(function (data, status, headers) {
                    $rootScope.$emit('registration.create', {message: "Reports found", data: data, status: status});
                }).
                error(function (data, status) {
                    $rootScope.$emit('error', {message: "Reports not found for this opportunity", data: data, status: status});
                });
            },
            loading: function (data) {
               
                var url = MapasCulturais.createUrl('reports', 'loadingGraphic', {});

                return $http.post(url, data).
                success(function (data, status, headers) {
                    $rootScope.$emit('registration.create', {message: "Reports found", data: data, status: status});
                }).
                error(function (data, status) {
                    $rootScope.$emit('error', {message: "Reports not found for this opportunity", data: data, status: status});
                });
            },
            delete: function (data) {

                var url = MapasCulturais.createUrl('reports', 'graphic', {'graphicId': data});
                
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
