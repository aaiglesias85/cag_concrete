var ForgotPasss = function () {
    // Elements
    var form;
    var submitButton;
    var validator;

    // Handle form
    var handleValidation = function (e) {
        // Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
        validator = FormValidation.formValidation(
            form,
            {
                fields: {
                    'email': {
                        validators: {
                            regexp: {
                                regexp: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                                message: 'The value is not a valid email address',
                            },
                            notEmpty: {
                                message: 'Email address is required'
                            }
                        }
                    },
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '.fv-row',
                        eleInvalidClass: '',  // comment to enable invalid state icons
                        eleValidClass: '' // comment to enable valid state icons
                    })
                }
            }
        );
    }

    var handleSubmitAjax = function (e) {
        // Handle form submit
        submitButton.addEventListener('click', function (e) {
            // Prevent button default action
            e.preventDefault();

            // Validate form
            validator.validate().then(function (status) {
                if (status == 'Valid') {
                    // Show loading indication
                    submitButton.setAttribute('data-kt-indicator', 'on');

                    // Disable button to avoid multiple click
                    submitButton.disabled = true;

                    var formData = new FormData();

                    var email = document.querySelector('#email').value;
                    formData.set('email', email);


                    // axios
                    var url = submitButton.closest('form').getAttribute('data-action');
                    axios
                        .post(url, formData, {
                            responseType: "json",
                        })
                        .then(function (res) {
                            if (res.status === 200 || res.status === 201) {
                                var response = res.data;
                                if (response.success) {
                                    toastr.success(response.message, "Success");

                                    setTimeout(function () {
                                        location.href = submitButton.closest('form').getAttribute('data-url-login');
                                    }, 3000);


                                } else {
                                    toastr.error('Sorry, the email is incorrect, please try again.', "Error");
                                }
                            } else {
                                toastr.error("Sorry, looks like there are some errors detected, please try again", "Error");
                            }
                        })
                        .catch(MyUtil.catchErrorAxios)
                        .then(function () {
                            // Hide loading indication
                            submitButton.removeAttribute('data-kt-indicator');

                            // Enable button
                            submitButton.disabled = false;
                        });
                    
                } else {
                    toastr.error("Sorry, looks like there are some errors detected, please try again.", "Error");
                }
            });
        });
    }

    var isValidUrl = function(url) {
        try {
            new URL(url);
            return true;
        } catch (e) {
            return false;
        }
    }

    // Public functions
    return {
        // Initialization
        init: function () {
            form = document.querySelector('#kt_password_reset_form');
            submitButton = document.querySelector('#kt_password_reset_submit');

            handleValidation();

            if (isValidUrl(submitButton.closest('form').getAttribute('data-action'))) {
                handleSubmitAjax(); // use for ajax submit
            }
        }
    };
}();