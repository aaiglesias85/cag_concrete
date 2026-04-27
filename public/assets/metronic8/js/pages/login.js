var Login = function () {
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
                    'password': {
                        validators: {
                            notEmpty: {
                                message: 'The password is required'
                            }
                        }
                    }
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

                    var password = document.querySelector('#password').value;
                    formData.set('password', password);


                    // axios — 429 y otros cuerpos JSON: mostrar `error` del backend (Admin en inglés)
                    var url = submitButton.closest('form').getAttribute('data-action');
                    var defaultLoginError = 'Sorry, the email or password is incorrect, please try again.';
                    var backendMessage = function (payload) {
                        if (payload && typeof payload === 'object') {
                            return payload.error || payload.message || null;
                        }
                        return null;
                    };
                    axios
                        .post(url, formData, {
                            responseType: "json",
                            validateStatus: function (status) {
                                return status >= 200 && status < 600;
                            },
                        })
                        .then(function (res) {
                            var response = res.data;
                            if (res.status >= 200 && res.status < 300 && response && response.success) {
                                form.reset();
                                location.href = response.url;
                                return;
                            }
                            var msg = backendMessage(response) || defaultLoginError;
                            toastr.error(msg, "Error");
                        })
                        .catch(function (err) {
                            console.log(err);
                            var msg = backendMessage(err && err.response ? err.response.data : null) || defaultLoginError;
                            toastr.error(msg, "Error");
                        })
                        .then(function () {
                            // Hide loading indication
                            submitButton.removeAttribute('data-kt-indicator');

                            // Enable button
                            submitButton.disabled = false;
                        });
                    
                } else {
                    toastr.error("Please enter a valid email and password.", "Error");
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
            form = document.querySelector('#kt_sign_in_form');
            submitButton = document.querySelector('#kt_sign_in_submit');

            handleValidation();

            if (isValidUrl(submitButton.closest('form').getAttribute('data-action'))) {
                handleSubmitAjax(); // use for ajax submit
            }
        }
    };
}();