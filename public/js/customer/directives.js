(function() {
	var customerDirectiveModule = angular.module(
		'customerDirectiveModule', 
		[
			'customerServiceModule'
		]
	);

	// IMPORTANT!!! these directives are just for making the update image button work, this part is still for optimization
	// BEGIN
	customerDirectiveModule.directive('customerUpdateImage', [function() {
		return {
			"restrict" : "A",
			"link"     : function(scope, elem, attrs) {
				elem.click(function() {
					// alert('clicked customerUpdateImageDirective directive!');
					
					$('#customer-update-profile-image-modal').modal('setting', {
						"detachable" : true,
						"closable"   : false,
						"onDeny"     : function() {
							scope.$apply(function() {
								console.log('Apply on deny.');
								// should clear currently selected image if there is any
								scope.fileInputImageSelected = '';
							});
							// scope.showUserProfileImageModal = false;
						}
					}).modal('show');
				});
			}
		};
	}]);

	customerDirectiveModule.directive('customerUpdateProfileImageModal', ['customerService', function(customerService) {
		return {
			"restrict" : "A",
			"link"     : function(scope, elem, attrs) {
				elem.modal({
					"detachable" : false,
					"closable"   : false,
					"onDeny"     : function() {
						console.log('onDeny was called');
						// scope.showUserProfileImageModal = false;
						// scope.$apply();
						return false;
					},
					"onVisible"  : function() {
						// $("#profile-image-file").val('');
						scope.uploadPicData.imgData = '';
						scope.$apply();
					},
					"onApprove"  : function() {
						customerService.updateProfileImage({
							"coordinates" : scope.uploadPicData.coordinates,
							"dataImage"   : scope.uploadPicData.imgData,
							"fileType"    : scope.uploadPicData.fileType
						}).then(function(response) {
							if (response.status == 'OK') {
								alert('Profile image successfully updated');
								window.location.reload();
							}
						});
						return false;
					}
				});

				// scope.$watch('showUserProfileImageModal', function(newVal, oldVal) {
				// 	if(newVal !== oldVal) {
				// 		elem.modal('show');
				// 	} else {
				// 		elem.modal('hide');
				// 	}
				// });
			}
		};
	}]);

   customerDirectiveModule.directive('customerUpdateProfileImageFileInput', [function() {
		return {
			"restrict" : "A",
			"scope"    : {
				"fileread"      : "=",
				"uploadPicData" : "=",
				"jCrop"         : "="
			},
			"link"     : function(scope, elem, attrs) {
				scope.uploadPicData = {
					"imgData"     : "",
					"filetype"    : "",
					"coordinates" : {
						"x" : "",
						"y" : "",
						"w" : "",
						"h" : ""
					}
				};

				scope.jCrop = {
					"obj" : {}
				};

				// elem.fileinput('<div class="ui green button">Upload an image</div>');                    
				elem.bind("change", function(changeEvent) {
					var file     = changeEvent.target.files[0];
					var fileType = /image.*/;

					scope.uploadPicData.fileType = file.type;
					scope.$apply();

					if (file.type.match(fileType)) {
						var reader = new FileReader();

						reader.onload = function(loadEvent) {
							/*customerProfileService.postTempProfileImage(reader.result).then(function(data) {
							if (data.status === "success") {
							scope.fileread = data.filename;
							} else {
							window.alert(data.status);
							}
							});*/
							scope.uploadPicData.imgData = reader.result;
							scope.$apply();
							$('#customer-update-profile-image-modal').modal('refresh');
							$("#profile-image").Jcrop({
								onSelect  : function(c) {
									scope.uploadPicData.coordinates.x = c.x;
									scope.uploadPicData.coordinates.y = c.y;
									scope.uploadPicData.coordinates.w = c.w;
									scope.uploadPicData.coordinates.h = c.h;
									scope.$apply();
								},
								setSelect : [
									20,
									20,
									100,
									100
								]
							}, function() {
								scope.jCrop.obj = this;
								scope.$apply();
							});
						};
						reader.readAsDataURL(file);
					} else {
						window.alert('Invalid file format.');
						scope.$apply(function() {
							scope.fileread = '';
						});
					}
				});

				// scope.$watch(
				// 	'fileread', 
				// 	function(newVal, oldVal) {
				// 		if (newVal !== '') {
				// 			$('#croppingContent').fadeIn();
				// 			$('#userProfileImageModal').modal('refresh');
				// 		} else {
				// 			$('#croppingContent').fadeOut();
				// 		}
				// 	}, 
				// 	true
				// );
			}
		};
	}]);
	// END
	// IMPORTANT!!! these directives are just for making the update image button work, this part is still for optimization

	customerDirectiveModule.directive('customerUpdate', ['customerService', function(customerService) {
		return {
			"restrict" : "A",
			"scope"    : {
				"customerForm" : "=",
				"status"       : "=?"
			},
			"link"     : function(scope, elem, attrs) {
				elem.click(function() {
					var error = false;

					scope.customerForm.resetResult();
					scope.customerForm.resetFieldErrors();

					// use the "error" variable as a flag for front end validation
					if (!error) {
						// email is not being updated
						var customer = {
							"title"      : scope.customerForm.fields.title.value,
							"firstName"  : scope.customerForm.fields.firstName.value,
							"lastName"   : scope.customerForm.fields.lastName.value,
							// "email"      : scope.customerForm.fields.email.value,
							"phone"      : scope.customerForm.fields.phone.value,
							"street"     : scope.customerForm.fields.street.value,
							"suburb"     : scope.customerForm.fields.suburb.value,
							"city"       : scope.customerForm.fields.city.value,
							"postCode"   : scope.customerForm.fields.postCode.value,
							"birthDate"  : scope.customerForm.fields.birthDate.value,
							"newsletter" : scope.customerForm.fields.newsletter.value,
							"country"    : scope.customerForm.fields.country.value,
							"timezone"   : scope.customerForm.fields.timezone.value,
						};

						elem.addClass('disabled loading');

						customerService.updateAccount(customer).then(function(response) {
							if (response.status == 'OK') {
								scope.customerForm.result.status = 'success';
							} else {
								scope.customerForm.result.status  = 'error';
								scope.customerForm.result.message = response.status;

								if (response.hasOwnProperty('errors')) {
									for (var attr in response.errors) {
										scope.customerForm.fields[attr].errors = angular.copy(response.errors[attr]);
									}
								}
							}

							elem.removeClass('disabled loading');
						});
					}
				});
			}
		}
	}]);

	customerDirectiveModule.directive('customerCreate', ['customerService', function(customerService) {
		return {
			"restrict" : "A",
			"scope"    : {
				"customerForm" : "=",
				"status"       : "=?"
			},
			"link"     : function(scope, elem, attrs) {
				elem.click(function() {
					var error = false;

					scope.customerForm.resetResult();
					scope.customerForm.resetFieldErrors();

					// use the "error" variable as a flag for front end validation				
					if (!error) {
						var customer = {
							"alias"      : scope.customerForm.fields.alias.value,
							"title"      : scope.customerForm.fields.title.value,
							"firstName"  : scope.customerForm.fields.firstName.value,
							"lastName"   : scope.customerForm.fields.lastName.value,
							"email"      : scope.customerForm.fields.email.value,
							"phone"      : scope.customerForm.fields.phone.value,
							"street"     : scope.customerForm.fields.street.value,
							"suburb"     : scope.customerForm.fields.suburb.value,
							"city"       : scope.customerForm.fields.city.value,
							"postCode"   : scope.customerForm.fields.postCode.value,
							"birthDate"  : scope.customerForm.fields.birthDate.value,
							"newsletter" : scope.customerForm.fields.newsletter.value,
							"country"    : scope.customerForm.fields.country.value,
							"timezone"   : scope.customerForm.fields.timezone.value,
						};

						elem.addClass('disabled loading');

						customerService.createAccount(customer).then(function(response) {
							if (response.status == 'OK') {
								scope.customerForm.result.status = 'success';
							} else {
								scope.customerForm.result.status  = 'error';
								scope.customerForm.result.message = response.status;

								if (response.hasOwnProperty('errors')) {
									for (var attr in response.errors) {
										scope.customerForm.fields[attr].errors = angular.copy(response.errors[attr]);
									}
								}
							}

							elem.removeClass('disabled loading');
						});
					}
				});
			}
		};
	}]);

	customerDirectiveModule.directive('customerValidateAccount', ['customerService', function(customerService) {
		return {
			"restrict" : "A",
			"scope"    : {
				"validationForm" : "=",
				"status"         : "=?"
			},
			"link"     : function(scope, elem, attrs) {
				elem.click(function() {
					var error = false;

					scope.validationForm.resetResult();
					scope.validationForm.resetFieldErrors();

					// use the "error" variable as a flag for front end validation
					if (!error) {
						var validationParams = {
							"email"                : scope.validationForm.fields.email.value,
							"alias"                : scope.validationForm.fields.alias.value,
							"password"             : scope.validationForm.fields.password.value,
							"passwordConfirmation" : scope.validationForm.fields.passwordConfirmation.value
						};

						elem.addClass('disabled loading');

						customerService.validateAccount(validationParams).then(function(response) {
							if (response.status == 'OK') {
								scope.validationForm.result.status = 'success';
							} else {
								scope.validationForm.result.status  = 'error';
								scope.validationForm.result.message = response.status;

								if (response.hasOwnProperty('errors')) {
									for (var attr in response.errors) {
										scope.validationForm.fields[attr].errors = angular.copy(response.errors[attr]);
									}
								}
							}


							elem.removeClass('disabled loading');
						});
					}
				});
			}
		}
	}])
})();