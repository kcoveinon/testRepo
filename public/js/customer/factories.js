(function(){
	var customerFactoryModule = angular.module(
		'customerFactoryModule', 
		[
			'globalFactoryModule'
		]
	);

	customerFactoryModule.factory('Customer', ['customerService', 'bookingService', 'Booking', 'Model', function(customerService, bookingService, Booking, Model) {
		function Customer() {
			this.id         = 0;
			this.email      = "";
			this.avatar     = "";
			this.gender     = "";
			this.newsletter = 0;
			this.phoneNo    = "";
			this.birthDate  = "";
			this.timeZone   = "";
			this.country    = {
				"id"   : 0,
				"code" : "",
				"name" : ""
			};
			this.name       = {
				"title" : "",
				"first" : "",
				"last"  : ""
			};
			this.address    = {
				"street"     : "",
				"suburb"     : "",
				"city"       : "",
				"postalCode" : 0
			};
			this.bookings   = {
				"all"     : [],
				"future"  : [],
				"past"    : [],
				"popular" : [],
				"current" : []
			};
		}

		Customer.prototype = Object.create(Model.prototype);

		Customer.prototype.createAddress = function() {
			return this.address.street + ' ' + this.address.suburb + ' ' + this.address.city + ' ' + this.address.postalCode;
		};

		Customer.prototype.createFullName = function() {
			return this.name.first + ' ' + this.name.last;
		};

		Customer.prototype.fetchAllBookings = function() {
			var parameters = { "email" : this.email };
			var self       = this;

			return bookingService.fetchAll(parameters).then(function(response) {
				if (response.status == 'OK') {
					self.bookings.all = [];

					for (var i in response.data.bookings) {
						var booking = new Booking();

						booking.setAttributes(response.data.bookings[i]);

						self.bookings.all.push(booking);
					}

					return response;
				}
			});
		};

		Customer.prototype.fetchFutureBookings = function() {
			var parameters = { "email" : this.email };
			var self       = this;

			return bookingService.fetchFuture(parameters).then(function(response) {
				if (response.status == 'OK') {
					self.bookings.future = [];

					for (var i in response.data.bookings) {
						var booking = new Booking();

						booking.setAttributes(response.data.bookings[i]);

						self.bookings.future.push(booking);
					}

					return response;
				}
			});
		};

		Customer.prototype.fetchPastBookings = function() {
			var parameters = { "email" : this.email };
			var self       = this;

			return bookingService.fetchPast(parameters).then(function(response) {
				if (response.status == 'OK') {
					self.bookings.past = [];

					for (var i in response.data.bookings) {
						var booking = new Booking();

						booking.setAttributes(response.data.bookings[i]);

						self.bookings.past.push(booking);
					}

					return response;
				}
			});
		};

		Customer.prototype.fetchPopularBookings = function() {
			var parameters = { "email" : this.email };
			var self       = this;

			return bookingService.fetchPopular(parameters).then(function(response) {
				if (response.status == 'OK') {
					self.bookings.popular = [];

					for (var i in response.data.bookings) {
						var booking = new Booking();

						booking.setAttributes(response.data.bookings[i]);

						self.bookings.popular.push(booking);
					}

					return response;
				}
			});
		};

		Customer.prototype.fetchCurrentBookings = function() {
			var parameters = { "email" : this.email };
			var self       = this;

			return bookingService.fetchCurrent(parameters).then(function(response) {
				if (response.status == 'OK') {
					self.bookings.current = [];

					for (var i in response.data.bookings) {
						var booking = new Booking();

						booking.setAttributes(response.data.bookings[i]);

						self.bookings.current.push(booking);
					}

					return response;
				}
			});
		}

		Customer.createByEmail = function(email) {
			var parameters = { "email" : email };

			return customerService.fetchByEmail(parameters).then(function(response) {
				if (response.status == 'OK') {
					var customer = new Customer();

					customer.setAttributes(response.data.customer);

					return customer;
				}
			});
		};

		return Customer;
	}]);

	customerFactoryModule.factory('customerFactory', ['customerService', 'Customer', function(customerService, Customer) {
		var currentCustomer = new Customer();

		var getCurrentCustomer = function() {
			return customerService.fetchCurrent().then(function(response) {
				if (response.status == 'OK') {
					currentCustomer.setAttributes(response.data.customer);

					return currentCustomer;
				}
			});
		};

		return {
			getCurrentCustomer : getCurrentCustomer
		};
	}]);
})();