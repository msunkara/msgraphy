var WIZIAPP_REPORT_HANDLER = (function(){
    function wiziappReceiveMessage(event){
        // Just wrap our handleRequest
        if ( event.origin == jQuery('#wiziapp_report_frame').attr('data-origin') ){
            WIZIAPP_REPORT_HANDLER.handleRequest(event.data);
        }
    };

    if ( window.addEventListener ){
        window.addEventListener("message", wiziappReceiveMessage, false);
    }

    var actions = {
        closeReportForm: function(){
            var $box = jQuery('.wiziapp_errors_container');
            //$box.data("overlay").close();
            $box
                .find(".report_container").hide().end()
                .find(".errors_container").show().end();

            $box.trigger('closingReportForm');
            $box = null;
        }
    };

    return {
        handleRequest: function(q){
            var paramsArray = q.split('&');
            var params = {};
            for ( var i = 0; i < paramsArray.length; ++i) {
                var parts = paramsArray[i].split('=');
                params[parts[0]] = decodeURIComponent(parts[1]);
            }
            if ( typeof(actions[params.action]) == "function" ){
                actions[params.action](params);
            }
            params = q = paramsArray = null;
        }
    };
})();