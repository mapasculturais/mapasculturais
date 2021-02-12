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
            reportsData: [],
            reportModal: false,
            graficType: true,
            graficData:false,
            ageRange: [               
                {range:"0 - 4", value: "0:4"},
                {range:"5 - 9", value: "5:9"},
                {range:"10 - 14", value: "10:14"},
                {range:"15 - 19", value: "15:19"},
                {range:"20 - 24", value: "20:24"},
                {range:"25 - 29", value: "25:29"},
                {range:"30 - 34", value: "30:34"},
                {range:"35 - 39", value: "35:39"},
                {range:"40 - 44", value: "40:44"},
                {range:"45 - 49", value: "45:49"},
                {range:"50 - 54", value: "50:54"},
                {range:"55 - 59", value: "55:59"},
                {range:"60 - 64", value: "60:64"},
                {range:"65 - 69", value: "65:69"},
                {range:"70 - 74", value: "70:74"},
                {range:"75 - 79", value: "75:79"},
                {range:"80 ou mais", value: "80"},
            ]
        };
        
        ReportsService.find({opportunity_id:MapasCulturais.entity.id}).success(function (data, status, headers){
            $scope.data.reportsData = data;           
        });        
       
    }]);
    
    module.factory('ReportsService', ['$http', '$rootScope', 'UrlService', function ($http, $rootScope, UrlService) {  
        return {
            find: function (data) {
                
                var url = MapasCulturais.createUrl('reports', 'dataOportunityReport', {opportunity:MapasCulturais.entity.id});
                
                return $http.get(url, data).
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
