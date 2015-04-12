(function() {
	var bookingFactoryModule = angular.module(
		'bookingFactoryModule', 
		[
			'globalFactoryModule'
		]
	);

	bookingFactoryModule.factory('Booking', ['Model', function(Model) {
		function Booking() {
			this.id       = 0;
			this.supplier = {
				"id"           : 0,
				"confirmation" : ""
			};
			this.vehicle  = {
				"categoryId" : 0,
				"classId"    : 0,
				"code"       : "",
				"distance"   : "",
				"name"       : ""
			};
			this.depot    = {
				"pickUp" : {
					"id" : 0,
					"code"       : "",
					"name"       : "",
					"address"    : "",
					"city"       : "",
					"phoneNo"    : "",
					"postalCode" : "",
					"supplierId" : 0,
					"countryId"  : 0,
					"isAirport"  : 0,
					"extraInfo"  : 0,
					"popularity" : 0,
					"latitude"   : 0,
					"longitude"  : 0,
					"accuracy"   : "",
					"comment"    : null
				},
				"return" : {
					"id" : 0,
					"code"       : "",
					"name"       : "",
					"address"    : "",
					"city"       : "",
					"phoneNo"    : "",
					"postalCode" : "",
					"supplierId" : 0,
					"countryId"  : 0,
					"isAirport"  : 0,
					"extraInfo"  : 0,
					"popularity" : 0,
					"latitude"   : 0,
					"longitude"  : 0,
					"accuracy"   : "",
					"comment"    : null
				}
			};
			this.date     = {
				"booked" : "",
				"pickUp" : "",
				"return" : ""
			};
		}

		Booking.prototype = Object.create(Model.prototype);

		return Booking;
	}]);
})();