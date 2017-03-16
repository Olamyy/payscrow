if (typeof Array.prototype.forEach !== 'function') {
    Array.prototype.forEach = function (callback, context) {
        for (var i = 0; i < this.length; i++) {
            callback.apply(context, [this[i], i, this]);
        }
    };
}

var PAYScrow_PUBLIC_KEY = null;
var payScrowButton = false;
var onClickContent = false;
var onClickBounded = false;
var payScrowUseButton = false;
var payScrowUseButtonForFrame = false;

function PayScrow(methodCode)
{
    this.methodInstance = null;
    this.methodCode = methodCode;
    if (methodCode === 'payScrow_creditcard') {
        this.methodInstance = new Creditcard();
    }

    if (methodCode === 'payScrow_directdebit') {
        this.methodInstance = new Elv();
    }

    this.helper = new PayScrowHelper();
}

PayScrow.prototype.validate = function ()
{
    this.debug("Start form validation");
    var valid = this.methodInstance.validate();
    this.debug(valid);
    return valid;
};

PayScrow.prototype.generateToken = function ()
{
    if (this.validate()) {
        if (this.helper.getMethodCode() === 'payScrow_creditcard') {
            new Validation($$('#payScrow_creditcard_cvc')[0].form.id).validate();
        }

        if (this.helper.getMethodCode() === 'payScrow_directdebit') {
            new Validation($$('#payScrow_directdebit_holdername')[0].form.id).validate();
        }

        var data = this.methodInstance.getTokenParameter();
        this.debug("Generating Token");
        this.debug(data);
        payScrow.createToken(
            data,
            tokenCallback
        );
    }
};

PayScrow.prototype.generateTokenOnSubmit = function ()
{
    if (this.helper.getElementValue('.payScrow-info-fastCheckout-' + this.helper.getShortCode()) !== 'true') {

        if (this.helper.getMethodCode() === 'payScrow_creditcard') {
            if (this.helper.getElementValue('.payScrow-info-pci-' + this.helper.getShortCode()) === 'SAQ A') {
                var data = this.methodInstance.getFrameTokenParameter();
                this.debug("Generating Token");
                this.debug(data);
                payScrow.createTokenViaFrame(data, tokenCallback);
            } else if (new Validation($$('#payScrow_creditcard_cvc')[0].form.id).validate()) {
                this.generateToken();
            }
        }

        if (this.helper.getMethodCode() === 'payScrow_directdebit') {
            if (new Validation($$('#payScrow_directdebit_holdername')[0].form.id).validate()) {
                this.generateToken();
            }
        }
    } else {
        payScrowDebitUseButton = this.helper.getMethodCode() === 'payScrow_directdebit' && payScrowUseButton;
        payScrowCcUseButton = this.helper.getMethodCode() === 'payScrow_creditcard' && (payScrowUseButton || payScrowUseButtonForFrame);
        if (payScrowButton && (payScrowDebitUseButton || payScrowCcUseButton)) {
            payScrowButton.removeAttribute('onclick');
            payScrowButton.stopObserving('click');
            payScrowButton.setAttribute('onclick', onClickContent);
            if (onClickBounded) {
                onClickBounded.forEach(function (handler) {
                    payScrowButton.observe('click', handler);
                });
            }

            payScrowButton.click();
            payScrowButton.removeAttribute('onclick');
            payScrowButton.stopObserving('click');
            if (this.helper.getMethodCode() === 'payScrow_directdebit') {
                payScrowButton.setAttribute('onclick', 'payScrowElv.generateTokenOnSubmit()');
            }

            if (this.helper.getMethodCode() === 'payScrow_creditcard') {
                payScrowButton.setAttribute('onclick', 'payScrowCreditcard.generateTokenOnSubmit()');
            }
        }
    }
};

PayScrow.prototype.setValidationRules = function ()
{
    this.methodInstance.setValidationRules();
};

PayScrow.prototype.logError = function (data)
{
    var that = this;
    new Ajax.Request(this.helper.getElementValue('.payScrow-payment-token-log-' + this.helper.getShortCode()), {
        method: 'post',
        parameters: data,
        onSuccess: function (response) {
            that.debug('Logging done.');
        }, onFailure: function () {
            that.debug('Logging failed.');
        }
    });
};

PayScrow.prototype.debug = function (message)
{
    if (this.helper.getElementValue('.payScrow-option-debug-' + this.helper.getShortCode()) === "1") {
        console.log(message);
    }
};

PayScrow.prototype.setEventListener = function (selector)
{
    this.methodInstance.setEventListener(selector);
    this.setOnClickHandler(selector);

};

