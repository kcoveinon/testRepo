@extends('default.default')


@section('content')
    <div class="ui fixed inverted main menu">
        <a class="launch item">
          <h1>Booking Form</h1>
        </a>
    </div><br/><br/><br/>
    <div ng-app="BookingApp">
        {{ Form::open(array('url' => 'RS/doBookingWithEquipments','class'=>'ui form segment','id'=>'registration_form', 'files'=>'true','ng-controller'=>'BookingController')) }}
            <div class="field">
                <label>PickUpLocationCode</label>
                <input placeholder="First Name" ng-model='pickUpLocationCode' type="text">
            </div>
            <div class="field">
                <label>Pick Up Date</label>
                <input placeholder="First Name" ng-model="pickUpDate" type="text">
            </div>
            <div class="field">
                <label>Pick Up Time</label>
                <input placeholder="First Name" ng-model="pickUpTime" type="text">
            </div>
            <div class="field">
                <label>Return Location Code</label>
                <input placeholder="First Name" ng-model="returnLocationCode" type="text">
            </div>
            <div class="field">
                <label>Return Date</label>
                <input placeholder="First Name" ng-model="returnDate" type="text">
            </div>
            <div class="field">
                <label>Return Time</label>
                <input placeholder="First Name" ng-model="returnTime" type="text">
            </div>                                   
            <div class="field">
                <label>Vehicle Class</label>
                <select class="ui dropdown" ng-model="vehicleClass">
                  <option value="">All</option>
                  <option value="ECMR">ECMR</option>
                  <option value="ECAR">ECAR</option>
                  <option value="CDAR">CDAR</option>
                  <option value="IDAR">IDAR</option>
                  <option value="FCAR">FCAR</option>
                  <option value="IFAR">IFAR</option>
                  <option value="PVAR">PVAR</option>
                  <option value="IVAR">IVAR</option>
                </select>
            </div>
            <div class="field">
                <label>Rate ID</label>
                <input placeholder="First Name" ng-model="rateId" type="text">
            </div>
            <div class="field">
                <label>Country Code</label>
                <input placeholder="First Name" ng-model="countryCode" type="text" value="AU">
            </div>              


            <div class="ui blue submit button" ng-click="addBooking()">Submit</div>
        {{Form::close()}}
    </div>
@stop

@section('page_js')

    {{ HTML::script('js/src/angular-1.2.13.js') }}
    <script type="text/javascript">

        (function () {
            var BookingApp = angular.module('BookingApp', []);

            BookingApp.config(['$interpolateProvider', '$httpProvider', function($interpolateProvider, $httpProvider){
                $interpolateProvider.startSymbol('<%').endSymbol('%>');
                $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
            }]);

            BookingApp.controller('BookingController', ['$scope','$http', function($scope, $http){
                $scope.addBooking = function() {
                    $http(
                        {
                            data: {
                                    pickUpLocationCode : $scope.pickUpLocationCode,
                                    returnLocationCode : $scope.returnLocationCode,
                                    pickUpDate         : $scope.pickUpDate,
                                    pickUpTime         : $scope.pickUpTime,
                                    returnDate         : $scope.returnDate,
                                    returnTime         : $scope.returnTime,
                                    vehicleClass       : $scope.vehicleClass,
                                    rateId             : $scope.rateId,
                                    countryCode        : $scope.countryCode
                                },
                            url : 'doBookingWithEquipments', 
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
        //          onSuccess : function(){
        //                 alert();
        //             }                
        //     }          
        //   )
        // ;

    </script>
@stop