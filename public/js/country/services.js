(function() {
	var countryServiceModule = angular.module('countryServiceModule', []);

	countryServiceModule.service('countryService', ['$rootScope', '$http', '$q', function($rootScope, $http, $q) {
		this.fetchAll = function() {
			var dfd = $q.defer();

			$http({
				"url"    : $rootScope.baseUrl + '/country/fetch-all',
				"method" : "POST"
			}).success(function(response) {
				dfd.resolve(response);
			}).error(function(response) {
				// log error
			});

			return dfd.promise;
		};
	}]);
})();