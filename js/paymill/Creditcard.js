function Creditcard()
{
    this.helper = new PayScrowHelper();
}

Creditcard.prototype.validate = function()
{
    var valid = true;
    if (this.helper.getElementValue('.payScrow-info-fastCheckout-cc') === 'false') {
        if (!payScrow.validateCvc(this.helper.getElementValue('#payScrow_creditcard_cvc'))) {
            if (payScrow.cardType(this.helper.getElementValue('#payScrow_creditcard_number')).toLowerCase() !== 'maestro') {
                valid = false;
            }
        }

        if (!payScrow.validateHolder(this.helper.getElementValue('#payScrow_creditcard_holdername'))) {
            valid = false;
        }

        if (!payScrow.validateExpiry(
                this.helper.getElementValue('#payScrow_creditcard_expiry_month'),
                this.helper.getElementValue('#payScrow_creditcard_expiry_year'))) {
            valid = false;
        }

        if (!payScrow.validateCardNumber(this.helper.getElementValue('#payScrow_creditcard_number'))) {
            valid = false;
        }
    }

    return valid;
};

Creditcard.prototype.setValidationRules = function()
{
    var that = this;

    Object.extend(Validation.methods, {
        'payScrow-validate-cc-number': new Validator(
            'payScrow-validate-cc-number',
            this.helper.getElementValue('.payScrow-payment-error-number'),
            function(value) {
                return payScrow.validateCardNumber(value);
            },
            ''
        ), 'payScrow-validate-cc-expdate-month': new Validator(
            'payScrow-validate-cc-expdate-month',
            this.helper.getElementValue('.payScrow-payment-error-expdate'),
            function(value) {
                return payScrow.validateExpiry(value, that.helper.getElementValue('.payScrow-validate-cc-expdate-year'));
            },
            ''
        ), 'payScrow-validate-cc-expdate-year': new Validator(
                'payScrow-validate-cc-expdate-year',
                this.helper.getElementValue('.payScrow-payment-error-expdate'),
                function(value) {
                    return payScrow.validateExpiry(that.helper.getElementValue('.payScrow-validate-cc-expdate-month'), value);
                },
                ''
        ), 'payScrow-validate-cc-holder': new Validator(
            'payScrow-validate-cc-holder',
            this.helper.getElementValue('.payScrow-payment-error-holder'),
            function(value) {
                return (payScrow.validateHolder(value));
            },
            ''
        ), 'payScrow-validate-cc-cvc': new Validator(
            'payScrow-validate-cc-cvc',
            this.helper.getElementValue('.payScrow-payment-error-cvc'),
            function(value) {
                if (payScrow.cardType(that.helper.getElementValue('#payScrow_creditcard_number')).toLowerCase() === 'maestro') {
                    return true;
                }

                return payScrow.validateCvc(value);
            },
            ''
        )
    });
};

Creditcard.prototype.unsetValidationRules = function()
{
    Object.extend(Validation.methods, {
        'payScrow-validate-cc-number': new Validator(
            'payScrow-validate-cc-number',
            '',
            function(value) {
                return true;
            },
            ''
        ), 'payScrow-validate-cc-expdate-month': new Validator(
            'payScrow-validate-cc-expdate-month',
            '',
            function(value) {
                return true;
            },
            ''
        ), 'payScrow-validate-cc-expdate-year': new Validator(
            'payScrow-validate-cc-expdate-year',
            '',
            function(value) {
                return true;
            },
            ''
        ), 'payScrow-validate-cc-holder': new Validator(
            'payScrow-validate-cc-holder',
            '',
            function(value) {
                return true;
            },
            ''
        ), 'payScrow-validate-cc-cvc': new Validator(
            'payScrow-validate-cc-cvc',
            '',
            function(value) {
                return true;
            },
            ''
        )
    });
};

Creditcard.prototype.getTokenParameter = function()
{
    PAYScrow_PUBLIC_KEY = this.helper.getElementValue('.payScrow-info-public_key-cc');
    payScrow.config('3ds_cancel_label', this.helper.getElementValue('.payScrow_3ds_cancel'));

    var cvc = '000';

    if (this.helper.getElementValue('#payScrow_creditcard_cvc') !== '') {
        cvc = this.helper.getElementValue('#payScrow_creditcard_cvc');
    }

    return {
        amount_int: parseInt(this.getTokenAmount()),
        currency: this.helper.getElementValue('.payScrow-payment-currency-cc'),
        number: this.helper.getElementValue('#payScrow_creditcard_number'),
        exp_month: this.helper.getElementValue('#payScrow_creditcard_expiry_month'),
        exp_year: this.helper.getElementValue('#payScrow_creditcard_expiry_year'),
        cvc: cvc,
        cardholder: this.helper.getElementValue('#payScrow_creditcard_holdername'),
        email: this.helper.getElementValue('.payScrow-payment-customer-email-cc')
    };
};

