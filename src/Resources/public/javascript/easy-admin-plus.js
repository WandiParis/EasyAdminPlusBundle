$(function(){
    var date=new Date();
    $('.datepicker').datepicker({ changeMonth: true, changeYear: true });
    $('.datepicker-future').datepicker({minDate: 'now', changeMonth: true, changeYear: true});
    $('.datepicker-past').datepicker({maxDate: 'now', changeMonth: true, changeYear: true});
    $('.datetimepicker').datetimepicker();
});

//function to remove query params form a url
function removeURLParameter(url, parameter) {
    //prefer to use l.search if you have a location/link object
    var urlparts= url.split('?');   
    if (urlparts.length>=2) {

        var prefix= encodeURIComponent(parameter)+'=';
        var pars= urlparts[1].split(/[&;]/g);

        //reverse iteration as may be destructive
        for (var i= pars.length; i-- > 0;) {    
            //idiom for string.startsWith
            if (pars[i].lastIndexOf(prefix, 0) !== -1) {  
                pars.splice(i, 1);
            }
        }

        url= urlparts[0] + (pars.length > 0 ? '?' + pars.join('&') : "");
        return url;
    } else {
        return url;
    }
}

function insertParam(key, value) {
    if (history.pushState) {
        // var newurl = window.location.protocol + "//" + window.location.host + search.pathname + '?myNewUrlQuery=1';
        var currentUrl = window.location.href;
        //remove any param for the same key
        var currentUrl = removeURLParameter(currentUrl, key);

        //figure out if we need to add the param with a ? or a &
        var queryStart;
        if(currentUrl.indexOf('?') !== -1){
            queryStart = '&';
        } else {
            queryStart = '?';
        }

        var newurl = currentUrl + queryStart + key + '=' + value
        window.history.pushState({path:newurl},'',newurl);
    }
}
// Overwrites the original easyadmin function.
function createAutoCompleteFields() {
    var autocompleteFields = $('[data-easyadmin-autocomplete-url]');

    autocompleteFields.each(function () {
        var $this = $(this),
            url = $this.data('easyadmin-autocomplete-url');

        $this.select2({
            theme: 'bootstrap',
            ajax: {
                url: url,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { 'query': params.term, 'page': params.page };
                },
                // to indicate that infinite scrolling can be used
                processResults: function (data, params) {
                    return {
                        results: data.results,
                        pagination: {
                            more: data.has_next_page
                        }
                    };
                },
                cache: true
            },
            placeholder: '',
            allowClear: true
        });
    });
}

$(function() {
    $('body').on('click', '.eap-edit-in-place', function () {
        $(this).hide();
        $('#' + $(this).attr('data-span-in-id')).show();
        $('#' + $(this).attr('data-input-id')).focus();
        if ($(this).attr('data-callback') && window[$(this).attr('data-callback')]) {
            window[$(this).attr('data-callback')]();
        }
        $("select.select2").select2();
    });

    $('body').on('keypress', '.eap-edit-in-place-input', function(evt){
        if(evt.keyCode == 13){
            $(this).parent().find('.eap-edit-in-place-ok').click();
        }
    });

    $('body').on('click','.eap-edit-in-place-close',function(){
        var span = $('#' + $(this).attr('data-span-id'));
        var span_in = $('#'+$(this).attr('data-span-in-id'));
        span.show();
        span_in.hide();
    });

    $('body').on('click','.eap-edit-in-place-eraser',function(){
        $('#'+$(this).attr('data-input-id')).val(null);
        $(this).siblings('.eap-edit-in-place-ok').click();
    });

    $('body').on('click','.eap-edit-in-place-ok',function(){
        var elm = $(this);
        elm.html('<i class="fa fa-spinner"></i>');
        var id = $(this).attr('data-item-id');
        var callback = $(this).attr('data-callback');
        var fieldName = $(this).attr('data-field-name');
        var cls = $(this).attr('data-class');
        var reload = ($(this).attr('data-reload'))? $(this).attr('data-reload'):'value';
        var line = ($(this).attr('data-line'))? $(this).attr('data-line'):null;
        var type = ($(this).attr('data-type'))? $(this).attr('data-type'):'string';
        var field= ($(this).attr('data-line'))? $(this).attr('data-field'):null;
        var input = $('#'+ $(this).attr('data-input-id'));
        var val = 0;
        if(input.attr('type') == 'checkbox'){
            val = input.is(':checked')
        }else{
            val = input.val();
        }
        var span = $('#'+ $(this).attr('data-span-id'));
        var span_in = $('#'+ $(this).attr('data-span-in-id'));
        console.log($(this).attr('data-target'));
        $.ajax({
            method: "POST",
            url: $(this).attr('data-target'),
            data: { id: id, fieldName: fieldName,value: val,cls: cls,reload: reload, type: type},
            dataType: 'json',
        }).done(function( retour ) {
            if(retour.code == 'NOK'){
                elm.html('<i style="color:red" class="fa fa-check-circle"></i> ('+retour.err+')');
                input.val(retour.val);
            }else{
                if(reload == 'entity' && line) {
                    $('#' + line).replaceWith(retour.val);
                }else if(reload == 'field' && field){
                    $('#' + field).replaceWith(retour.val);
                }else{
                    elm.html('<i style="color:#00a65a;" class="jsa-click fa fa-save"></i>');
                    input.val(retour.val);
                    span_in.hide();
                    if(retour.val) span.html(retour.val); else span.html('<em>&nbsp;</em>');
                    span.show();
                }
            }
            if(callback) window[callback](retour,elm);
        }).fail(function( error ){
            elm.html('<i style="color:red" class="fa fa-check-circle"></i> (Error '+error.status+')');
        });
    });

});