CRM.$(function($) {
    $('body').on('click','.myoberror-info',function(e){
        e.preventDefault();

        $.getJSON(CRM.url("civicrm/ajax/civimyob/sync/contact/errors",
            {myoberrorid: $(this).data('myoberrorid')})
        ).done(function (result) {
            if((typeof result) == "object") {
                result = getArrayFromObject(result);
            }
            if(result.length > 0) {
                CRM.alert(getErrorsText(result),"Contact sync","error");
            }
        });
    });

    $('body').on('click','.myoberror-invoice-info',function(e){
        e.preventDefault();

        $.getJSON(CRM.url("civicrm/ajax/civimyob/sync/invoice/errors",
            {myoberrorid: $(this).data('myoberrorid')})
        ).done(function (result) {
            if((typeof result) == "object") {
                result = getArrayFromObject(result);
            }
            if(result.length > 0) {
                CRM.alert(getErrorsText(result),"Contributions sync","error");
            }
        });
    });

    function getArrayFromObject(object) {
        var array = $.map(object, function(value, index) {
          return [value];
        });
        return array;
    }

    function getErrorsText(result) {
        var text ="";
        for(var i=0; i<result.length; i++) {
            text += "<br>";
            text += result[i];
            if(i != (result.length-1)) {
                text += "<br>";
            }
        }
        return text;
    }
});
