<!DOCTYPE html>
<html lang="en" ng-app="BookingApp">
	<head>
		<meta charset="UTF-8">
		<title>Prototype - Europcar Booking</title>

		<link rel="stylesheet" href="{{ url() }}/css/vendor/semantic_ui/semantic.css">
		<link rel="stylesheet" href="{{ url() }}/css/vendor/font_awesome/font-awesome.min.css">

		<style>
			body {
				font-family: Arial, Helvetica, sans-serif;
				font-size: 12px;
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

			.ui.booking-page {
				width: 800px;
				margin: auto;
			}
		</style>
	</head>

	<body>
		<!-- /book/reservation/20151213/1000/20151213/1900/TXLT01/TXLT01/IDMR/MR/JOHN/SMITH/DE -->
		<div ng-controller="BookingController">
			<div class="ui one column grid booking-page">
				<div class="column process-report">
					<div class="ui two column grid">
						<div class="sixteen wide column process-report-header">
							GENERAL
						</div>
						<div class="two wide column">Status</div>
						<div class="fourteen wide column">: <% booking.status %></div>

						<div class="sixteen wide column process-report-header">
							RESPONSE
						</div>
						<div class="sixteen wide column">
							<span ng-if="booking.response == ''">---</span>
							<pre ng-if="booking.response != ''"><% booking.response | json %></pre>
						</div>
					</div>
				</div>

				<div class="column">
					<div class="ui fluid form">
						<h4 class="ui header">Driver Details</h4>
						<div class="four fields">
							<div class="field ">
								<label>Title</label>
								<input type="text" ng-model="bookingDetails.driver.title" placeholder="MR">
							</div>

							<div class="field ">
								<label>First Name</label>
								<input type="text" ng-model="bookingDetails.driver.firstName" placeholder="JOHN">
							</div>

							<div class="field ">
								<label>Last Name</label>
								<input type="text" ng-model="bookingDetails.driver.lastName" placeholder="SMITH">
							</div>

							<div class="field ">
								<label>Country Code</label>
								<input type="text" ng-model="bookingDetails.driver.countryOfResidence" placeholder="DE">
							</div>
						</div>

						<h4 class="ui header">Pick Up Details</h4>
						<div class="three fields">
							<div class="field ">
								<label>Date</label>
								<input type="text" ng-model="bookingDetails.pickUp.date" placeholder="20151213">
							</div>

							<div class="field ">
								<label>Time</label>
								<input type="text" ng-model="bookingDetails.pickUp.time" placeholder="1000">
							</div>

							<div class="field ">
								<label>Station Code</label>
								<input type="text" ng-model="bookingDetails.pickUp.stationCode" placeholder="TXLT01">
							</div>
						</div>

						<h4 class="ui header">Return Details</h4>
						<div class="three fields">
							<div class="field ">
								<label>Date</label>
								<input type="text" ng-model="bookingDetails.return.date" placeholder="20151215">
							</div>

							<div class="field ">
								<label>Time</label>
								<input type="text" ng-model="bookingDetails.return.time" placeholder="1900">
							</div>

							<div class="field ">
								<label>Station Code</label>
								<input type="text" ng-model="bookingDetails.return.stationCode" placeholder="TXLT01">
							</div>
						</div>

						<h4 class="ui header">Vehicle Details</h4>
						<div class="field ">
							<label>Car Category Code</label>
							<input type="text" ng-model="bookingDetails.carCategoryCode" placeholder="IDMR">
						</div>

						<h4 class="ui header">Equipment List Details</h4>

						<div class="three fields">
							<div class="six wide field ">
								<label>Equipment Code</label>
							</div>

							<div class="six wide field ">
								<label>Equipment Quantity</label>
							</div>
						</div>

						<div class="three fields" ng-repeat="equipment in bookingDetails.equipmentList">
							<div class="six wide field ">
								<input type="text" ng-model="equipment.code" placeholder="CSB">
							</div>

							<div class="six wide field ">
								<input type="number" ng-model="equipment.quantity" placeholder="1" min="1">
							</div>

							<div class="four wide field ">
								<input type="button" class="ui fluid red button floated left" value="Remove" ng-click="removeEquipmentField($index)">
							</div>
						</div>

						<div class="field">
							<input type="button" class="ui blue button floated right" value="Add Equipment" ng-click="addEquipmentField()">
						</div>

						<div class="field">
							<input type="button" class="ui positive button" value="Book" ng-click="reserveBooking()">
						</div>
					</div>
				</div>
			</div>
		</div>

		<script type="text/javascript" src="{{ url() }}/js/vendor/angularjs/angular.min.js"></script>

		<script type="text/javascript">
			(function() {
				// services
				var bookingServicesModule = angular.module('BookingServicesModule', []);

				bookingServicesModule.service('BookingService', ['$http', '$q', function($http, $q) {
					this.reserveBooking = function(params) {
						var dfd    = $q.defer();
						var apiUrl = '{{ url() }}/EC/book/reservation';

						$http({
							"method" : "POST",
							"url"    : apiUrl,
							"data"   : params
						}).success(function(response) {
							dfd.resolve(response);
						}).error(function(response) {
							// log error
						});			

						return dfd.promise;
					}
				}]);

				// controller
				var bookingControllersModule = angular.module(
					'BookingControllerModule', 
					[
						'BookingServicesModule'
					]
				);

				bookingControllersModule.controller('BookingController', ['$scope', 'BookingService', function($scope, bookingService) {
					$scope.booking = {
						"response" : '',
						"status"   : '---'
					}
					$scope.bookingDetails = {
						"carCategoryCode" : "IDMR",
						"equipmentList" : [
							{
								"code"     : "",
								"quantity" : ""
							}
						],
						"driver" : {
							"title"     : "MR",
							"firstName" : "JOHN",
							"lastName"  : "SMITH",
							"countryOfResidence" : "DE"
						},
						"pickUp" : {
							"stationCode" : "TXLT01",
							"date"        : "20151213",
							"time"        : "1000"
						},
						"return" : {
							"stationCode" : "TXLT01",
							"date"        : "20151215",
							"time"        : "1900"
						}
					};

					$scope.reserveBooking = function() {
						// /book/reservation/20151213/1000/20151213/1900/TXLT01/TXLT01/IDMR/MR/JOHN/SMITH/DE
						var bookingDetails = {
							"pickUpDate"         : $scope.bookingDetails.pickUp.date, 
							"pickUpTime"         : $scope.bookingDetails.pickUp.time,
							"pickUpStationCode"  : $scope.bookingDetails.pickUp.stationCode,
							"returnDate"         : $scope.bookingDetails.return.date,
							"returnTime"         : $scope.bookingDetails.return.time,
							"returnStationCode"  : $scope.bookingDetails.return.stationCode,
							"countryOfResidence" : $scope.bookingDetails.driver.countryOfResidence,
							"title"              : $scope.bookingDetails.driver.title,
							"firstName"          : $scope.bookingDetails.driver.firstName,
							"lastName"           : $scope.bookingDetails.driver.lastName,
							"carCategoryCode"    : $scope.bookingDetails.carCategoryCode,
							"equipmentList"      : $scope.bookingDetails.equipmentList
						};

						$scope.booking.status   = 'booking reservation.';
						$scope.booking.response = '';

						bookingService.reserveBooking(bookingDetails).then(function(response) {
							$scope.booking.response = response;

							$scope.booking.status = 'completed booking reservation.';
						});
					};

					$scope.addEquipmentField = function() {
						$scope.bookingDetails.equipmentList.push({
							"code"     : "",
							"quantity" : ""
						});
					}

					$scope.removeEquipmentField = function(index) {
						if (index > -1) {
							$scope.bookingDetails.equipmentList.splice(index, 1);
						}
					}
				}]);

				// app
				var bookingApp = angular.module(
					'BookingApp', 
					[
						'BookingControllerModule',
						'BookingServicesModule'
					]
				);

				bookingApp.config(['$interpolateProvider', '$httpProvider', function($interpolateProvider, $httpProvider) {
					$interpolateProvider.startSymbol('<%').endSymbol('%>');
					$httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
				}]);
			})();
		</script>
	</body>
</html>