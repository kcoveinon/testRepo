(function() {
	var globalProviderModule = angular.module('globalProviderModule', []);

	globalProviderModule.provider('globalData', [function() {
		 var _symbols = {
            "start" : "<%",
            "end"   : "%>"
        };

        this.$get = function() {
        };

        this.getSymbols = function() {
            return _symbols;
        };

        this.setSymbols = function(symbols) {
        	for (var attr in symbols) {
        		if (symbols.hasOwnProperty(attr)) {
        			_symbols[attr] = symbols[attr];
        		}
        	}
        };
	}]);
})();