(function() {
	var vroomCustomerApp = angular.module(
		'VroomCustomerApp', 
		[
			'bookingServiceModule',
			'bookingFactoryModule',
			'customerControllerModule',
			'customerDirectiveModule',
			'customerFactoryModule',
			'customerServiceModule',
			'globalConstantModule',
			'globalControllerModule',
			'globalDirectiveModule',
			'globalServiceModule',
			'globalFactoryModule',
			'globalProviderModule',
			'jqueryUiDirectiveModule',
			'semanticUiDirectiveModule'
		]
	);

	vroomCustomerApp.config(['$interpolateProvider', '$httpProvider', 'globalDataProvider', function($interpolateProvider, $httpProvider, globalDataProvider) {
		var symbols = globalDataProvider.getSymbols();

		$interpolateProvider.startSymbol(symbols.start).endSymbol(symbols.end);
		$httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
	}]);

	// sample to see if angular is working, remove this later on
	vroomCustomerApp.controller('FirstController', ['$scope', 'globalSectionViewService', function($scope, globalSectionViewService) {
		$scope.sectionViews = globalSectionViewService.getSectionViews();
	}]);
})();