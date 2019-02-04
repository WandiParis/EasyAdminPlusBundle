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