<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <meta charset="utf-8" />
        <link type="text/css" href="{{{ asset('css/src/semantic.min.css')  }}}" rel="stylesheet"  media="screen"/>
        <meta name="_token" content="{{ csrf_token() }}" />
        <title> RedSpot Booking Form</title>
    </head>
    <style type="text/css">
        button[ng-click]{
            cursor: pointer;
        }
    </style>
    <body>
    <div class='container' ng-app="BookingApp">
        <div class="ui fixed inverted main menu">
            <a class="launch item">
              <h1>RedSpot Booking Form</h1>
            </a>
        </div><br/><br/><br/>

        <div ng-controller='BookingController'>
            <form name="myForm" class='ui form segment' ng-submit="myForm.$valid && addBooking.submit()">

                <div class='two fields'>
                    <div class="field">
                        <label>PickUpLocationCode</label>
                        <input placeholder="First Name" required  name='pickUpLocationCode' ng-model="bookingDetails.pickUpLocationCode" type="text"/>
                    </div>
                    <div class="field">
                        <label>Pick Up Date</label>
                        <input type="date" placeholder="Pick Up Date" required  name='pickUpDate' ng-model="bookingDetails.pickUpDate" type="text"/>
                    </div>
                </div>
                <div class='two fields'>
                    <div class="field">
                        <label>Pick Up Time</label>
                        <input placeholder="Pick Up Time" required  name='pickUpTime' ng-model="bookingDetails.pickUpTime" type="text"/>
                    </div>
                    <div class="field">
                        <label>Return Location Code</label>
                        <input placeholder="Return Location Code" required  name='returnLocationCode' ng-model="bookingDetails.returnLocationCode" type="text"/>
                    </div>
                </div>
                <div class='two fields'>
                    <div class="field">
                        <label>Return Date</label>
                        <input type="date" placeholder="Return Date" required  name='returnDate' ng-model="bookingDetails.returnDate" type="text"/>
                    </div>
                    <div class="field">
                        <label>Return Time</label>
                        <input placeholder="Return Time" required  name='returnTime' ng-model="bookingDetails.returnTime" type="text"/>
                    </div>                                   
                </div>      
                <div class='two fields'>
                    <div class="field">
                        <label>Rate ID</label>
                        <input placeholder="Rate ID" required  name='rateId' ng-model="bookingDetails.rateId" type="text"/>
                    </div>
                    <div class="field">
                        <label>Country Code</label>
                        <input placeholder="Country Code" required  name='countryCode' ng-model="bookingDetails.countryCode" type="text" value="AU"/>
                    </div>
                    </div>
                <div class="field">
                    <label>Vehicle Class</label>
                    <select ng-model="vehicleClass" name='vehicleClass' class="ui dropdown" required>
                        <option value=""></option>
                        <option ng-repeat="vClass in bookingDetails.vehicleClass" value="<% vClass %>">
                            <% vClass %>
                        </option>
                    </select>
                </div>
                <div class='three fields'>
                    <div class="field">
                        <label>Equipment Code</label>
                        <select ng-model="bookingDetails.eqCode" class="ui dropdown">
                            <option ng-repeat="eCodes in bookingDetails.equipmentsCodes">
                                <% eCodes %>
                            </option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Quantity</label>
                        <select ng-model="bookingDetails.eqQty" class="ui dropdown">
                            <option ng-repeat="i in bookingDetails.quantityArray track by $index">
                                <% $index + 1 %>
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
                <input type='submit' class='ui blue button' value='Submit'/>
                <br/><br/>
                <div class="ui positive message" id="responseDiv" ng-if='response.xml !== ""'>
                    <div class="header">
                        Booking Response
                    </div>
                    <div style='font-family:monospace;'><pre><% response.xml | json %></pre></div>
                </div>            
            {{Form::close()}}
        </div>

        {{ HTML::script('js/src/jquery-2.0.0.min.js') }}
        {{ HTML::script('js/src/angular-1.2.13.js') }}
        {{ HTML::script('js/src/semantic.min.js') }}
        <script type="text/javascript">

            (function () {
                var BookingApp = angular.module('BookingApp', ['BookingControllerModule']);

                BookingApp.config(['$interpolateProvider', '$httpProvider', function ($interpolateProvider, $httpProvider){
                    $interpolateProvider.startSymbol('<%').endSymbol('%>');
                    $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
                }]);

                var bookingControllerModule = angular.module('BookingControllerModule', []);
                bookingControllerModule.controller('BookingController', ['$scope', '$http', function ($scope, $http){

                    $scope.bookingDetails = {
                        'pickUpLocationCode' : 'BNE',
                        'returnLocationCode' : 'ADL',
                        'pickUpTime'         : '10:00',
                        'returnTime'         : '12:00',
                        'vehicleClass'       : [ 'ECMR', 'ECAR', 'CDAR', 'IDAR', 'FCAR', 'IFAR', 'PVAR', 'IVAR'],
                        'vehicleCategory'    : [ 
                                                {code : '1',  alias : 'Mini' },
                                                {code : '2',  alias : 'Subcompact' },
                                                {code : '3',  alias : 'Economy' },
                                                {code : '4',  alias : 'Compact' },
                                                {code : '5',  alias : 'MidSize' },
                                                {code : '6',  alias : 'Intermediate' },
                                                {code : '7',  alias : 'Standard' },
                                                {code : '8',  alias : 'Full Size' },
                                                {code : '9',  alias : 'Luxury' },
                                                {code : '10', alias : 'Premium' },
                                                {code : '23', alias : 'Special' },
                                                {code : '32', alias : 'Mini-Elite' },
                                                {code : '34', alias : 'Economy Elite' },
                                                {code : '35', alias : 'Compact Elite' },
                                                {code : '36', alias : 'Intermediate Elite' },
                                                {code : '37', alias : 'Standard Elite' },
                                                {code : '38', alias : 'Fullsize Elite' },
                                                {code : '39', alias : 'Premium Elite' },
                                                {code : '40', alias : 'Luxury Elite'},
                                                {code : '41', alias : 'Oversize'}
                                               ],                    
                        'equipmentsCodes'    : [ 'BCAPS' , 'BOOST', 'BSEAT', 'SATNV' ],
                        'rateId'             : '12',
                        'countryCode'        : 'AU',
                        'quantityArray'      : new Array(10),
                        'bookingEquipments'  : []
                    };
                    $scope.response = { xml: '' };
                    $scope.addEquipments = function() {
                        alert();
                        var eqCode = $scope.bookingDetails.eqCode;
                        var eqQty = $scope.bookingDetails.eqQty;
                        if( (typeof eqCode !== 'undefined' && typeof eqQty !== 'undefined') && (eqCode !== "" && eqQty !== "")) {
                            $scope.bookingDetails.bookingEquipments.push({name:eqCode, qty:eqQty, action:'Delete'})
                            $scope.bookingDetails.eqCode = "";
                            $scope.bookingDetails.eqQty = "";
                        } else {
                            alert("Kindly complete the required fields");
                        }
                    };

                    $scope.removeEquipment = function(index) {
                        $scope.bookingDetails.bookingEquipments.splice(index, 1);
                    }

                    $scope.addBooking = {
                        submit: function() {
                            $('#responseDiv').fadeOut();
                            $http(
                                {
                                    url : 'do-booking-with-equipments', 
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
                                    method : 'POST',
                                    type: 'json'
                                })
                                .success(function(data, status, headers, config) {
                                    $('#responseDiv').fadeIn();
                                    $scope.response = {
                                        xml : data
                                    }
                                })
                        }
                    }
                }]);
            })();

        </script>
        </div>
    </body>


    {{ HTML::script('js/src/jquery-2.0.0.min.js') }}
    {{ HTML::script('js/src/semantic.min.js') }}
     @yield('page_js')

</html>