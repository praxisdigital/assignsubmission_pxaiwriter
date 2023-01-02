define(['jquery', "core/ajax", "core/str", 'core/templates', 'core/modal_factory', "core/modal_events"], function ($, Ajax, Str, Templates, ModalFactory, ModalEvents) {
    var EventCreator = function (formItem, events) {
        // this.contextId = 1; // Sys context
        // this.instanceid = formItem.instanceid;
        this.init();
    }

    EventCreator.prototype.init = function () {
        $('.createnew').on('click', function () {

        }.bind(this));

    }

    EventCreator.prototype.createNewEvent = async function (root) {

        var promises = Ajax.call([
            {
                methodname: "mod_mod_assign_submission_pxaiwriter_doaimagic",
                args: { contextid: this.contextId, jsonformdata: JSON.stringify(formData) },
                done: this.handleResponse.bind(this),
                fail: this.handleFailure.bind(this),
            },
        ]);
    }

    EventCreator.prototype.handleResponse = function (response) {
        responseObj = JSON.parse(response);
        if (responseObj.success == false) {
            let error = responseObj.message;
            if (responseObj.errors && responseObj.errors.overlapping_events_error) {
                error = responseObj.errors.overlapping_events_error;
            }
            $('#error-text').css('display', 'block');
            $('#error-text').text(error);
            $('[data-action="save"]').prop('disabled', false);
        }

        else {
            window.location.reload();
        }
    }

    EventCreator.prototype.handleFailure = function (data) {
        $('#error-text').css('display', 'block');
        $('#error-text').text("An error occurred during creating event");
        $('[data-action="save"]').prop('disabled', false);
    }

    return {
        init: function (formItem, events) {
            return new EventCreator(formItem, events);
        }
    };
});