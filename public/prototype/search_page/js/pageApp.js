var pageApp = angular.module('pageApp', [])

pageApp.config(['$interpolateProvider', function($interpolateProvider) {
	$interpolateProvider.startSymbol('<%').endSymbol('%>');
}]);
