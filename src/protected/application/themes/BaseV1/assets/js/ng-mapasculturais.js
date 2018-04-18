//Inicia o mapa para a busca e configura o timeout do scroll
jQuery(document).ready(function() {

    if ($('body').hasClass('action-search')) {
        MapasCulturais.Map.initialize({mapSelector: '.js-map', locateMeControl: false, exportToGlobalScope: true, mapCenter: MapasCulturais.mapCenter});
    }

});


(function(angular) {
    var app = angular.module('ng-mapasculturais', []);
    app.config(["$httpProvider", function($httpProvider) {
        $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
        $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';
        $httpProvider.defaults.transformRequest = function (data) {
            var result = angular.isObject(data) && String(data) !== '[object File]' ? $.param(data) : data;

            return result;
        };
    }]);

    app.factory('loginService', ['$rootScope', '$http', '$location', function ($rootScope, $http, $location) {
        $rootScope.after_login = $location.absUrl();
        $rootScope.$watch(function() {
            return $location.absUrl();
        }, function () {
            $rootScope.after_login = $location.absUrl();
        });

        return {
            setLastUrl: function () {
                var endPoint = MapasCulturais.createUrl('panel', 'setUrlCookie');
                var params   = {redirect_url_auth: $rootScope.after_login};
                var auth_url = $("#main-nav ul.menu li.login a").attr('data-auth');

                $http.post(endPoint, params).then(function(){
                    window.location = auth_url;
                });
            }
        };
    }]);
})(angular);
