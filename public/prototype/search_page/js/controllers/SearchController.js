pageApp.controller("SearchCtrl", function($scope,$http) {
    $http.get("search_page/js/fakedata/locations.json").success(function(response){
    	$scope.places = response;
    });
    $http.get("search_page/js/fakedata/countries.json").success(function(response){
    	$scope.countries = response;
    });

    $scope.featuredImages = [
		{src:'temp-featured.jpg',title:'Pic 1'},
		{src:'temp-featured3.jpg',title:'Pic 2'},
		{src:'temp-featured2.jpg',title:'Pic 3'},
		{src:'temp-featured3.jpg',title:'Pic 4'},
		{src:'temp-featured4.jpg',title:'Pic 5'}
	]; 
    $scope.reviewsImages = [
		{src:'temp-reviews.jpg',title:'Pic 1'},
		{src:'temp-reviews2.jpg',title:'Pic 2'},
		{src:'temp-reviews3.jpg',title:'Pic 3'},
		{src:'temp-reviews4.jpg',title:'Pic 4'},
		{src:'temp-reviews5.jpg',title:'Pic 5'}
	];
    $scope.popularLocations = [
		{imageSource:'temp-location1.jpg',Title:'Adelaide'},
		{imageSource:'temp-location2.jpg',Title:'Perth'},
		{imageSource:'temp-location3.jpg',Title:'Brisbane'},
		{imageSource:'temp-location4.jpg',Title:'Lorem'},
		{imageSource:'temp-location3.jpg',Title:'ipsum'},
		{imageSource:'temp-location2.jpg',Title:'Dolor Sit'},
		{imageSource:'temp-location1.jpg',Title:'Amet Ipsum'},
		{imageSource:'temp-location4.jpg',Title:'Canada'}
	]; 
    $scope.suppliers = [
		{
			Title: 'Lorem Dolor',
			Content: 'At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos.. ',
			Source: 'http://www.google.com',
			ImageSource: 'temp-supplier.jpg'
		},
		{
			Title: 'Accusamus',
			Content: 'At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos.. ',
			Source: 'http://www.google.com',
			ImageSource: 'temp-supplier.jpg'
		},
		{
			Title: 'Dignissimos Et',
			Content: 'At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos.. ',
			Source: 'http://www.google.com',
			ImageSource: 'temp-supplier.jpg'
		},
		{
			Title: 'Lorem Ipsum',
			Content: 'At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos.. ',
			Source: 'http://www.google.com',
			ImageSource: 'temp-supplier.jpg'
		},
		{
			Title: 'Iusto Set',
			Content: 'At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos.. ',
			Source: 'http://www.google.com',
			ImageSource: 'temp-supplier.jpg'
		},
		{
			Title: 'Vero Amet',
			Content: 'At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos.. ',
			Source: 'http://www.google.com',
			ImageSource: 'temp-supplier.jpg'
		}
	]; 
	$scope.faqs = [
		{
			ImageSource:'temp-gps.png',
			Title:'GPS Units can only be added',
			Options: [
						{Title: 'Option 1', Content: 'At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos'},
						{Title: 'Option 2', Content: 'At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos'},
						{Title: 'Option 3', Content: 'At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos'},
						{Title: 'Option 4', Content: 'At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos'}
						]
		},
		{
			ImageSource:'temp-childseat.png',
			Title:'Child Seat can only be added',
			Options: [
						{Title: 'Option 1', Content: 'At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos'},
						{Title: 'Option 2', Content: 'At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos'},
						{Title: 'Option 3', Content: 'At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos'},
						{Title: 'Option 4', Content: 'At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos'}
						]
		},
		{
			ImageSource:'temp-tol.png',
			Title:'What about toll roads',
			Options: [
						{Title: '', Content: 'At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos'},
						{Title: '', Content: 'At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos'},
						{Title: '', Content: 'At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos'},
						{Title: '', Content: 'At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos'}
						]
		}
	]; 
    $scope.sideBarInfo = [
		{imageSource:'temp-zero.jpg', Link:'http://www.google.com', Title:'Booking Fees, Cancellation Fees'},
		{imageSource:'temp-moneybag.jpg', Link:'http://www.google.com', Title:'Cheapest price guaranteed'},
		{imageSource:'temp-csr.jpg', Link:'http://www.google.com', Title:'Real people to talk to'},
		{imageSource:'temp-cc.jpg', Link:'http://www.google.com', Title:'No credit card required until you pickup the car'}
	]; 
	
	$scope.driverAges = [];
	for(var i=18;i<=75;i++) {
	  $scope.driverAges.push(i);
	}
	$scope.form = {};
	$scope.submitSearch = function(form){
		console.log(form); 
	};
});