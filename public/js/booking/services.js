(function() {
	var bookingServiceModule = angular.module('bookingServiceModule', []);

	bookingServiceModule.service('bookingService', ['$rootScope', '$http', '$q', function($rootScope, $http, $q) {
		this.fetchAll = function(parameters) {
			var dfd = $q.defer();

			$http({
				"method" : "POST",
				"url"    : $rootScope.baseUrl + "/booking/fetch-all",
				"data"   : parameters
			}).success(function(response) {
				dfd.resolve(response);
			}).error(function(response) {
				// log error
			});

			return dfd.promise;
		};

		this.fetchFuture = function(parameters) {
			var dfd = $q.defer();

			$http({
				"method" : "POST",
				"url"    : $rootScope.baseUrl + "/booking/fetch-future",
				"data"   : parameters
			}).success(function(response) {
				dfd.resolve(response);
			}).error(function(response) {
				// log error
			});

			return dfd.promise;
		};

		this.fetchPast = function(parameters) {
			var dfd = $q.defer();

			$http({
				"method" : "POST",
				"url"    : $rootScope.baseUrl + "/booking/fetch-past",
				"data"   : parameters
			}).success(function(response) {
				dfd.resolve(response);
			}).error(function(response) {
				// log error
			});

			return dfd.promise;
		};

		this.fetchPopular = function(parameters) {
			var dfd = $q.defer();

			$http({
				"method" : "POST",
				"url"    : $rootScope.baseUrl + "/booking/fetch-popular",
				"data"   : parameters
			}).success(function(response) {
				dfd.resolve(response);
			}).error(function(response) {
				// log error
			});

			return dfd.promise;
		};

		this.fetchCurrent = function(parameters) {
			var dfd = $q.defer();

			$http({
				"method" : "POST",
				"url"    : $rootScope.baseUrl + "/booking/fetch-current",
				"data"   : parameters
			}).success(function(response) {
				dfd.resolve(response);
			}).error(function(response) {
				// log error
			});

			return dfd.promise;
		};
	}]);
})();