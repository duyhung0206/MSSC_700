function applyCreditForm(url, id) {
    credit_value = $(id).value;
    var data = {};
    var allShipping = document.getElementsByName("order[shipping_method]");
    for (index = 0; index < allShipping.length; ++index) {
        var shippingElement = allShipping[index];
        if (shippingElement.checked) {
            data['order[shipping_method]'] = shippingElement.value;
        }
    }
    new Ajax.Request(url, {
        method: 'post',
        parameters: {credit_value: credit_value},
        onException: '',
        onComplete: function(response) {
            if (response.responseText.isJSON()) {
                if (order) {
                    var res = response.responseText.evalJSON();
                    order.loadArea(['items', 'shipping_method', 'totals', 'billing_method'], true, data);
//                    $('customercredit_balance').update("" + res.balance);
//                    $('customercredit_input').value = res.credit_value;
                }
            }
        }
    });
}


function checkoutCartCreditAmount(url) {
    var button = $('btn-apply-credit');
    var warning = $('advice-validate-number-customer_credit')
    warning.hide();
    var amount = $('customercredit_input').value;
    if (amount < 0) {
        warning.show();
        $('customercredit_input').value = 0;
    } else {
        button.setAttribute('onclick', url);
    }
}

function sendCreditToFriend(el) {
    if (!el)
        return;
    var receivercredit = $('customercredit-receiver');
    if (el.checked) {
        if (receivercredit) {
            receivercredit.show();
            if ($('recipient_name'))
                $('recipient_name').addClassName('required-entry');
            if ($('recipient_email')) {
                $('recipient_email').addClassName('required-entry');
                $('recipient_email').addClassName('validate-email');
                $('recipient_email').addClassName('validate-same-email');
            }
        }
    } else {
        if (receivercredit)
        {
            if ($('recipient_email')) {
                $('recipient_email').removeClassName('required-entry');
                $('recipient_email').removeClassName('validate-email');
                $('recipient_email').removeClassName('validate-same-email');
            }
            receivercredit.hide();
            if ($('recipient_name'))
                $('recipient_name').removeClassName('required-entry');
        }
    }
}