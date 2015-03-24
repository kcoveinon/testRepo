@extends('default.default')


@section('content')
    <div class="ui fixed inverted main menu">
        <a class="launch item">
          <h1>RedSpot Booking Form</h1>
        </a>
    </div><br/><br/><br/>
    <div ng-app="BookingApp">
        {{ Form::open(array('url' => 'RS/doBookingWithEquipments','class'=>'ui form segment','id'=>'registration_form', 'files'=>'true','ng-controller'=>'BookingController')) }}
            <div class='two fields'>
                <div class="field">
                    <label>PickUpLocationCode</label>
                    <input placeholder="First Name" ng-model="bookingDetails.pickUpLocationCode" type="text">
                </div>
                <div class="field">
                    <label>Pick Up Date</label>
                    <input placeholder="First Name" ng-model="bookingDetails.pickUpDate" type="text">
                </div>
            </div>
            <div class='two fields'>
                <div class="field">
                    <label>Pick Up Time</label>
                    <input placeholder="First Name" ng-model="bookingDetails.pickUpTime" type="text">
                </div>
                <div class="field">
                    <label>Return Location Code</label>
                    <input placeholder="First Name" ng-model="bookingDetails.returnLocationCode" type="text">
                </div>
            </div>
            <div class='two fields'>
                <div class="field">
                    <label>Return Date</label>
                    <input placeholder="First Name" ng-model="bookingDetails.returnDate" type="text">
                </div>
                <div class="field">
                    <label>Return Time</label>
                    <input placeholder="First Name" ng-model="bookingDetails.returnTime" type="text">
                </div>                                   
            </div>      
            <div class='two fields'>
                <div class="field">
                    <label>Rate ID</label>
                    <input placeholder="First Name" ng-model="bookingDetails.rateId" type="text">
                </div>
                <div class="field">
                    <label>Country Code</label>
                    <input placeholder="First Name" ng-model="bookingDetails.countryCode" type="text" value="AU">
                </div>
                </div>
            <div class="field">
                <label>Vehicle Class</label>
                <select  ng-model="vehicleClass">
                  <option value="">All</option>
                  <option ng-repeat = "vClass in bookingDetails.vehicleClass" value="<% vClass %>">
                        <% vClass %>
                  </option>
                </select>
            </div>
            <div class='three fields'>
                <div class="field">
                    <label>Equipment Code</label>
                    <select ng-model="bookingDetails.eqCode">
                      <option ng-repeat = "eCodes in bookingDetails.equipmentsCodes">
                            <% eCodes %>
                      </option>
                    </select>
                </div>
                <div class="field">
                    <label>Quantity</label>
                    <select ng-model="bookingDetails.eqQty">
                      <option ng-repeat="i in bookingDetails.quantityArray track by $index">
                            <% $index+1 %>
                      </option>
                    </select>
                </div>
                <div class="field">
                    <div class="ui default button" ng-click="addEquipments()" style='margin-top:21px;'>Add Equipment</div>
                </div>
            </div>
            <div class="field" ng-if ='bookingDetails.bookingEquipments.length > 0'>
                <label>Booking Equipments</label>
                <table class="ui table">
                    <thead>
                        <tr>
                          <th>Equipment Code</th>
                          <th>Quanitity</th>
                          <th>/</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr ng-repeat = "equips in bookingDetails.bookingEquipments">
                            <td><% equips.name %></td>
                            <td><% equips.qty %></td>
                            <td ng-click="removeEquipment(index)" style='cursor:pointer;font-weight:bold;'><% equips.action %></td>
                        </tr>
                    </tbody>
                </table>
            </div>            
            <div class="ui blue button" ng-click="addBooking()">Submit</div>
        {{Form::close()}}
    </div>
@stop

