'use strict';

var app = angular.module('app', [
    'ngRoute',          //$routeProvider
    'mgcrea.ngStrap',   //bs-navbar, data-match-route directives
    'controllers',      //Our module frontend/web/js/controllers.js
    'mgcrea.ngStrap.alert',
    'mgcrea.ngStrap.modal'       
]);


app.config(['$routeProvider', '$locationProvider', '$httpProvider',
    function($routeProvider, $locationProvider, $httpProvider) {
        $routeProvider.
            when('/', {
                templateUrl: 'partials/index.html'
            }).
            when('/login', {
                templateUrl: 'partials/login.html',
                controller: 'LoginController'
            }).
            when('/signup', {
                templateUrl: 'partials/signup.html',
                controller: 'SignupController'
            }).
            when('/test', {
                templateUrl: 'partials/test.html',
                controller: 'TestController'
            }).
            otherwise({
                templateUrl: 'partials/404.html'
            });
        $httpProvider.interceptors.push('authInterceptor');

        // use the HTML5 History API
        $locationProvider.html5Mode(true);
    }
]);

app
  .config(function($alertProvider) {
    angular.extend($alertProvider.defaults, {
      animation: 'am-fade-and-slide-top',
      placement: 'top'
    });
  })

app.factory('authInterceptor', function ($q, $window, $location) {
    return {
        request: function (config) {
            if ($window.sessionStorage.access_token) {
                //HttpBearerAuth
                config.headers.Authorization = 'Bearer ' + $window.sessionStorage.access_token;
            }
            return config;
        },
        responseError: function (rejection) {
            if (rejection.status === 401) {
                $location.path('/login').replace();
            }
            return $q.reject(rejection);
        }
    };
});