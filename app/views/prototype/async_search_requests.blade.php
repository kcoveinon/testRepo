<!DOCTYPE html>
<html lang="en" ng-app="PrototypeAsyncSearchRequestApp">
	<head>
		<meta charset="UTF-8">
		<title>Prototype - Asynchronous Search Requests</title>

		<link rel="stylesheet" href="{{ url() }}/css/vendor/semantic_ui/semantic.css">
		<link rel="stylesheet" href="{{ url() }}/css/vendor/font_awesome/font-awesome.min.css">

		<style>
			body {
				font-family: Arial, Helvetica, sans-serif;
				font-size: 12px;
			}

			.ui.search-page {
				width: 800px;
				margin: auto;
			}

			.process-report {
				background: #efe;
				border: 1px solid #dcddde; margin-bottom: 10px;
				font-family: monospace;
				padding: 10px 0;
			}

			.process-report > .grid > .column { margin: 0; }

			.process-report .process-report-header:first-child { margin: 0 !important; }

			.process-report .process-report-header { font-weight: bold; margin-top: 15px !important; }

			.search-results > .vehicle	{ border: 1px solid #dcddde; margin-bottom: 10px; }

			.search-results > .vehicle > .column:first-child { border: none; }

			.search-results > .vehicle > .column {
				border-left: 1px solid #dcddde;
				display: table-cell;
				margin: 0px;
				padding: 5px;
			}

			.search-results > .vehicle .vehicle-image { width: 100%; }

			.search-results > .vehicle .vehicle-supplied-by { text-align: center; }

			.search-results > .vehicle .vehicle-supplied-by span { color: #888; font-size: 11px; font-weight: bold; }

			.search-results > .vehicle .vehicle-supplied-by span,
			.search-results > .vehicle .vehicle-supplied-by img { vertical-align: middle; }

			.search-results > .vehicle .vehicle-name { color: #0d4b77; font-weight: bold; text-transform: uppercase; }

			.search-results > .vehicle .vehicle-features { color: #333; margin-top: 67px; }

			.search-results > .vehicle .vehicle-features .label { font-size: 11px; font-weight: bold; }

			.search-results > .vehicle .vehicle-feature { margin: 0 5px; position: relative; font-weight: bold; }

			.search-results > .vehicle .vehicle-feature-value { 
				font-size: 10px;
				font-weight: normal;
				position: absolute;
				right: 0;
			}

			.search-results > .vehicle .vehicle-feature-aircon { color: #3498db; }

			.search-results > .vehicle .vehicle-feature-seats > .vehicle-feature-value { left: 12px; }

			.search-results > .vehicle .vehicle-feature-doors > .vehicle-feature-value { left: 15px; }

			.search-results > .vehicle .vehicle-feature-baggage > .vehicle-feature-value { left: 18px; }

			.search-results > .vehicle .vehicle-booking-total-price { color: #0d4b77; font-weight: bold; font-size: 21px; }

			.search-results > .vehicle .vehicle-book-price-per-day { color: rgba(0, 0, 0, 0.5); font-weight: bold; font-size: 12px; }

			.search-results > .vehicle .book-button,
			.search-results > .vehicle .book-button:hover { background-color: #27ae60; margin-top: 65px; }

			.search-results > .vehicle .vehicle-category .column,
			.search-results > .vehicle .vehicle-class .column { 
				color: #777;
				font-weight: bold;
				font-size: 11px;
				margin: 0;
			}
		</style>
	</head>

	<body>
		<div 
			ng-controller="SearchVehicleController" 
			ng-init="
				searchParams.pickUp.date = '{{ $pickUpDate }}';
				searchParams.pickUp.time = '{{ $pickUpTime }}';
				searchParams.return.date = '{{ $returnDate }}';
				searchParams.return.time = '{{ $returnTime }}';
				searchParams.pickUp.locationId = '{{ $pickUpLocationId }}';
				searchParams.return.locationId = '{{ $returnLocationId }}';
				searchParams.countryCode = '{{ $countryCode }}';
				searchParams.driverAge = '{{ $driverAge }}';
				samplePickUpLocationId = '{{ $pickUpLocationId }}';
			"
		>
			<div class="ui one column grid search-page">
				<div class="column process-report">
					<div class="sixteen wide column process-report-header">
						SUPPLIERS SEARCH DEPOT PAIR
					</div>
					<div class="sixteen wide column">
						<pre><% suppliersPopularDepotPair | json %></pre>
					</div>
					<div class="sixteen wide column process-report-header">
						SUPPLIERS SEARCH PARAMS
					</div>
					<div class="sixteen wide column">
						<pre><% suppliersSearchParams | json %></pre>
					</div>
				</div>

				<div class="column process-report">
					<div class="ui two column grid">
						<div class="sixteen wide column process-report-header">
							GENERAL
						</div>

						<div class="four wide column">Total requests made</div>
						<div class="twelve wide column">: <% searchRequestStatus.total %></div>

						<div class="four wide column">Pending requests</div>
						<div class="twelve wide column">: <% (searchRequestStatus.total - searchRequestStatus.finished) %></div>

						<div class="four wide column">Finished requests</div>
						<div class="twelve wide column">: <% searchRequestStatus.finished %></div>

						<div class="four wide column">Total time</div>
						<div class="twelve wide column">: 
							<span ng-if="!searchRequestStatus.timeTotal">---</span>
							<span ng-if="searchRequestStatus.timeTotal"><% searchRequestStatus.timeTotal %> seconds</span>
						</div>

						<div class="four wide column">Status</div>
						<div class="twelve wide column">: 
							<span ng-if="searchRequestStatus.total != searchRequestStatus.finished">
								waiting for <% (searchRequestStatus.total - searchRequestStatus.finished) %> more supplier<span ng-if="(searchRequestStatus.total - searchRequestStatus.finished) > 1">s</span>.
							</span>
							<span ng-if="searchRequestStatus.total == searchRequestStatus.finished">
								completed all requests.
							</span>
						</div>

						<div class="sixteen wide column process-report-header">
							SUPPLIERS
						</div>

						<div ng-repeat-start="supplierRequestStatus in suppliersRequestStatus" class="four wide column"><% supplierRequestStatus.name %></div>
						<div ng-repeat-end class="twelve wide column">: 
							<span ng-if="!supplierRequestStatus.executionTime">fetching vehicles.</span>
							<span ng-if="supplierRequestStatus.executionTime"><% supplierRequestStatus.executionTime %> seconds</span>
						</div>
					</div>
				</div>

				<div class="column search-results">
					<div class="ui three column grid vehicle" ng-repeat="vehicle in vehicles">
						<div class="four wide column">
							<img class="vehicle-image" ng-src="http://vroomvroomvroom.com.au/book/images/vehicles/AU_<% vehicle.supplierCode %>_<% vehicle.categoryCode %>.jpg" src="http://vroomvroomvroom.com.au/book/images/vehicles/AU_<% vehicle.supplierCode %>_<% vehicle.categoryCode %>.jpg">

							<div class="vehicle-supplied-by">
								<span>SUPPLIED BY</span>
								<img data-ng-src="http://vroomvroomvroom.eu/img/suppliers/icon-<% vehicle.supplierCode %>.gif" class="supplier logo" src="http://vroomvroomvroom.eu/img/suppliers/icon-<% vehicle.supplierCode %>.gif">
							</div>
						</div>

						<div class="eight wide column vehicle-details">
							<div class="vehicle-name"><% vehicle.name %></div>

							<div class="ui two column grid vehicle-category">
								<div class="three wide column">CATEGORY</div>
								<div class="column"><% vehicle.expandedCode.type %></div>
							</div>

							<div class="ui two column grid vehicle-class">
								<div class="three wide column">CLASS</div>
								<div class="column"><% vehicle.expandedCode.category %></div>
							</div>

							<div class="vehicle-features">
								<div class="label">FEATURES</div>
								<span ng-if='vehicle.hasAirCondition' class="vehicle-feature vehicle-feature-aircon">
									<i class="fa fa-asterisk vehicle-feature-icon"></i>
								</span>
								<span class="vehicle-feature">
									<% vehicle.transmission.code %>
								</span>
								<span class="vehicle-feature">
									<i class="fa fa-road vehicle-feature-icon"></i>
								</span>
								<span class="vehicle-feature vehicle-feature-seats">
									<i class="fa fa-user vehicle-feature-icon"></i>
									<span class="vehicle-feature-value"><% vehicle.seats %></span>
								</span>
								<span class="vehicle-feature vehicle-feature-doors">
									<img class="vehicle-feature-icon" src="http://vroomvroomvroom.eu/img/icons/icon-door.png" style="width:14px;height:14px;">
									<span class="vehicle-feature-value"><% vehicle.doorCount %></span>
								</span>
								<span class="vehicle-feature vehicle-feature-baggage">
									<i class="fa fa-suitcase vehicle-feature-icon"></i>
									<span class="vehicle-feature-value"><% vehicle.baggageQty %></span>
								</span>
							</div>
						</div>

						<div class="four wide column vehicle-book">
							<div class="vehicle-booking-total-price">
								<% vehicle.currency %> <% vehicle.totalRateEstimate %>
							</div>

							<div class="vehicle-book-price-per-day">
								<% vehicle.currency %> <% (vehicle.totalRateEstimate / 2) | priceFormatter %> /day
							</div>

							<div class="ui fluid small book primary button book-button">
								Book Now
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<script type="text/javascript" src="{{ url() }}/js/vendor/angularjs/angular.min.js"></script>

		<script>
			(function() {
				// services
				var commsLayerServicesModule = angular.module('CommsLayerServicesModule', []);

				commsLayerServicesModule.service('CommsLayerSearchService', ['$http', '$q', function($http, $q) {
					this.searchSupplierVehicles = function(params) {
						var dfd = $q.defer();
						var apiUrl = '{{ url() }}/' + 
							params.supplier + '/search/' +
							params.pickUp.date + '/' + params.pickUp.time + '/' +
							params.return.date + '/' + params.return.time + '/' +
							params.pickUp.station + '/' + params.return.station + '/' +
							params.country + '/' + params.age;

						$http({
							"method" : "POST",
							"url"    : apiUrl
						}).success(function(response) {
							dfd.resolve(response);
						}).error(function(response) {
							// log error
						});

						return dfd.promise;
					};

					this.getLocationSuppliersPopularDepotPair = function(params) {
						var dfd = $q.defer();
						var apiUrl = '{{ URL() }}/location/get-suppliers-popular-depot-pair/' + 
							params.pickUpLocationId + '/' + params.returnLocationId;

						$http({
							"method" : "POST",
							"url"    : apiUrl
						}).success(function(response) {
							dfd.resolve(response);
						}).error(function(response) {
							// log error
						});

						return dfd.promise;
					}
				}]);

				var numberFiltersModule = angular.module('numberFiltersModule', []);

				numberFiltersModule.filter('priceFormatter', function() {
					return function(price) {
						return price.toFixed(2);
					};
				});

				// app
				var prototypeAsyncSearchRequestApp = angular.module('PrototypeAsyncSearchRequestApp', ['CommsLayerServicesModule', 'numberFiltersModule']);

				prototypeAsyncSearchRequestApp.config(['$interpolateProvider', '$httpProvider', function($interpolateProvider, $httpProvider) {
					$interpolateProvider.startSymbol('<%').endSymbol('%>');
					$httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
				}]);

				prototypeAsyncSearchRequestApp.controller('SearchVehicleController', ['$scope', 'CommsLayerSearchService', function($scope, commsLayerSearchService) {
					$scope.vehicles = [];

					// noted need for actual search page
					$scope.searchRequestStatus = {
						"total"     : 0,
						"finished"  : 0,
						"timeStart" : 0,
						"timeEnd"   : 0,
						"timeTotal" : 0
					};

					// not needed for actual search page
					$scope.suppliersRequestStatus = {
						"EC" : {
							"name" : "Europcar",
							"executionTime" : 0
						},
						"HZ" : {
							"name" : "Hertz",
							"executionTime" : 0
						},
						"TH" : {
							"name" : "Thrifty",
							"executionTime" : 0	
						},
						"RS" : {
							"name" : "RedSpot",
							"executionTime" : 0
						}
					}
					// not needed for actual search page
					var date = new Date();

					$scope.$watch(
						function() {
							return $scope.searchParams;
						},
						function(newSearchParams) {
							var searchLocation = {
								'pickUpLocationId' : $scope.searchParams.pickUp.locationId,
								'returnLocationId' : $scope.searchParams.return.locationId
							};

							commsLayerSearchService.getLocationSuppliersPopularDepotPair(searchLocation).then(function(response) {
								if (response.status == 'OK') {
									$scope.suppliersSearchParams     = [];
									$scope.suppliersPopularDepotPair = response.data;

									// not needed for actual search page
									$scope.searchRequestStatus.timeStart = date.getTime();

									for (supplierCode in $scope.suppliersPopularDepotPair) {
										var searchParams = {
											"supplier" : supplierCode,
											"age"      : $scope.searchParams.driverAge,
											"country"  : $scope.searchParams.countryCode,
											"pickUp"   : {
												"date"    : $scope.searchParams.pickUp.date,
												"time"    : $scope.searchParams.pickUp.time,
												"station" : $scope.suppliersPopularDepotPair[supplierCode].pickUpDepot
											},
											"return"   : {
												"date"    : $scope.searchParams.return.date,
												"time"    : $scope.searchParams.return.time,
												"station" : $scope.suppliersPopularDepotPair[supplierCode].returnDepot
											}
										};

										// not needed for actual search page
										$scope.suppliersSearchParams.push(searchParams);

										// not needed for actual search page
										$scope.searchRequestStatus.total++;

										commsLayerSearchService.searchSupplierVehicles(searchParams).then(function(response) {
											// not needed for actual search page
											$scope.searchRequestStatus.finished++;

											if (response.status == 'OK') {
												for (var vehicleIndex in response.data) {
													$scope.vehicles.push(response.data[vehicleIndex]);
												}
											}

											$scope.suppliersRequestStatus[response.supplierCode].executionTime = response.executionTime;

											if ($scope.searchRequestStatus.finished == $scope.searchRequestStatus.total) {
												var date = new Date();

												$scope.searchRequestStatus.timeEnd = date.getTime();

												$scope.searchRequestStatus.timeTotal = ($scope.searchRequestStatus.timeEnd - $scope.searchRequestStatus.timeStart) / 1000;
											}
										});
									}
								} else {
									console.log('failed getting the location suppliers popular depot pair');
								}
							});
						},
						true
					);
				}]);
			})();
		</script>
	</body>
</html>