@section('page_js')

    {{ HTML::script('js/src/angular-1.2.13.js') }}
    <script type="text/javascript">
        (function () {
            var BookingApp = angular.module('BookingApp', ['BookingControllerModule']);

            BookingApp.config(['$interpolateProvider', '$httpProvider', function($interpolateProvider, $httpProvider){
                $interpolateProvider.startSymbol('<%').endSymbol('%>');
                $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
            }]);

            var bookingControllerModule = angular.module('BookingControllerModule', []);
            bookingControllerModule.controller('BookingController', ['$scope', '$http', function($scope, $http){
                $scope.bookingDetails = {
                    'pickUpLocationCode' : 'BNE',
                    'returnLocationCode' : 'ADL',
                    'pickUpDate'         : '12/12/2015',
                    'returnDate'         : '12/15/2015',
                    'pickUpTime'         : '10:00',
                    'returnTime'         : '12:00',
                    'vehicleClass'       : [ 'ECMR', 'ECAR', 'CDAR', 'IDAR', 'FCAR', 'IFAR', 'PVAR', 'IVAR'],
                    'equipmentsCodes'    : [ 'BCAPS' , 'BOOST', 'BSEAT', 'SATNV'],
                    'rateId'             : '12',
                    'countryCode'        : 'AU',
                    'quantityArray'      : new Array(10),
                    'bookingEquipments' : []
                };

                $scope.addEquipments = function() {
                    if($scope.eqCode !== "" || $scope.eqQty !== "") {
                        $scope.bookingDetails.bookingEquipments.push({name:$scope.eqCode, qty:$scope.eqQty, action:'Delete'})
                    }
                    else {
                        alert("Kindly complete the required fields");
                    }
                };

                $scope.removeEquipment = function(index) {
                    $scope.bookingDetails.bookingEquipments.splice(index, 1);
                }

                $scope.addBooking = function() {
                    $http(
                        {
                            url : 'doBookingWithEquipments', 
                            data: {
                                    pickUpLocationCode : $scope.bookingDetails.pickUpLocationCode,
                                    returnLocationCode : $scope.bookingDetails.returnLocationCode,
                                    pickUpDate         : $scope.bookingDetails.pickUpDate,
                                    pickUpTime         : $scope.bookingDetails.pickUpTime,
                                    returnDate         : $scope.bookingDetails.returnDate,
                                    returnTime         : $scope.bookingDetails.returnTime,
                                    vehicleClass       : $scope.vehicleClass,
                                    rateId             : $scope.bookingDetails.rateId,
                                    countryCode        : $scope.bookingDetails.countryCode,
                                    vehicleEquipments  : $scope.bookingDetails.bookingEquipments
                                },
                            method : 'POST'
                        })
                        .success(function(data, status, headers, config) {

                        })
                    };
            }]);
        })();

        // var requiredField = "This field is required";
        // $('.ui.form')
        //   .form({
        //     pickUpLocationCode: {
        //       identifier  : 'pickUpLocationCode',
        //       rules: [
        //         {
        //           type   : 'empty',
        //           prompt : requiredField
        //         }
        //       ]
        //     },
        //     returnLocationCode: {
        //       identifier  : 'returnLocationCode',
        //       rules: [
        //         {
        //           type   : 'empty',
        //           prompt : requiredField
        //         }
        //       ]
        //     },
        //     pickUpDate: {
        //       identifier : 'pickUpDate',
        //       rules: [
        //         {
        //           type   : 'empty',
        //           prompt : requiredField
        //         }
        //       ]
        //     },
        //     returnDate: {
        //       identifier : 'returnDate',
        //       rules: [
        //         {
        //           type   : 'empty',
        //           prompt : requiredField
        //         }
        //       ]
        //     },
        //     pickUpTime: {
        //       identifier : 'pickUpTime',
        //       rules: [
        //         {
        //           type   : 'empty',
        //           prompt : requiredField
        //         }
        //       ]
        //     },     
        //     returnTime: {
        //       identifier : 'returnTime',
        //       rules: [
        //         {
        //           type   : 'empty',
        //           prompt : requiredField
        //         }
        //       ]
        //     },
        //     vehicleClass: {
        //       identifier : 'vehicleClass',
        //       rules: [
        //         {
        //           type   : 'empty',
        //           prompt : requiredField
        //         }
        //       ]
        //     },    
        //     rateId: {
        //       identifier : 'rateId',
        //       rules: [
        //         {
        //           type   : 'empty',
        //           prompt : requiredField
        //         }
        //       ]
        //     },
        //     countryCode: {
        //       identifier : 'countryCode',
        //       rules: [
        //         {
        //           type   : 'empty',
        //           prompt : requiredField
        //         }
        //       ]
        //     },                                   
        //   },
        //     {
        //         inline: true,
        //         on: 'blur',
        //         transition: 'fade down',               
        //     }          
        //   )
        // ;

    </script>
@stop