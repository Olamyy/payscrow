function Elv()
{
    this.helper = new PayScrowHelper();
}

Elv.prototype.validate = function()
{
    var valid = true;
    
    if (this.helper.getElementValue('.payScrow-info-fastCheckout-elv') === 'false') {
        if (this.helper.getElementValue('#payScrow_directdebit_holdername') === '') {
            valid = false;
        }

        if (this.isSepa()) {
            ibanValidator = new PayScrowIban();

            if (!ibanValidator.validate(this.helper.getElementValue('#payScrow_directdebit_account_iban'))) {
                valid = false;
            }

            if (this.helper.getElementValue('#payScrow_directdebit_bankcode_bic').length !== 8
                    && this.helper.getElementValue('#payScrow_directdebit_bankcode_bic').length !== 11) {
                valid = false;
            }
        } else {
            if (!payScrow.validateAccountNumber(this.helper.getElementValue('#payScrow_directdebit_account_iban'))) {
                valid = false;
            }

            if (!payScrow.validateBankCode(this.helper.getElementValue('#payScrow_directdebit_bankcode_bic'))) {
                valid = false;
            }
        }
    }
            
    return valid;
};

Elv.prototype.unsetValidationRules = function()
{
    Object.extend(Validation.methods, {
        'payScrow-validate-dd-holdername': new Validator(
            'payScrow-validate-dd-holdername',
            '',
            function(value) {
                return true;
            },
            ''
        ), 'payScrow-validate-dd-account-iban': new Validator(
            'payScrow-validate-dd-account-iban',
            '',
            function(value) {
                return true;
            },
            ''
        ), 'payScrow-validate-dd-bankcode-bic': new Validator(
            'payScrow-validate-dd-bankcode-bic',
            '',
            function(value) {
                return true;
            },
            ''
        )
    });
};

Elv.prototype.setValidationRules = function ()
{
    var that = this;
    Object.extend(Validation.methods, {
        'payScrow-validate-dd-holdername': new Validator(
            'payScrow-validate-dd-holdername',
            this.helper.getElementValue('.payScrow-payment-error-holder-elv'),
            function(value) {
                return !(value === '');
            },
            ''
        ), 'payScrow-validate-dd-account-iban': new Validator(
            'payScrow-validate-dd-account-iban',
            this.helper.getElementValue('.payScrow-payment-error-number-iban-elv'),
            function(value) {
                if (that.isSepa()) {
                    iban = new PayScrowIban();
                    return iban.validate(value);
                }
                return payScrow.validateAccountNumber(value);
            },
            ''
        ), 'payScrow-validate-dd-bankcode-bic': new Validator(
            'payScrow-validate-dd-bankcode-bic',
            this.helper.getElementValue('.payScrow-payment-error-bankcode-bic-elv'),
            function(value) {
                if (that.isSepa()) {
                    return value.length === 8 || value.length === 11;
                }
                
                return payScrow.validateBankCode(value);
            },
            ''
        )
    });
};

Elv.prototype.getTokenParameter = function()
{
    PAYScrow_PUBLIC_KEY = this.helper.getElementValue('.payScrow-info-public_key-elv');
    
    var data = null;
    
    if (!this.isSepa()) {
        data = {
            number: this.helper.getElementValue('#payScrow_directdebit_account_iban'),
            bank: this.helper.getElementValue('#payScrow_directdebit_bankcode_bic'),
            accountholder: this.helper.getElementValue('#payScrow_directdebit_holdername')
        };
    } else {
        data = {
            iban: this.helper.getElementValue('#payScrow_directdebit_account_iban').replace(/\s+/g, ''),
            bic: this.helper.getElementValue('#payScrow_directdebit_bankcode_bic'),
            accountholder: this.helper.getElementValue('#payScrow_directdebit_holdername')
        };
    }
    
    return data;
};

Elv.prototype.isSepa = function()
{
    var reg = new RegExp(/^\D{2}/);
    return reg.test(this.helper.getElementValue('#payScrow_directdebit_account_iban'));
};

Elv.prototype.setEventListener = function(selector)
{
    var that = this;
    
    if (this.helper.getElementValue('.payScrow-info-fastCheckout-elv') === 'true') {
        that.unsetValidationRules();
    }
    
    Event.observe('payScrow_directdebit_holdername', 'keyup', function() {
        that.setValidationRules();
        that.helper.setElementValue('.payScrow-info-fastCheckout-elv', 'false');
        if (!$$(selector)[0]) {
            payScrowElv.generateToken();
        } else {
            payScrowElv.setOnClickHandler(selector);
        }
    });

    Event.observe('payScrow_directdebit_account_iban', 'keyup', function() {
        that.setValidationRules();
        that.helper.setElementValue('.payScrow-info-fastCheckout-elv', 'false');
        if (!$$(selector)[0]) {
            payScrowElv.generateToken();
        } else {
            payScrowElv.setOnClickHandler(selector);
        }
    });

    Event.observe('payScrow_directdebit_bankcode_bic', 'keyup', function() {
        that.setValidationRules();
        that.helper.setElementValue('.payScrow-info-fastCheckout-elv', 'false');
        if (!$$(selector)[0]) {
            payScrowElv.generateToken();
        } else {
            payScrowElv.setOnClickHandler(selector);
        }
    });
};