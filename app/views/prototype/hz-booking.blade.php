<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <meta charset="utf-8" />
        <link type="text/css" href="{{{ asset('css/src/semantic.min.css')  }}}" rel="stylesheet"  media="screen"/>
        <meta name="_token" content="{{ csrf_token() }}" />
        <title>Hertz Booking Form</title>
    </head>
    <style type="text/css">
        button[ng-click]{
            cursor: pointer;
        }
    </style>
    <body>
    <div class="ui fixed inverted main menu">
        <a class="launch item">
          <h1>Hertz Booking Form</h1>
        </a>
    </div><br/><br/><br/>
    <div ng-app="BookingApp">
        <div ng-controller='BookingController'>
        <form name="myForm" class='ui form segment' ng-submit="myForm.$valid && addBooking.submit()">
            <div class='two fields'>
                <div class="field">
                    <label>Customer First Name</label>
                    <input placeholder="First Name" ng-model="bookingDetails.firstName" required type="text"/>
                </div>
                <div class="field">
                    <label>Customer Last Name</label>
                    <input placeholder="Pick Up Date" required ng-model="bookingDetails.lastName" type="text"/>
                </div>
            </div>
            <div class='two fields'>
                <div class="field">
                    <label>Pick Up Location Code</label>
                    <input placeholder="First Name" ng-model="bookingDetails.pickUpLocationCode" required type="text"/>
                </div>
                <div class="field">
                    <label>Pick Up Date</label>
                    <input placeholder="Pick Up Date" required ng-model="bookingDetails.pickUpDate" type="date"/>
                </div>
            </div>
            <div class='two fields'>
                <div class="field">
                    <label>Pick Up Time</label>
                    <input placeholder="Pick Up Time" required ng-model="bookingDetails.pickUpTime" type="text"/>
                </div>
                <div class="field">
                    <label>Return Location Code</label>
                    <input placeholder="Return Location Code" required ng-model="bookingDetails.returnLocationCode" type="text"/>
                </div>
            </div>
            <div class='two fields'>
                <div class="field">
                    <label>Return Date</label>
                    <input placeholder="Return Date" required ng-model="bookingDetails.returnDate" type="date"/>
                </div>
                <div class="field">
                    <label>Return Time</label>
                    <input placeholder="Return Time" required ng-model="bookingDetails.returnTime" type="text"/>
                </div>                                   
            </div>
            <div class='two fields'>
                <div class="field">
                    <label>Driver Age</label>
                    <input placeholder="Driver Age" required ng-model="bookingDetails.driverAge" type="number"/>
                </div>     
                <div class="field">
                    <label>Country Code</label>
                    <input placeholder="Country Code" required ng-model="bookingDetails.countryCode" type="text" value="AU"/>
                </div> 
            </div>            
            <div class='two fields'>
                <div class="field">
                    <label>Vehicle Category</label>
                    <select ng-model="vehicleCategory" required>
                        <option value="">All</option>
                        <option ng-repeat="vCategory in bookingDetails.vehicleCategory" value='<% vCategory.code %>'>
                            <% vCategory.alias %>
                        </option>
                    </select>
                </div>
                <div class='field'>
                    <label>Vehicle Class</label>
                    <select ng-model="vehicleClass" required>
                        <option value="">All</option>
                        <option ng-repeat="vClass in bookingDetails.vehicleClass" value="<% vClass.code %>">
                            <% vClass.alias %>
                        </option>
                    </select>
                </div>
            </div>            
            <div class='three fields'>
                <div class="field">
                    <label>Equipment Code</label>
                    <select ng-model="bookingDetails.eqCode">
                        <option ng-repeat="eCodes in bookingDetails.equipmentsCodes" value="<% $index %>">
                            <% eCodes.alias %>
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
                        <tr ng-repeat = "equips in bookingDetails.bookingEquipments track by $index">
                            <td><% equips.name %></td>
                            <td><% equips.qty %></td>
                            <td ng-click="removeEquipment($index)" style='cursor:pointer;font-weight:bold;'><% equips.action %></td>
                        </tr>
                    </tbody>
                </table>
            </div>              
                <input type='submit' class='ui blue button' id="submitBtn" value='Submit'/>
            <br/><br/>

            <div class="ui positive message" id="responseDiv" ng-if='response.xml !== ""'>
                <div class="header">
                    Booking Response
                </div>
                <div style='font-family:monospace;'><pre><% response.xml | json %></pre></div>
            </div>            
        </form>
        </div>
    </div>


    {{ HTML::script('js/src/angular-1.2.13.js') }}
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
                    'firstName' : 'Inon',
                    'lastName' : 'Baguio',
                    'pickUpLocationCode' : 'BNE',
                    'returnLocationCode' : 'ADL',
                    'pickUpDate'         : '2015-12-12',
                    'returnDate'         : '2015-12-15',
                    'pickUpTime'         : '10:00',
                    'returnTime'         : '12:00',
                    'driverAge'          : 25,
                    'vehicleCategory'    : [
                                            {code : '1' , alias : 'Car'},
                                            {code : '2' , alias : 'Van'},
                                            {code : '3' , alias : 'SUV'},
                                            {code : '4' , alias : 'Convertible'},
                                            {code : '7' , alias : 'Limousine'},
                                            {code : '8' , alias : 'Station Wagon'},
                                            {code : '9' , alias : 'Pickup'},
                                            {code : '10', alias : 'Motorhome'},
                                            {code : '11', alias : 'All-terrain'},
                                            {code : '12' , alias : 'Recreational'},
                                            {code : '13' , alias : 'Sport'},
                                            {code : '14' , alias : 'Special'},
                                            {code : '15' , alias : 'Pickup Extended Cab'},
                                            {code : '16' , alias : 'Regular Cab Pickup'},
                                            {code : '17' , alias : 'Special Offer'},
                                            {code : '18' , alias : 'Coupe'},
                                            {code : '19' , alias : 'Monospace'},
                                            {code : '20' , alias : '2 Wheel Vehicle'},
                                            {code : '21' , alias : 'Roadster'},
                                            {code : '21' , alias : 'Crossover'},
                                            {code : '23' , alias : 'Commercial/Van Truck'}
                                           ],
                    'vehicleClass'       : [ 
                                            {code : '1', alias : 'Mini' },
                                            {code : '2', alias : 'Subcompact' },
                                            {code : '3', alias : 'Economy' },
                                            {code : '4', alias : 'Compact' },
                                            {code : '5', alias : 'MidSize' },
                                            {code : '6', alias : 'Intermediate' },
                                            {code : '7', alias : 'Standard' },
                                            {code : '8', alias : 'Full Size' },
                                            {code : '9', alias : 'Luxury' },
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
                    'equipmentsCodes'    : [
                                            {code : '4',  alias : 'Ski Rack' },
                                            {code : '7',  alias : 'Infant Child Seat' },
                                            {code : '8',  alias : 'Child Toddler Seat' },
                                            {code : '9',  alias : 'Booster Seat' },
                                            {code : '11', alias : 'Hand Controls Right' },
                                            {code : '12', alias : 'Hand Controls Left' },
                                            {code : '13', alias : 'Neverlost System' },
                                            {code : '14', alias : 'Snow Tires' },
                                            {code : '18', alias : 'Spinner Knob' },
                                            {code : '27', alias : 'Satellite Radio' },
                                            {code : '29', alias : 'Seatbelt Extender' }
                                            ],
                    'rateId'             : '12',
                    'countryCode'        : 'AU',
                    'quantityArray'      : new Array(10),
                    'bookingEquipments'  : []
                };
                $scope.response = { xml: '' };
                $scope.addEquipments = function() {
                    if((typeof $scope.bookingDetails.eqCode !== "undefined"
                       && typeof $scope.bookingDetails.eqQty !== "undefined")
                       && ($scope.bookingDetails.eqCode !== "" && $scope.bookingDetails.eqQty !== "")
                       ){
                        var equipmentIndex = $scope.bookingDetails.eqCode;
                        $scope.bookingDetails.bookingEquipments.push({name      : $scope.bookingDetails.equipmentsCodes[equipmentIndex].alias, 
                                                                      qty       : $scope.bookingDetails.eqQty, 
                                                                      action    :'Delete',
                                                                      eqOTACode : $scope.bookingDetails.equipmentsCodes[equipmentIndex].code })
                        $scope.bookingDetails.eqCode = "";
                        $scope.bookingDetails.eqQty  = "";
                    }
                    else {
                        alert("Kindly complete the required fields");
                    }
                };

                $scope.removeEquipment = function(index) {
                    $scope.bookingDetails.bookingEquipments.splice(index, 1);
                }

                $scope.addBooking = {
                    submit: function() {
                        $('#responseDiv').fadeOut();
                        $('#submitBtn').html("Processing");
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
                                        rateId             : $scope.bookingDetails.rateId,
                                        countryCode        : $scope.bookingDetails.countryCode,
                                        vehicleEquipments  : $scope.bookingDetails.bookingEquipments,
                                        vehicleCategory    : $scope.vehicleCategory,
                                        vehicleClass       : $scope.vehicleClass,
                                        age                : $scope.bookingDetails.driverAge,
                                        firstName          : $scope.bookingDetails.firstName,
                                        lastName           : $scope.bookingDetails.lastName
                                    },
                                method : 'post',
                                type: 'json'
                            })
                            .success(function(data, status, headers, config) {
                                $('#responseDiv').fadeIn();
                                $('#submitBtn').html("Submit");    
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