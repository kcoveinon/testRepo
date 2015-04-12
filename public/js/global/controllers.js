(function() {
	var globalControllerModule = angular.module(
		'globalControllerModule', 
		[
			'customerFactoryModule',
			'customerServiceModule'
		]
	);

	globalControllerModule.controller('GlobalMenuController', ['$rootScope', '$scope', 'customerFactory', 'Customer', function($rootScope, $scope, customerFactory, Customer) {
		customerFactory.getCurrentCustomer().then(function(customer) {
			if (customer instanceof Customer) {
				$scope.customer = customer;
			}
		});

		$scope.menu = [
			{
				"active"             : true,
				"icon"               : "home",
				"label"              : "Profile",
				"sectionViewDetails" : { 
					"rightContent" : { 
						"url" : $rootScope.baseUrl + "/view/get/customer.profile" 
					}
				}
			},
			{
				"active"             : false,
				"icon"               : "mail",
				"label"              : "My Bookings",
				"sectionViewDetails" : { 
					"rightContent" : { 
						"url" : $rootScope.baseUrl + "/view/get/booking.all"
					}
				}
			},
			{
				"active"             : false,
				"icon"               : "comment outline",
				"label"              : "My Reviews",
				"sectionViewDetails" : { 
					"rightContent" : { 
						"html" : "reviews" 
					}
				}
			}
		];
	}]);
})();