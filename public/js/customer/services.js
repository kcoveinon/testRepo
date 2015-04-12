(function() {
	var customerServiceModule = angular.module('customerServiceModule', []);

	customerServiceModule.service('customerService', ['$rootScope', '$http', '$q', function($rootScope, $http, $q) {
		this.fetchCurrent = function() {
			var dfd = $q.defer();

			$http({
				"method" : "POST",
				"url"    : $rootScope.baseUrl + "/customer/fetch-current"
			}).success(function(response) {
				dfd.resolve(response);
			}).error(function(response) {
				// log error
			});

			return dfd.promise;
		};

		this.fetchById = function(parameters) {
			var dfd = $q.defer();

			$http({
				"method" : "POST",
				"url"    : $rootScope.baseUrl + "/customer/fetch-by-id",
				"data"   : parameters
			}).success(function(response) {
				dfd.resolve(response);
			}).error(function(response) {
				// log error
			});

			return dfd.promise;
		};

		this.fetchByEmail = function(parameters) {
			var dfd = $q.defer();

			$http({
				"method" : "POST",
				"url"    : $rootScope.baseUrl + "/customer/fetch-by-email",
				"data"   : parameters
			}).success(function(response) {
				dfd.resolve(response);
			}).error(function(response) {
				// log error
			});

			return dfd.promise;
		};

		this.updateAccount = function(parameters) {
			var dfd = $q.defer();

			$http({
				"method" : "POST",
				"url"    : $rootScope.baseUrl + "/customer/update-account",
				"data"   : parameters
			}).success(function(response) {
				dfd.resolve(response);
			}).error(function(response) {
				// log error
			});

			return dfd.promise;
		};

		this.createAccount = function(parameters) {
			var dfd = $q.defer();

			$http({
				"method" : "POST",
				"url"    : $rootScope.baseUrl + "/customer/create-account",
				"data"   : parameters
			}).success(function(response) {
				dfd.resolve(response);
			}).error(function(response) {
				// log error
			});

			return dfd.promise;
		};

		this.validateAccount = function(parameters) {
			var dfd = $q.defer();

			$http({
				"method" : "POST",
				"url"    : $rootScope.baseUrl + "/customer/validate-account",
				"data"   : parameters
			}).success(function(response) {
				dfd.resolve(response);
			}).error(function(response) {
				// log error
			});

			return dfd.promise;
		};

		this.updateProfileImage = function(parameters) {
			var dfd = $q.defer();

			$http({
				"method"   : "POST",
				"url"      : $rootScope.baseUrl + "/customer/update-profile-image",
				"data"     : parameters,
				"dataType" : "JSON"
			}).success(function(data) {
				dfd.resolve(data);
			}).error(function(data) {
				// log error
			});

			return dfd.promise;
		};
	}]);
})();