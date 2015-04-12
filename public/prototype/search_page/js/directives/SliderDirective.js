pageApp.directive('slider', function ($timeout) {
  return {
    restrict: 'A',
	scope:{
		images: '='
	},
	template: '<div class="line-separator col-sm-10 padding0 top10"></div>' +
			  '<div class="col-sm-2 padding0 text-right next-prev">' +
			      '<a ng-click="prev()"><img src="{{ asset(\'prototype/search_page/images/next-prev-circle.png\'); }}"/></a>' +
				  '<a ng-click="next()"><img class="img-flip" src="{{ asset(\'prototype/search_page/images/next-prev-circle.png\'); }}"/></a>' +
			  '</div>' +
			  '<img class="img-responsive slide" ng-repeat="image in images" ng-show="image.visible" ng-src="/prototype/search_page/images/<% image.src %>"/>',
    link: function (scope, elem, attrs) {
		scope.currentIndex=0;

		scope.next=function(){
			scope.currentIndex<scope.images.length-1?scope.currentIndex++:scope.currentIndex=0;
		};
		
		scope.prev=function(){
			scope.currentIndex>0?scope.currentIndex--:scope.currentIndex=scope.images.length-1;
		};
		
		scope.$watch('currentIndex',function(){
			scope.images.forEach(function(image){
				image.visible=false;
			});
			scope.images[scope.currentIndex].visible=true;
		});
		
		/* Start: For Automatic slideshow*/
		
		var timer;
		
		var sliderFunc=function(){
			timer=$timeout(function(){
				scope.next();
				timer=$timeout(sliderFunc,5000);
			},5000);
		};
		
		sliderFunc();
		
		scope.$on('$destroy',function(){
			$timeout.cancel(timer);
		});
		
		/* End : For Automatic slideshow*/
		
    }
  }
});