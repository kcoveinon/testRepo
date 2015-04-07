(function() {
	var globalConstantModule = angular.module('globalConstantModule', []);

	globalConstantModule.constant('globalNameTitlesConstant', ['Mr', 'Ms', 'Mrs']);
	
	globalConstantModule.constant(
		'globalTimeZonesConstant', 
		[
			'-12:00',
			'-11:00',
			'-10:00',
			'-09:00',
			'-08:00',
			'-07:00',
			'-06:00',
			'-05:00',
			'-04:00',
			'-03:00',
			'-02:00',
			'-01:00',
			'+00:00',
			'+01:00',
			'+02:00',
			'+03:00',
			'+04:00',
			'+05:00',
			'+06:00',
			'+07:00',
			'+08:00',
			'+09:00',
			'+10:00',
			'+11:00',
			'+12:00'
		]
	);
})();