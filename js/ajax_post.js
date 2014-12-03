

function removeAllErrorMessage() {
    $(".error").children(".message").remove();
    $(".error").removeClass('error');
}

function showElementError(eid, message) {




    $("#" + eid).parents(".element").removeClass("error");
    $("#" + eid).parents(".element").children(".message").remove();


    $("#" + eid).parents(".element").addClass("error");
    $("#" + eid).parents(".inputWrapper").after("<div class='message' style='float:left;padding:4px'>" + message + "</div>");


}





function ajax_post(url, data,
        click_button_id,
        success_message_div_id,
        success_message_class,
        success_message,
        successFunction,
        error_message_div_id,
        error_message_class,
        error_message,
        errorFunction,
        doneFunction) {




    //hide the button


    if (typeof click_button_id === 'string') {
        $('#' + click_button_id).hide();


    }
    var successDiv = null;
    if (typeof success_message_div_id === 'string') {
        successDiv = $("#" + success_message_div_id);
    }


    // remove suucess message


    if (successDiv !== null) {
        if (typeof success_message_class === 'string') {
            successDiv.removeClass(success_message_class);
        }
        if (typeof success_message === 'string') {
            successDiv.text("");
        }
        successDiv.hide();
    }


    var errorDiv = null;


    if (typeof error_message_div_id === 'string') {
        errorDiv = $("#" + error_message_div_id);
    }


    // remove error message


    if (typeof error_message_class === 'string') {
        $('.' + error_message_class).removeClass(error_message_class);
    }
    if (errorDiv !== null) {


        if (typeof error_message === 'string') {
            errorDiv.text("");
        }
        errorDiv.hide();
    }


    function errorHandle(errorObj) {
        var errorMessage = null;


        if (typeof errorObj['error_message'] === 'string') {


            errorMessage = errorObj['error_message'];


        }
        else if (typeof error_message === 'string') {
            errorMessage = error_message;
        }


        if (errorDiv !== null) {
            if (typeof error_message_class === 'string') {
                errorDiv.addClass(error_message_class);
            }


            if (errorMessage !== null) {
                errorDiv.text(errorMessage);
            }


            errorDiv.show();
        }


        if (typeof errorObj['error_input_id'] === 'string') {


            var em = null;
            if (typeof errorObj['error_detail'] === 'string') {
                em = errorObj['error_detail'];
            }
            else if (errorMessage !== null) {
                em = errorMessage;
            }
            if (em !== null) {
                showElementError(errorObj['error_input_id'], em);
            }
        }


        else {
            $('html, body').animate({scrollTop: 0}, 0);
        }


        if (typeof errorFunction === 'function') {
            errorFunction(errorObj);
        }
    }


    $.ajax({
        url: url,
        type: 'post',
        data: data,
        success: function(data, textStatus, xhr) {
            if (typeof successFunction === 'function') {
                successFunction(data, textStatus, xhr);
            }

        },
        error: function(xhr, textStatus, errorThrown) {
            var errorObj = {};


            try {
                errorObj = JSON.parse(xhr.responseText);


            }
            catch (e) {
                errorObj['error_message'] = xhr.responseText;
            }
            errorHandle(errorObj);
        },
        complete: function() {
            if (typeof click_button_id === 'string') {
                $('#' + click_button_id).show();


            }
            if (typeof doneFunction === 'function') {
                doneFunction();
            }
        }
    });




}