PayScrow.prototype.setOnClickHandler = function (selector)
{
    var that = this;
    if (!payScrowButton) {
        if ($$(selector)[0]) {
            payScrowButton = $$(selector)[0];
            payScrowUseButton = true;
        } else if (typeof (payScrowPci) !== 'undefined' && payScrowPci === 'SAQ A') {
            if ($$('#onestepcheckout-place-order')[0]) {
                payScrowButton = $$('#onestepcheckout-place-order')[0];
            } else if ($$('#firecheckout-form button[onclick*="checkout.save()"]')[0]) {
                payScrowButton = $$('#firecheckout-form button[onclick*="checkout.save()"]')[0];
            } else if ($$('#onestepcheckout-form')[0]) {
                payScrowButton = $$('#onestepcheckout-form button[onclick*="review.save()"]')[0];
            } else {
                payScrowButton = $$('button[onclick*="payment.save()"]')[0];
            }
            payScrowUseButtonForFrame = true;
        }
    }

    if (payScrowButton) {
        if (!onClickContent) {
            onClickContent = payScrowButton.getAttribute('onclick');
            if (payScrowButton.getStorage()._object.prototype_event_registry) {
                onClickBounded = payScrowButton.getStorage()._object.prototype_event_registry._object.click;
            }
        }
        
        payment.switchMethod = payment.switchMethod.wrap(function (originalSwitchMethod, method) {
            payment.originalSwitchMethod = originalSwitchMethod;
            
            originalSwitchMethod(method);
            
            payScrowButton.removeAttribute('onclick');
            payScrowButton.stopObserving('click');
            
            if (that.helper.getMethodCode() === 'payScrow_directdebit' && payScrowUseButton) {
                payScrowButton.setAttribute('onclick', 'payScrowElv.generateTokenOnSubmit()');
            } else if (that.helper.getMethodCode() === 'payScrow_creditcard' && (payScrowUseButton || payScrowUseButtonForFrame)) {
                payScrowButton.setAttribute('onclick', 'payScrowCreditcard.generateTokenOnSubmit()');
            } else {
                payScrowButton.setAttribute('onclick', onClickContent);
                if (onClickBounded) {
                    onClickBounded.forEach(function (handler) {
                        payScrowButton.observe('click', handler);
                    });
                }
            }

            
        });

        if (that.helper.getMethodCode() === 'payScrow_directdebit' && payScrowUseButton) {
            payScrowButton.stopObserving('click');
            payScrowButton.removeAttribute('onclick');
            payScrowButton.setAttribute('onclick', 'payScrowElv.generateTokenOnSubmit()');
        }

        if (that.helper.getMethodCode() === 'payScrow_creditcard' && (payScrowUseButton || payScrowUseButtonForFrame)) {
            payScrowButton.stopObserving('click');
            payScrowButton.removeAttribute('onclick');
            payScrowButton.setAttribute('onclick', 'payScrowCreditcard.generateTokenOnSubmit()');
        }
    }
};

PayScrow.prototype.setCreditcards = function (creditcards)
{
    this.methodInstance.creditcards = creditcards;
};

tokenCallback = function (error, result)
{
    var payScrow = new PayScrow('default');

    payScrow.debug("Enter payScrowResponseHandler");

    var rules = {};
    if (error) {
        var message = 'unknown_error';
        var key = error.apierror;
        if (payScrow.helper.getElementValue('.PAYScrow_' + key + '-' + payScrow.helper.getShortCode()) !== '') {
            message = payScrow.helper.getElementValue('.PAYScrow_' + key + '-' + payScrow.helper.getShortCode());
        }

        if (message === 'unknown_error' && error.message !== undefined) {
            message = error.message;
        }


        // Appending error
        rules['payScrow-validate-' + payScrow.helper.getShortCode() + '-token'] = new Validator(
            'payScrow-validate-' + payScrow.helper.getShortCode() + '-token',
            payScrow.helper.getElementValue('.payScrow-payment-error-' + payScrow.helper.getShortCode() + '-token') + ' ' + message,
            function (value) {
                return false;
            },
            ''
        );

        payScrow.helper.setElementValue('#payScrow_creditcard_cvc', '');
        payScrow.logError(error);
        payScrow.debug(error.apierror);
        payScrow.debug(error.message);
        payScrow.debug("PayScrow Response Handler triggered: Error.");
        Object.extend(Validation.methods, rules);
        if (!payScrowUseButtonForFrame) {
            new Validation($$('.payScrow-payment-token-' + payScrow.helper.getShortCode())[0].form.id).validate();
        }
    } else {
        rules['payScrow-validate-' + payScrow.helper.getShortCode() + '-token'] = new Validator(
            'payScrow-validate-' + payScrow.helper.getShortCode() + '-token',
            '',
            function (value) {
                return true;
            },
            ''
        );

        Object.extend(Validation.methods, rules);

        payScrow.debug("Saving Token in Form: " + result.token);
        payScrow.helper.setElementValue('.payScrow-payment-token-' + payScrow.helper.getShortCode(), result.token);

        payScrowDebitUseButton = payScrow.helper.getMethodCode() === 'payScrow_directdebit' && payScrowUseButton;
        payScrowCcUseButton = payScrow.helper.getMethodCode() === 'payScrow_creditcard' && (payScrowUseButton || payScrowUseButtonForFrame);
        if (payScrowButton && (payScrowDebitUseButton || payScrowCcUseButton)) {
            payScrowButton.removeAttribute('onclick');
            payScrowButton.stopObserving('click');
            payScrowButton.setAttribute('onclick', onClickContent);
            if (onClickBounded) {
                onClickBounded.forEach(function (handler) {
                    payScrowButton.observe('click', handler);
                });
            }

            payScrowButton.click();

            payScrowButton.stopObserving('click');
            payScrowButton.removeAttribute('onclick');

            if (payScrow.helper.getMethodCode() === 'payScrow_directdebit') {
                payScrowButton.setAttribute('onclick', 'payScrowElv.generateTokenOnSubmit()');
            }

            if (payScrow.helper.getMethodCode() === 'payScrow_creditcard') {
                payScrowButton.setAttribute('onclick', 'payScrowCreditcard.generateTokenOnSubmit()');
            }
        }

    }
};
