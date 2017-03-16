function PayScrowHelper()
{
    
}

PayScrowHelper.prototype.getElementValue = function(selector)
{
    var value = '';
    if ($$(selector)[0]) {
        value = $$(selector)[0].value;
    }

    return value;
};

PayScrowHelper.prototype.setElementValue = function(selector, value)
{
    if ($$(selector)[0]) {
        $$(selector)[0].value = value;
    }
};

PayScrowHelper.prototype.getShortCode = function()
{
    var methods = {
        payScrow_creditcard: "cc",
        payScrow_directdebit: 'elv'
    };

    if (payment.currentMethod in methods) {
        return methods[payment.currentMethod];
    }

    return 'other';
};

PayScrowHelper.prototype.getMethodCode = function()
{
    return payment.currentMethod;
};