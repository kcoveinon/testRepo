(function() {
	var semanticUiDirectiveModule = angular.module('semanticUiDirectiveModule', []);

	semanticUiDirectiveModule.directive('semanticUiDimmerHover', [function() {
		return {
			"restrict" : "A",
			"link"     : function(scope, elem, attrs) {
				elem.dimmer({
					"on" : "hover"
				});
			}
		};
	}]);

	semanticUiDirectiveModule.directive('semanticUiDropdown', [function() {
		return {
			"restrict" : "A",
			"scope"    : {
				"dropdownModel" : "="
			},
			"link"     : function(scope, elem, attrs) {
				elem.dropdown({
					"onShow" : function() {
						elem.dropdown({
							"onChange" : function(value, text, $choice) {
								scope.dropdownModel =  value;
								scope.$apply();
							}
						});
					}
				});
			}
		};
	}]);

	semanticUiDirectiveModule.directive('semanticUiCheckbox', [function() {
		return {
			"restrict" : "A",
			"scope"    : {
				"checkboxModel" : "="
			},
			"link"     : function(scope, elem, attrs) {
				scope.$watch(
					function() {
						return scope.checkboxModel;
					},
					function() {
						elem.find('input[type=checkbox]').prop('checked', scope.checkboxModel);
					}
				);

				elem.checkbox({
					"onEnable" : function() {
						scope.checkboxModel = true;
						scope.$apply();
					},
					"onDisable" : function() {
						scope.checkboxModel = false;
						scope.$apply();
					}
				});
			}
		};
	}]);

	semanticUiDirectiveModule.directive('semanticUiTabs', [function() {
		return {
			restrict : 'A',
			link     : function(scope, elem, attrs) {
				var tabClass = attrs.semanticUiTabs;

				elem.children().click(function() {
					var currentTab = $(this);
					var tabCount   = currentTab.index() + 1;

					currentTab.addClass('active');
					currentTab.siblings().removeClass('active');
					
					$('.tab[class*=' + tabClass + '-]').removeClass('active');
					$('.tab.' + tabClass + '-' + tabCount).addClass('active');
				});

				// elem.click(function() {
				// 	
				// 	elem.addClass('active');

				// 	alert(elem.index());

				// 	var oldElem = scope.activeBookingTab;
				// 	scope.activeBookingTab = elem.attr('id');

				// 	$('#'+oldElem+'Tab').removeClass('active');
				// 	$('#'+scope.activeBookingTab+'Tab').addClass('active');
				// });
			}
		};
	}]);
})();