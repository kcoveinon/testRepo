(function() {
	var globalServiceModule = angular.module('globalServiceModule', []);

	globalServiceModule.service('globalSectionViewService', [function() {
		var _sectionViews = {
			// "rightContent" : {
			// 	"url" : ""
			// }
		};

		this.getSectionViews = function() {
			return _sectionViews;
		};

		this.setSectionViews = function(sectionViews) {
			for(var attr in sectionViews){
				if(sectionViews.hasOwnProperty(attr)){
					_sectionViews[attr] = sectionViews[attr];
				}
			}
		};
	}]);

	globalServiceModule.service('globalViewService', ['$http', '$q', function($http, $q) {
		this.fetchView = function(view) {
			var dfd = $q.defer();

			$http({
				"method" : "GET",
				"url"    : view.url
			}).success(function(response) {
				dfd.resolve(response);
			}).error(function(response) {
				// log error
			});

			return dfd.promise;
		};
	}]);
})();