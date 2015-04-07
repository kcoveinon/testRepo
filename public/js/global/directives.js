(function() {
	var globalDirectiveModule = angular.module('globalDirectiveModule', ['globalServiceModule']);

	globalDirectiveModule.directive('globalSectionView', ['$compile', '$timeout', 'globalSectionViewService', 'globalViewService', function($compile, $timeout, globalSectionViewService, globalViewService) {
		return {
			"restrict" : "A",
			"scope"    : {
				"sectionViewDefault" : "=?"
			},
			"link"     : function(scope, elem, attrs) {
				var sectionViews    = globalSectionViewService.getSectionViews();
				var sectionViewName = attrs.globalSectionView;

				if (scope.hasOwnProperty('sectionViewDefault')) {
					var sectionViewDetails = {};

					sectionViewDetails[sectionViewName] = scope.sectionViewDefault;

					globalSectionViewService.setSectionViews(sectionViewDetails);
				}

				scope.$watch(
					function() {
						return sectionViews[sectionViewName];
					},
					function(newSectionViewDetails) {
						elem.html('<div class="ui active inverted dimmer"><div class="ui text loader">Loading</div></div>');

						if(newSectionViewDetails.hasOwnProperty('html') || newSectionViewDetails.hasOwnProperty('url')) {
							var viewHtml = '';

							if(newSectionViewDetails.hasOwnProperty('html')) {
								viewHtml = newSectionViewDetails.html;

								elem.html(viewHtml);
								$compile(elem.contents())(scope);
							} else {
								globalViewService.fetchView(newSectionViewDetails).then(function(response) {
									viewHtml = response;

									elem.html(viewHtml);
									$compile(elem.contents())(scope);
								});
							}
						}
					},
					true
				);
			}
		};
	}]);

	globalDirectiveModule.directive('globalChangeSectionView', ['globalSectionViewService', function(globalSectionViewService) {
		return {
			"restrict" : "A",
			"scope"    : {
				"menu"      : "=",
				"menuItem"  : "=?",
				"menuIndex" : "@?"
			},
			"link"     : function(scope, elem, attrs) {
				elem.click(function() {
					var menuItem           = scope.menuItem || null;
					var menuIndex          = scope.menuIndex || 0;
					var sectionViewDetails = {};

					if (menuItem != null) {
						sectionViewDetails = menuItem;						
					} else {
						sectionViewDetails = scope.menu[menuIndex].sectionViewDetails;

						for (index in scope.menu) {
							scope.menu[index].active = ((menuIndex == index) ? true : false);
						}
					}

					globalSectionViewService.setSectionViews(sectionViewDetails);
					scope.$apply();
				});
			}
		};
	}]);

	globalDirectiveModule.directive('globalShowHideHover', [function() {
		return {
			"restrict" : "A",
			"link"     : function(scope, elem, attrs) {
				var selector = attrs.globalShowHideHover;

				elem.hover(
					function() {
						$(selector).fadeIn();
					},
					function() {
						$(selector).fadeOut();
					}
				);
			}
		};
	}]);
})();