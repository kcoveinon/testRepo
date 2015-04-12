(function() {
	var customerControllerModule = angular.module(
		'customerControllerModule', 
		[
			'bookingServiceModule',
			'countryServiceModule',
			'customerFactoryModule',
			'customerServiceModule',
			'globalConstantModule'
		]
	);

	customerControllerModule.controller('CustomerProfileController', ['$rootScope', '$scope', 'customerFactory', 'Customer', function($rootScope, $scope, customerFactory, Customer) {
		customerFactory.getCurrentCustomer().then(function(customer) {
			if (customer instanceof Customer) {
				$scope.customer = customer;

				$scope.customer.fetchAllBookings();
				$scope.customer.fetchFutureBookings();
				$scope.customer.fetchPastBookings();
				$scope.customer.fetchPopularBookings();
			}
		});

		$scope.updateProfileMenuItem = {
			"rightContent" : { 
				"url" : $rootScope.baseUrl + "/view/get/customer.update_profile_form" 
			}
		};
	}]);

	customerControllerModule.controller('CustomerUpdateController', ['$scope', 'countryService', 'customerFactory', 'globalNameTitlesConstant', 'globalTimeZonesConstant', 'Form', 'Customer', function($scope, countryService, customerFactory, globalNameTitlesConstant, globalTimeZonesConstant, Form, Customer) {
		$scope.dropDowns = {
			"nameTitles" : globalNameTitlesConstant,
			"timeZones"  : globalTimeZonesConstant,
			"countries"  : []
		};

		var fields = ["id", "title", "firstName", "lastName", "email", "phone", "street", "suburb", "city", "postCode", "birthDate", "newsletter", "country", "timezone"];

		$scope.customerForm = new Form(fields);

		customerFactory.getCurrentCustomer().then(function(customer) {
			if (customer instanceof Customer) {
				$scope.customer = customer;

				$scope.customerForm.fields.title.value      = $scope.customer.name.title;
				$scope.customerForm.fields.firstName.value  = $scope.customer.name.first;
				$scope.customerForm.fields.lastName.value   = $scope.customer.name.last;
				$scope.customerForm.fields.email.value      = $scope.customer.email;
				$scope.customerForm.fields.phone.value      = $scope.customer.phoneNo;
				$scope.customerForm.fields.street.value     = $scope.customer.address.street;
				$scope.customerForm.fields.suburb.value     = $scope.customer.address.suburb;
				$scope.customerForm.fields.city.value       = $scope.customer.address.city;
				$scope.customerForm.fields.postCode.value   = $scope.customer.address.postalCode;
				$scope.customerForm.fields.birthDate.value  = $scope.customer.birthDate;
				$scope.customerForm.fields.newsletter.value = $scope.customer.newsletter;
				$scope.customerForm.fields.country.value    = $scope.customer.country.code;
				$scope.customerForm.fields.timezone.value   = $scope.customer.timeZone;

				// IMPORTANT! create country model
				countryService.fetchAll().then(function(response) {
					if (response.status == 'OK') {
						for (var i in response.data.countries) {
							$scope.dropDowns.countries.push(response.data.countries[i]);
						}
					}
				});
			}
		});
	}]);

	customerControllerModule.controller('CustomerBookingsController', ['$scope', 'customerFactory', 'Customer', function($scope, customerFactory, Customer) {
		customerFactory.getCurrentCustomer().then(function(customer) {
			if (customer instanceof Customer) {
				$scope.customer = customer;

				$scope.customer.fetchCurrentBookings();
				$scope.customer.fetchFutureBookings();
				$scope.customer.fetchPastBookings();
				$scope.customer.fetchPopularBookings();
			}
		});
	}]);

	customerControllerModule.controller('CustomerRegistrationController', ['$scope', 'countryService', 'globalNameTitlesConstant', 'globalTimeZonesConstant', 'Form', function($scope, countryService, globalNameTitlesConstant, globalTimeZonesConstant, Form) {
		$scope.dropDowns = {
			"nameTitles" : globalNameTitlesConstant,
			"timeZones"  : globalTimeZonesConstant,
			"countries"  : []
		};

		var fields = ["alias", "id", "title", "firstName", "lastName", "email", "phone", "street", "suburb", "city", "postCode", "birthDate", "newsletter", "country", "timezone"];
		
		$scope.customerForm = new Form(fields);

		countryService.fetchAll().then(function(response) {
			if (response.status == 'OK') {
				for (var i in response.data.countries) {
					$scope.dropDowns.countries.push(response.data.countries[i]);
				}
			}
		});
	}]);

	customerControllerModule.controller('CustomerValidateAccountController', ['$scope', 'Form', function($scope, Form) {
		var fields = ["email", "alias", "password", "passwordConfirmation"];

		$scope.validationForm = new Form(fields);
	}]);
})();