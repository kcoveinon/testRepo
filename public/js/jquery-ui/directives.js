(function(){
	var jqueryUiDirectiveModule = angular.module('jqueryUiDirectiveModule', []);

	jqueryUiDirectiveModule.directive('jqueryUiDatePicker', [function() {
		return {
			"restrict" : "A",
			"link"     : function(scope, elem, attrs) {
				elem.attr('readonly', 'readonly');

				elem.datepicker({
					"changeYear"  : true,
					"yearRange"   : "-100:+0",
					"changeMonth" : true,
					"dateFormat"  : "yy-mm-dd"
				});
			}
		};
	}]);
})();