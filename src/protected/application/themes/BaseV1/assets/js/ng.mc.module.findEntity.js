(function (angular) {
    "use strict";

    angular.module('mc.module.findEntity', [])
        .factory('FindService', ['$rootScope', '$http', '$q', function ($rootScope, $http, $q) {
                var baseUrl = MapasCulturais.baseURL + '/api/';
                var canceller;

                function extend(query) {
                    return angular.extend(query, {
                        "@select": 'id,name,type,shortDescription,terms',
                        "@files": '(avatar.avatarSmall):url',
                        "@order": 'name'
                    });
                }
                ;

                function request(url, query, success_cb, error_cb) {
                    cancelRequest();

                    query = extend(query);


                    canceller = $q.defer();

                    var p = $http({
                        url: url,
                        method: "GET",
                        timeout: canceller.promise,
                        cache: true,
                        params: query
                    });

                    if (angular.isFunction(success_cb)) {
                        p.success(success_cb);
                    }

                    if (angular.isFunction(error_cb)) {
                        p.error(error_cb);
                    }
                }
                ;

                function cancelRequest() {
                    if (canceller) {
                        canceller.resolve();
                    }
                }

                return {
                    cancel: function () {
                        cancelRequest();
                    },
                    find: function (entity, query, success_cb, error_cb) {
                        var url = baseUrl + entity + '/find';

                        request(url, query, success_cb, error_cb);
                    },
                    findOne: function (entity, query, success_cb, error_cb) {
                        var url = baseUrl + entity + '/find';

                        request(url, query, success_cb, error_cb);
                    },
                    pagination: function (entity, resultsPerPage, query, success_cb, error_cb) {
                        var url = baseUrl + entity + '/find',
                            page = 0, executing = false;

                        var pagination = this;

                        function success() {
                            success_cb.call(pagination, arguments);
                            executing = false;
                        }

                        function error() {
                            error_cb.call(pagination, arguments);
                            executing = false;
                        }

                        return {
                            reset: function () {
                                page = 0;
                            },
                            nextPage: function () {
                                if (!executing) {
                                    executing = true;

                                    page++;

                                    query['@page'] = page;
                                    query['@limit'] = resultsPerPage;

                                    request(url, query, success, error);
                                }
                            },
                            currentPage: function () {
                                if (this._page > 0 && !executing) {
                                    executing = true;

                                    query['@page'] = page;
                                    query['@limit'] = resultsPerPage;

                                    request(url, query, success, error);
                                }
                            },
                            previousPage: function () {
                                if (this._page > 0 && !executing) {
                                    executing = true;

                                    page--;

                                    query['@page'] = page;
                                    query['@limit'] = resultsPerPage;

                                    request(url, query, success, error);
                                }
                            }
                        };
                    }
                };
            }])


        .directive('findEntity', ['$timeout', 'FindService', function ($timeout, FindService) {
                var timeouts = {};

                return {
                    restrict: 'E',
                    templateUrl: MapasCulturais.templateUrl.findEntity,
                    scope: {
                        spinnerCondition: '=',
                        entity: '@',
                        noResultsText: '@',
                        filter: '=',
                        select: '=',
                        onRepeatDone: '=',
                        apiQuery: '='
                    },
                    link: function ($scope, el, attrs) {
                        $scope.attrs = attrs;

                        $scope.result = [];

                        $scope.searchText = '';

                        $scope.noEntityFound = false;

                        $scope.noMoreResults = false;

                        // pagination at the end of container scroll
                        var $el = jQuery(el[0]);
                        var $container = $el.find('.result-container');

                        $container.scroll(function () {
                            var containerInnerHeight = this.scrollHeight;
                            var containerScroll = jQuery(this).scrollTop();
                            var containerHeight = jQuery(this).height();
                            var bottomY = containerInnerHeight - containerHeight - containerScroll;
                            if (bottomY < containerHeight && !$scope.noMoreResults && !$scope.paginating) {
                                $scope.paginating = true;
                                $scope.find(10);
                            }
                        }).bind('mousewheel DOMMouseScroll', function (e) {
                            var e0 = e.originalEvent,
                                delta = e0.wheelDelta || -e0.detail;

                            this.scrollTop += (delta < 0 ? 1 : -1) * 30;
                            e.preventDefault();
                        });

                        $scope.avatarUrl = function (entity) {
                            if (entity['@files:avatar.avatarSmall'])
                                return entity['@files:avatar.avatarSmall'].url;
                            else
                                return MapasCulturais.defaultAvatarURL;
                        };

                        $scope.find = function (time) {
                            if (timeouts.find)
                                $timeout.cancel(timeouts.find);

                            time = time || 1000;

                            FindService.cancel();

                            var s = $scope.searchText.trim().replace(' ', '*');

                            if (parseInt(s) != s && s.length < 2) {
                                return;
                            }

                            var query = angular.isObject($scope.apiQuery) ? $scope.apiQuery : {};

                            if ($scope.lastS != s) {
                                $scope.lastS = s;
                                $scope.result = [];
                                $scope.noMoreResults = false;
                                $scope.pagination = FindService.pagination($scope.entity, 20, query, function (data, status) {
                                    $scope.processResult(data, status);
                                    $scope.spinnerCondition = false;
                                    $scope.paginating = false;
                                });
                            }


                            query.name = 'ILIKE(*' + s + '*)';
                            timeouts.find = $timeout(function () {
                                $scope.spinnerCondition = true;
                                $scope.pagination.nextPage();
                            }, time);
                        };

                        $scope.processResult = function (data, status) {
                            data = data[0];
                            if (angular.isFunction($scope.filter))
                                data = $scope.filter(data, status);

                            if (data.length > 0) {
                                $scope.result = $scope.result.concat(data);
                            } else if ($scope.result.length === 0) {
                                $scope.noEntityFound = true;

                                $timeout(function () {
                                    $scope.noEntityFound = false;
                                }, 3000);
                            } else {
                                // final dos resultados
                                $scope.noMoreResults = true;
                            }

                        };

                        jQuery(el).on('find', function () {
                            $scope.find();
                        });
                    }
                };
            }]);
})(angular);