$(function(){
    var date=new Date();
    var format='dd/mm/yy';
    $('.datepicker').datepicker({dateFormat: format, changeMonth: true, changeYear: true });
    $('.datepicker-future').datepicker({dateFormat: format,minDate: 'now', changeMonth: true, changeYear: true});
    $('.datepicker-past').datepicker({dateFormat: format,maxDate: 'now', changeMonth: true, changeYear: true});
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

    $('body').on('click','.eap-edit-in-place-bool',function(){
        var elm = $(this);
        var id = elm.attr('data-item-id');
        var fieldName = elm.attr('data-field-name');
        var val = (parseInt(elm.attr('data-value')) > 0)? 0:1;
        var reload = ($(this).attr('data-reload'))? $(this).attr('data-reload'):'value';
        var line = ($(this).attr('data-line'))? $(this).attr('data-line'):null
        var field= ($(this).attr('data-line'))? $(this).attr('data-field'):null;
        var type = ($(this).attr('data-type'))? $(this).attr('data-type'):'string';
        var view= ($(this).attr('data-view'))? $(this).attr('data-view'):'list';
        $.ajax({
            method: "POST",
            url: $(this).attr('data-target'),
            data: { id: id, fieldName: fieldName,value: val,cls: '',reload: reload, type: type, view:view },
            dataType: "json",
        }).done(function( retour ) {
            if(retour.code == 'NOK'){
                alert('Une erreur est survenue ('+retour.err+')');
            }else{
                console.log(retour);
                if(retour.val == 1 || retour.val == "1" || retour.val == "oui" || retour.val == 'true'){
                    elm.removeClass('fa-square-o');
                    elm.addClass('fa-check-square-o');
                    elm.attr('data-value',1);
                }else{
                    elm.removeClass('fa-check-square-o');
                    elm.addClass('fa-square-o');
                    elm.attr('data-value',0);
                }

            }
        }).fail(function( error ){
            elm.html('<i style="color:red" class="fa fa-warning"></i>');
        });
    });

    $('body').on('mouseover', '.eap-edit-in-place', function(){
        if($(this).attr('data-hover-icon')){
            $('#' + $(this).attr('data-hover-icon')).show();
        }
    });

    $('body').on('mouseout', '.eap-edit-in-place', function(){
        if($(this).attr('data-hover-icon')){
            $('#' + $(this).attr('data-hover-icon')).hide();
        }
    });

    $('body').on('click','.eap-edit-in-place-ok',function(){
        console.log('ok')
        var elm = $(this);
        elm.html('<i class="fa fa-spinner"></i>');
        var id = $(this).attr('data-item-id');
        var callback = $(this).attr('data-callback');
        var fieldName = $(this).attr('data-field-name');
        var cls = $(this).attr('data-class');
        var reload = ($(this).attr('data-reload'))? $(this).attr('data-reload'):'value';
        var line = ($(this).attr('data-line'))? $(this).attr('data-line'):null;
        var type = ($(this).attr('data-type'))? $(this).attr('data-type'):'string';
        var field= ($(this).attr('data-field'))? $(this).attr('data-field'):null;
        var view= ($(this).attr('data-view'))? $(this).attr('data-view'):'list';
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
            data: { id: id, fieldName: fieldName,value: val,cls: cls,reload: reload, type: type, view:view},
            dataType: 'json',
        }).done(function( retour ) {
            if(retour.code == 'NOK'){
                elm.html('<i style="color:red" class="fa fa-check-circle"></i> ('+retour.err+')');
                input.val(retour.val);
            }else{
                elm.html('<i style="color:#00a65a; curosr:pointer;" class="fa fa-save"></i>');
                span_in.hide();
                if(retour.val) span.html(retour.html); else span.html('<em>&nbsp;</em>');
                span.show();
            }
            if(callback) window[callback](retour,elm);
        }).fail(function( error ){
            elm.html('<i style="color:red" class="fa fa-check-circle"></i> (Error '+error.status+')');
        });
    });

});
