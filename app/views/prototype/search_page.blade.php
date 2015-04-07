<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css" rel="stylesheet">
        <link href="http://country-list.umpirsky.com/css/flags32.css" rel="stylesheet" type="text/css">
        <link href="{{ url() }}/prototype/search_page/css/reset.css" rel="stylesheet">
        <link href="{{ url() }}/prototype/search_page/css/style.css" rel="stylesheet">
        <title></title>
    </head>
    <body>
        
        <div class="container-fluid">
            <div class="row" ng-app="pageApp" ng-controller="SearchCtrl">
                <div class="col-sm-12 padding0" style="background: #cbcbcb;">
                    <div class="col-sm-10 col-sm-offset-1 padding0 cf">
                        <div class="col-sm-8">
                            <img class="img-responsive" src="{{ asset('prototype/search_page/images/temp-logo.png'); }}"/>
                        </div>
                        <div class="col-sm-4">Links</div>
                        <div class="col-sm-12 padding20 home-search">
                            <form role="form" name="form" ng-submit="submitSearch(form)">
                                <div class="form-group">
                                    <div class="location-enter input-group input-group-lg col-sm-6 col-sx-12 glyph-padding-lr15 glyph-overlap pull-left">
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-map-marker"></span>
                                        </span>
                                        <input required autocomplete="off" class="form-control" type="text" placeholder="Enter Pickup City or Airport Code" ng-model="form.searchLocationX">
                                        <ul ng-show="filteredX.length != 0 && form.searchLocationX.length > 1" class="autocomplete-list padding20 well">
                                            <li ng-repeat="place in filteredX = (places | filter:form.searchLocationX | orderBy:Country )" >
                                                <a ng-click="form.searchLocationX = fullPlaceName" ng-init="fullPlaceName = place.Name + ', ' + place.City + ', ' + place.Country">
                                                    <h4 style="font-weight: bold"><% place.Country %></h4>
                                                    <ul class="lpadding20">
                                                        <li><% place.Name %></li>
                                                        <li><% place.City %></li>
                                                        <li><% place.Country %></li>
                                                    </ul>
                                                </a>
                                            </li>
                                            <span class="result-count pull-right">showing <% filteredX.length %> result(s)</span>
                                        </ul>
                                    </div>
                                    <div class="location-return col-sm-6 col-sx-12 input-group-lg cf">
                                        <input required autocomplete="off" class="form-control" type="text" placeholder="Enter Pickup City or Airport Code" ng-model="form.searchLocationY">
                                        <ul ng-show="filteredY.length != 0 && form.searchLocationY.length > 1" class="autocomplete-list padding20">
                                            <li ng-repeat="place in filteredY = (places | filter:form.searchLocationY)" ng-click="form.searchLocationY = fullPlaceName">
                                                <a ng-init="fullPlaceName = place.Name + ', ' + place.City + ', ' + place.Country">
                                                    <h4 style="font-weight: bold"><% place.Country %></h4>
                                                    <ul class="lpadding20">
                                                        <li><% place.Name %></li>
                                                        <li><% place.City %></li>
                                                        <li><% place.Country %></li>
                                                    </ul>
                                                </a>
                                            </li>
                                            <span class="result-count pull-right">showing <% filteredY.length %> result(s)</span>
                                        </ul>
                                    </div>
                                    <div class="input-group input-group-lg col-sm-3 top20 glyph-padding-lr15 glyph-overlap pull-left">
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </span>
                                        <input required autocomplete="off" class="form-control" id="date-pickup" type="text" ng-model="form.datePickup">
                                    </div>
                                    <div class="input-group input-group-lg col-sm-3 top20 glyph-padding-lr15 glyph-overlap pull-left">
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </span>
                                        <input required autocomplete="off" class="form-control" id="date-return" type="text" ng-model="form.dateReturn">
                                    </div>
                                    <div class="driver-info input-group input-group-lg col-sm-3 top20 glyph-padding-lr15 glyph-overlap pull-left">
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-user"></span>
                                        </span>
                                        <button class="form-control lpadding0 text-left" type="button" ng-click="driverDetails = !driverDetails; driverCountryAge = ''"><% form.driverCountryAge %></button>
                                        <div class="doubledropdown" ng-show="driverDetails">
                                            <div class="dropdown pull-left dd-country">
                                                Driver Residency
                                                <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenuCountry" data-toggle="dropdown" aria-expanded="true">
                                                Select Country
                                                <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu f32" role="menu" aria-labelledby="dropdownMenuCountry">
                                                    <li role="presentation" ng-repeat="country in countries" class="flag <%country.Code | lowercase%>">
                                                        <a ng-click="form.driverCountryAge = country.Name" role="menuitem" href="#"><% country.Name %></a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="dropdown pull-left dd-age" ng-click="driverDetails = !driverDetails">
                                                Age
                                                <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenuAge" data-toggle="dropdown" aria-expanded="true">
                                                Select Age
                                                <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu f32" role="menu" aria-labelledby="dropdownMenuAge">
                                                    <li role="presentation" ng-repeat="age in driverAges">
                                                        <a ng-click="form.driverCountryAge = form.driverCountryAge + ', ' + age" role="menuitem" href="#"><% age %></a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 top20">
                                        <input type="submit" class="col-xs-12 btn-lg btn-primary" value="Search">
                                    </div>
                                </div>

                            </form>

                        </div>
                    </div>
                    <div class="col-sm-12 checked-info">
                        <img class="col-sm-10 col-sm-offset-1 img-responsive" src="{{ asset('prototype/search_page/images/temp-blue-checked.jpg'); }}"/>
                    </div>
                    <a href="#" class="col-sm-6 col-sm-offset-4 padding0">
                        <img class="img-responsive" src="{{ asset('prototype/search_page/images/temp-affiliates.png'); }}"/>
                    </a>
                </div>
                <div class="mini-sliders col-sm-10 col-sm-offset-1 padding0">
                    <div class="col-sm-6">
                        <h3 class="text-uppercase">As Featured in</h3>
                        <div class="col-sm-12 padding0 slider" images="featuredImages" slider></div>
                    </div>
                    <div class="col-sm-6">
                        <h3 class="text-uppercase">Our Customer Reviews</h3>
                        <div class="col-sm-12 padding0 slider" images="reviewsImages" slider></div>
                    </div>
                </div>
                <div class="col-sm-10 col-sm-offset-1 padding0 popular-locations cf">
                    <h3 class="text-uppercase">Our Most Popular Locations</h3>
                    <div class="line-separator col-sm-12 padding0"></div>
                    <div ng-repeat="location in popularLocations" class="col-sm-3 top15 pull-left padding0 margin5">
                        <img class="img-responsive" ng-src="{{ asset('prototype/search_page/images/<% location.imageSource %>'); }}"/>
                        <a class="text-uppercase loc-name col-sm-12 padding5 pull-left">
                            <span class="pull-left padding5"><% location.Title %></span>
                            <img class="arrow-location pull-right" src="{{ asset('prototype/search_page/images/temp-arrow-location.png'); }}"/>
                        </a>
                    </div>
                </div>
                <div class="col-sm-10 col-sm-offset-1 padding0">
                    <div class="col-sm-4">
                        <img class="img-responsive" src="{{ asset('prototype/search_page/images/temp-umbrella.png'); }}"/>
                    </div>
                    <div class="col-sm-8">
                        <h3 class="text-uppercase">Make Your Travel Relaxed, Secure and Insured</h3>
                    </div>
                </div>
                <div class="col-sm-10 col-sm-offset-1 padding0">
                    <h3 class="text-uppercase">Nothing Spells Adventure Like Campervans Road Trip!</h3>
                </div>
                <div class="line-separator col-sm-10 col-sm-offset-1 padding0"></div>
                <div class="col-sm-8 col-sm-offset-2 padding0 successful-bookings-info">
                    <img class="img-responsive" src="{{ asset('prototype/search_page/images/temp-bookings.jpg'); }}"/>
                </div>
                <div class="line-separator col-sm-10 col-sm-offset-1 padding0"></div>
                <div class="col-sm-10 col-sm-offset-1 padding0 info-tabs" ng-init="tab = 1">
                    <div class="col-sm-12 top20 padding10 lpadding0 tab-links">
                        <a ng-click="tab = 1" class="padding10" ng-class="{ active : tab == 1 }" href="">Why Us</a>
                        <a ng-click="tab = 2" class="padding10" ng-class="{ active : tab == 2 }" href="">Suppliers</a>
                        <a ng-click="tab = 3" class="padding10" ng-class="{ active : tab == 3 }" href="">Faq</a>
                    </div>
                    <div ng-show="tab == 1" class="tab-content col-sm-8 padding0">
                        <p class="top20 bottom20">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
                        <p class="top20 bottom20">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
                        <p class="top20 bottom20">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
                    </div>
                    <div ng-show="tab == 2" class="tab-content col-sm-8 padding0">
                        <div ng-repeat="supplier in suppliers" class="col-sm-6 top20 padding0">
                            <h3 class="text-uppercase"><% supplier.Title %></h3>
                            <img class="img-responsive pull-left padding15 lpadding0" ng-src="{{ asset('prototype/search_page/images/<% supplier.ImageSource %>'); }}"/>
                            <p class="text-left padding15"><% supplier.Content %><a href="<% supplier.Source %>">Read More</a></p>
                        </div>
                    </div>
                    <div ng-show="tab == 3" class="tab-content col-sm-8 padding0 faqs">
                        <div ng-repeat="faq in faqs" class="col-sm-4 padding0 top20">
                            <img class="img-responsive center-block" ng-src="{{ asset('prototype/search_page/images/<% faq.ImageSource %>'); }}"/>
                            <strong><% faq.Title %></strong>
                            <ul class="top20">
                                <li ng-repeat="option in faq.Options">
                                    <strong><% option.Title %></strong>
                                    <p class="top20 bottom20"><% option.Content %></p>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-sm-4 sidebar-info">
                        <ul class="pull-left">
                            <li ng-repeat="info in sideBarInfo" class="col-sm-12 text-left padding10">
                                <a ng-href="<% info.Link %>">
                                    <img ng-src="{{ asset('prototype/search_page/images/<% info.imageSource %>'); }}" class="pull-left col-sm-4"/>
                                </a>
                                <p class="col-sm-8 top20"><% info.Title %></p>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="line-separator col-sm-10 col-sm-offset-1 padding0 top20"></div>
                <div class="col-sm-10 col-sm-offset-1 padding0 successful-bookings-info">
                    <div class="top20 text-left">
                        <img class="img-responsive pull-left rpadding20" src="{{ asset('prototype/search_page/images/clock.png'); }}"/>
                        <h3 class="text-uppercase">Last Minute Car Rental</h3>
                        <p class="top20">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                    </div>
                </div>
                <div class="line-separator col-sm-10 col-sm-offset-1 padding0 bottom20 top20"></div>
                <div class="col-sm-12 social-buttons">
                        <img class="col-sm-10 col-sm-offset-1 img-responsive" src="{{ asset('prototype/search_page/images/temp-blue-social.jpg'); }}"/>
                    </div>
                <div class="col-sm-10 col-sm-offset-1 padding0 top20">
                    <div class="col-sm-2 col-sm-offset-1">
                        <b>About Us</b>
                        <ul class="top10">
                            <li>Lorem</li>
                            <li>Ipsum</li>
                            <li>Consectetur</li>
                            <li>Dolor</li>
                            <li>Nostrud</li>
                            <li>Adipiscing</li>
                        </ul>
                    </div>
                    <div class="col-sm-2">
                        <b>Suppliers</b>
                        <ul class="top10">
                            <li>Lorem</li>
                            <li>Ipsum</li>
                            <li>Consectetur</li>
                            <li>Dolor</li>
                            <li>Nostrud</li>
                            <li>Adipiscing</li>
                            <li>more...</li>
                        </ul>
                    </div>
                    <div class="col-sm-2">
                        <b>Our Locations</b>
                        <ul class="top10">
                            <li>Lorem</li>
                            <li>Ipsum</li>
                            <li>Consectetur</li>
                            <li>Dolor</li>
                            <li>Nostrud</li>
                            <li>Adipiscing</li>
                            <li>more...</li>
                        </ul>
                    </div>
                    <div class="col-sm-2">
                        <b>Vehicles</b>
                        <ul class="top10">
                            <li>Lorem</li>
                            <li>Ipsum</li>
                            <li>Consectetur</li>
                            <li>Dolor</li>
                            <li>Nostrud</li>
                            <li>Adipiscing</li>
                        </ul>
                    </div>
                    <div class="col-sm-2">
                        <b>Other</b>
                        <ul class="top10">
                            <li>Lorem</li>
                            <li>Ipsum</li>
                            <li>Consectetur</li>
                            <li>Dolor</li>
                            <li>Nostrud</li>
                            <li>Adipiscing</li>
                        </ul>
                    </div>
                </div>
                <footer class="col-sm-10 col-sm-offset-1 padding0"></footer>
            </div>
        </div>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
        <script src="http://code.angularjs.org/1.2.9/angular.min.js" type="text/javascript"></script>
        <script src="http://code.angularjs.org/1.2.9/angular-animate.min.js"></script>
        <script src="{{ url() }}/prototype/search_page/js/pageApp.js"></script>
        <script src="{{ url() }}/prototype/search_page/js/controllers/SearchController.js"></script>
        <script src="{{ url() }}/prototype/search_page/js/directives/SliderDirective.js"></script>
        <link rel="stylesheet" type="text/css" href="{{ url() }}/prototype/search_page/css/jquery.datetimepicker.css"/ >
        <script src="{{ url() }}/prototype/search_page/js/plugins/jquery.datetimepicker.js"></script>   

        <script type="text/javascript">
            (function() {
                $('#date-pickup').datetimepicker({
                    format:'Y/m/d H:i',
                    onShow:function( ct ){
                        this.setOptions({
                            maxDate:$('#date-return').val().split(" ", 1).toString()?$('#date-return').val().split(" ", 1).toString():false,
                            minDate:'-1970/01/01'//yesterday is minimum date(for today use 0 or -1970/01/01)
                        })
                    },
                    timepicker:true
                });
                $('#date-return').datetimepicker({
                    format:'Y/m/d H:i',
                    onShow:function( ct ){
                        this.setOptions({
                            minDate:$('#date-pickup').val().split(" ", 1).toString()?$('#date-pickup').val().split(" ", 1).toString():false
                        })
                    },
                    timepicker:true
                });
            })();
        </script>
    </body>
</html>


    