Creditcard.prototype.getFrameTokenParameter = function()
{
    PAYScrow_PUBLIC_KEY = this.helper.getElementValue('.payScrow-info-public_key-cc');

    return {
        amount_int: parseInt(this.getTokenAmount()),
        currency: this.helper.getElementValue('.payScrow-payment-currency-cc'),
        email: this.helper.getElementValue('.payScrow-payment-customer-email-cc')
    };
};


Creditcard.prototype.getTokenAmount = function()
{
    var that = this;
    var returnVal = null;

    new Ajax.Request(this.helper.getElementValue('.payScrow-payment-token-url-cc'), {
        asynchronous: false,
        onSuccess: function(response) {
            returnVal = response.transport.responseText;
        }, onFailure: function() {
            Object.extend(Validation.methods, {
                'payScrow-validate-cc-token': new Validator(
                    'payScrow-validate-cc-token',
                    that.helper.getElementValue('.payScrow-payment-error-cc-token') + " Amount not accessable.",
                    function(value) {
                        return value !== '';
                    },
                ''
                )
            });
        }

    });

    return returnVal;
};

Creditcard.prototype.payScrowShowCardIcon = function()
{
    var detector = new PayScrowBrandDetection();
    var brand = detector.detect(this.helper.getElementValue('#payScrow_creditcard_number'));
    brand = brand.toLowerCase();
    $$('#payScrow_creditcard_number')[0].className = $$('#payScrow_creditcard_number')[0].className.replace(/payScrow-card-number-.*/g, '');
    if (brand !== 'unknown') {
        if(this.creditcards.length > 0 && this.creditcards.indexOf(brand) === -1) {
            return;
        }

        $$('#payScrow_creditcard_number')[0].addClassName("payScrow-card-number-" + brand);
        if (!detector.validate(this.helper.getElementValue('#payScrow_creditcard_number'))) {
            $$('#payScrow_creditcard_number')[0].addClassName("payScrow-card-number-grayscale");
        }
    }
};

Creditcard.prototype.setEventListener = function(selector)
{
    var that = this;

    if (this.helper.getElementValue('.payScrow-info-fastCheckout-cc') === 'true') {
        that.unsetValidationRules();
    }

    Event.observe('payScrow_creditcard_number','keyup', function() {
        that.setValidationRules();
        that.helper.setElementValue('.payScrow-info-fastCheckout-cc', 'false');
        if (!$$(selector)[0]) {
            payScrowCreditcard.generateToken();
        } else {
            payScrowCreditcard.setOnClickHandler(selector);
        }
    });

    Event.observe('payScrow_creditcard_cvc', 'keyup', function() {
        that.setValidationRules();
        that.helper.setElementValue('.payScrow-info-fastCheckout-cc', 'false');
        if (!$$(selector)[0]) {
            payScrowCreditcard.generateToken();
        } else {
            payScrowCreditcard.setOnClickHandler(selector);
        }
    });

    Event.observe('payScrow_creditcard_expiry_month', 'change', function() {
        that.setValidationRules();
        that.helper.setElementValue('.payScrow-info-fastCheckout-cc', 'false');
        if (!$$(selector)[0]) {
            payScrowCreditcard.generateToken();
        } else {
            payScrowCreditcard.setOnClickHandler(selector);
        }
    });

    Event.observe('payScrow_creditcard_expiry_year', 'change', function() {
        that.setValidationRules();
        that.helper.setElementValue('.payScrow-info-fastCheckout-cc', 'false');
        if (!$$(selector)[0]) {
            payScrowCreditcard.generateToken();
        } else {
            payScrowCreditcard.setOnClickHandler(selector);
        }
    });

    Event.observe('payScrow_creditcard_holdername', 'keyup', function() {
        that.setValidationRules();
        that.helper.setElementValue('.payScrow-info-fastCheckout-cc', 'false');
        if (!$$(selector)[0]) {
            payScrowCreditcard.generateToken();
        } else {
            payScrowCreditcard.setOnClickHandler(selector);
        }
    });

    Event.observe('payScrow_creditcard_number', 'keyup', function() {
        that.payScrowShowCardIcon();
    });

};

Creditcard.prototype.setCreditcards = function(creditcards)
{
    this.creditcards = creditcards;
};

Creditcard.prototype.openPayScrowFrame = function(lang)
{
    $$('#payScrowFastCheckoutDiv')[0].parentNode.removeChild($$('#payScrowFastCheckoutDiv')[0]);
    payScrow.embedFrame('payScrowContainer', {lang: lang}, PayScrowFrameResponseHandler);
    this.helper.setElementValue('.payScrow-info-fastCheckout-cc', 'false');
};

PayScrowFrameResponseHandler = function(error)
{
    if (error) {
        payScrowCreditcard.debug("iFrame load failed with " + error.apierror + error.message);
    } else {
        payScrowCreditcard.debug("iFrame successfully loaded");
        payScrowCreditcard.setOnClickHandler(payScrowTokenSelector);
    }
}
