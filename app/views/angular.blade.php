<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html ng-app="TestApp">
    <head>
        <meta charset="utf-8" />
        <link type="text/css" href="{{{ asset('css/src/bootstrap.min.css')  }}}" rel="stylesheet"  media="screen"/>
        <meta name="_token" content="{{ csrf_token() }}" />
    </head>

    <body>
        <div ng-controller="TodoCtrl">
            <h2>Total ToDo's: <% getTotalTodos() %></h2>
            <ul class='list-unstyled'>
                <li ng-repeat="todo in todos">
                    <input type="checkbox" ng-model="todo.done"/>
                    <span><% todo.text %></span>
                    <span ng-click="remove($index)"> X </span>
                </li>
            </ul>

            <form>
                <input type="text" ng-model="formTodoText" ng-model-instant/>
                <button class="btn" ng-click="addTodo()"><i class="icon-plus">Add</i></button>
            </form>

        </div>
    </body>

    {{ HTML::script('js/src/angular-1.2.13.js') }}
    <script type="text/javascript">
        (function () {
            var TestApp = angular.module('TestApp', []);

            TestApp.config(['$interpolateProvider, $httpProvider', function($interpolateProvider){
                $interpolateProvider.startSymbol('<%').endSymbol('%>');
            }]);

            TestApp.controller('TodoCtrl', ['$scope', function($scope){

                $scope.todos = [
                {
                    text : "Learn AngularJS",
                    done : false
                },
                {
                    text : "Build an App",
                    done : false
                },
                ];

                $scope.getTotalTodos = function() {
                    return $scope.todos.length
                };

                $scope.addTodo = function() {
                    $scope.todos.push({text:$scope.formTodoText, done:false})
                    $scope.formTodoText = '';
                }

                $scope.remove = function(index) {
                    $scope.todos.splice(index, 1);
                }
            }]);

        })();

    </script>
</html>
