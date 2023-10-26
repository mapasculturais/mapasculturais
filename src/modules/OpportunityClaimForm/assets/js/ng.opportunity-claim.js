(function (angular) {
    "use strict";
    var module = angular.module('ng.opportunity-claim', ['ngSanitize', 'checklist-model']);

    module.controller('OpportunityClaimController',['$scope', '$timeout', 'OpportunityClaimService',function($scope, $timeout, OpportunityClaimService){
        var labels = MapasCulturais.gettext.opportunityClaim;

        $scope.send = function(registration_id) {
            var message = $scope.data.message;
            MapasCulturais.opportunity_claim_ok = true;

            if(!message){
                MapasCulturais.Messages.error(labels.emptyMessage);
                MapasCulturais.opportunity_claim_ok = false;
            }

            if(MapasCulturais.opportunity_claim_ok) {
                OpportunityClaimService.send(message,registration_id).
                    success(function (data) {
                        $scope.data.message = '';
                    });
            }
        };
    }]);

    module.factory('OpportunityClaimService', ['$http', '$rootScope', function($http, $rootScope){
        var labels = MapasCulturais.gettext.opportunityClaim;
        var controllerId = null,
            baseUrl = MapasCulturais.baseURL.substr(-1) === '/' ?  MapasCulturais.baseURL : MapasCulturais.baseURL + '/';

        try{ controllerId = MapasCulturais.request.controller; }catch (e){};
        try{ entityId = MapasCulturais.entity.id; }catch (e){};

        return {
            controllerId: controllerId,

            getUrl: function(action){
                return baseUrl + controllerId + '/' + action;
            },

            send: function(message, registration_id) {
                return $http.post(this.getUrl('sendOpportunityClaimMessage'), {message: message, registration_id: registration_id}).
                    success(function(data, status){
                        MapasCulturais.Messages.success(labels.claimSended);
                    }).
                    error(function(data, status){
                        MapasCulturais.Messages.error(labels.claimSendError);
                    });
            }
        };
    }]);
})(angular);