'use strict';

var controllers = angular.module('controllers', []);

controllers.controller('MainController', ['$scope', '$location', '$window', '$http',
    function ($scope, $location, $window, $http) {
        $scope.user = {
            name: $window.sessionStorage.username,
            id: $window.sessionStorage.user_id
        }

        $scope.loggedIn = function() {
            return Boolean($window.sessionStorage.access_token);
        };

        $scope.logout = function () {
            $scope.user.name = "";
            delete $window.sessionStorage.username;
            delete $window.sessionStorage.user_id;
            delete $window.sessionStorage.access_token;
            $location.path('/login').replace();
        };

        $scope.goToTest = function () {
            if ($scope.loggedIn()) {
                $location.path('/test').replace();
            }else {
              $http.post('frontend/web/api/exist', '{"username":"' + $scope.user.name + '", "user_id":"' + $scope.user.id + '"}').success(
                  function (data) {
                    if (data.access_token) {
                      $window.sessionStorage.username = $scope.user.name;
                      $window.sessionStorage.user_id = data.user_id;
                      $window.sessionStorage.access_token = data.access_token;
                      $location.path('/test').replace();
                    } else {
                      $location.path('/login').replace();
                    }
                  })
            }
        };
    }
]);

controllers.controller('ContactController', ['$scope', '$http', '$window',
    function($scope, $http, $window) {
        $scope.captchaUrl = 'site/captcha';
        $scope.contact = function () {
            $scope.submitted = true;
            $scope.error = {};
            $http.post('frontend/web/api/contact', $scope.contactModel).success(
                function (data) {
                    $scope.contactModel = {};
                    $scope.flash = data.flash;
                    $window.scrollTo(0,0);
                    $scope.submitted = false;
                    $scope.captchaUrl = 'site/captcha' + '?' + new Date().getTime();
            }).error(
                function (data) {
                    angular.forEach(data, function (error) {
                        $scope.error[error.field] = error.message;
                    });
                }
            );
        };

        $scope.refreshCaptcha = function() {
            $http.get('site/captcha?refresh=1').success(function(data) {
                $scope.captchaUrl = data.url;
            });
        };
    }]);

controllers.controller('TestController', ['$scope', '$http', '$window', '$location',
    function ($scope, $http, $window, $location) {
        $scope.testId = "";
        $scope.dict = {
            word: "",
            word_id: "",
            translates: [],
            language: ""
        }
        $scope.choosen = {};
        $scope.counter = 0;
        $scope.words_count = 0;
        $scope.test_errors = 0;
        $scope.show_error = false;
        $scope.show_max_error = false;
        $scope.complete_test = false;

        $scope.init = function() {
            $scope.testStart();
        }
        $scope.testStart = function(){
            if ($scope.loggedIn()) {
                $http.post('frontend/web/api/start', '{"user_id":"' + $window.sessionStorage.user_id + '"}').success(function (data) {
                    $scope.testId = data.testId;
                    $scope.words_count = data.words_count;
                    $scope.testNext();
                })
            }
        }

        $scope.testNext = function() {
            $http.post('frontend/web/api/next', '{"testId":"' + $scope.testId + '"}').success(function (data) {
              if (data.translates.length){
                $scope.dict = data;
                $scope.counter++;
              }else{
                $scope.complete_test = true;
              }
            })
        }
        $scope.checkAnswer = function() {
            $scope.choosen = {
                username: $window.sessionStorage.username,
                test_id: $scope.testId,
                word_id: $scope.dict.word_id,
                answer_id: $scope.dict.choose,
                language: $scope.dict.language,
            }
            $http.post('frontend/web/api/check', $scope.choosen).success(
                function (data) {
                    if(data.maxErrors) {
                        $scope.show_max_error = true;
                        $scope.test_errors++;
                    }else if(!data.error) {
                        $scope.show_error = false;
                        $scope.choosen.is_error = false;
                        $scope.testNext();
                    }else{
                        $scope.test_errors++;
                        $scope.show_error = true;
                    }
            })
        }

    }
]);

controllers.controller('LoginController', ['$scope', '$http', '$window', '$location',
    function($scope, $http, $window, $location) {
        $scope.login = function () {
            $scope.submitted = true;
            $scope.error = {};
            $http.post('frontend/web/api/login', $scope.userModel).success(
                function (data) {
                    $scope.user.name = $scope.userModel.username;
                    $window.sessionStorage.username = $scope.userModel.username;
                    $window.sessionStorage.user_id = data.user_id;
                    $window.sessionStorage.access_token = data.access_token;
                    $location.path('/test').replace();
            }).error(
                function (data) {
                    angular.forEach(data, function (error) {
                        $scope.error[error.field] = error.message;
                    });
                }
            );
        };
    }
]);

controllers.controller('SignupController', ['$scope', '$http', '$window', '$location',
  function($scope, $http, $window, $location) {
    $scope.signup = function () {
      $scope.submitted = true;
      $scope.error = {};
      $http.post('frontend/web/api/signup', $scope.userModel).success(
          function (data) {
            $window.sessionStorage.username = userModel.username;
            $window.sessionStorage.user_id = data.user_id;
            $window.sessionStorage.access_token = data.access_token;
            $location.path('/test').replace();
          }).error(
          function (data) {
            angular.forEach(data, function (error) {
              $scope.error[error.field] = error.message;
            });
          }
      );
    };
  }
]);