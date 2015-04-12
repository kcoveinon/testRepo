(function() {
	var globalFactoryModule = angular.module('globalFactoryModule', []);

	globalFactoryModule.factory('Form', [function() {
		function Form(fields) {
			this.result = {
				"status"  : "",
				"message" : ""
			};

			this.fields = [];

			for (var i = 0; i < fields.length; i++) {
				this.fields[fields[i]] = {
					"value"  : "",
					"errors" : []
				};
			}
		}

		Form.prototype.setResult = function(result) {
			for (var attr in result) {
				if (result.hasOwnProperty(attr)) {
					this.result[attr] = result[attr];
				}
			}
		};

		Form.prototype.setFields = function(fields) {
			for (var attr in fields) {
				if (fields.hasOwnProperty(attr)) {
					this.fields[attr] = fields[attr];
				}
			}
		};

		Form.prototype.resetFieldErrors = function() {
			for (var attr in this.fields) {
				if (this.fields.hasOwnProperty(attr)) {
					this.fields[attr].errors = [];
				}
			}
		};

		Form.prototype.resetResult = function() {
			for (var attr in this.result) {
				if (this.result.hasOwnProperty(attr)) {
					this.result[attr] = '';
				}
			}
		};

		return Form;
	}]);

	globalFactoryModule.factory('Model', [function() {
		function Model() {

		}

		Model.prototype.setAttributes = function(attributes) {
			for (var attr in attributes) {
				if (attributes.hasOwnProperty(attr)) {
					this[attr] = attributes[attr];
				}
			}
		};

		return Model;
	}]);
})();