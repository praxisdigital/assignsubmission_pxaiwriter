define(['jquery', "core/ajax", "core/str", 'core/templates', 'core/modal_factory', "core/modal_events"], function ($, Ajax, Str, Templates, ModalFactory, ModalEvents) {
    var EventCreator = function (id, cmid, contextid, steps_data, assignmentid) {

        this.instanceid = id;
        this.cmid = cmid;
        this.contextId = contextid;
        this.steps = steps_data;
        this.currStep = 0;
        this.selStart = 0;
        this.selEnd = 0;
        this.assignmentid = assignmentid

        this.init();
    }

    EventCreator.prototype.init = function () {

        var getSelectedTextareaText = function (name) {

            var selText = '';
            var txtarea = document.getElementsByName(name);
            txtarea = (txtarea && txtarea.length > 0) ? txtarea[0] : null;
            if (txtarea) {
                var start = txtarea.selectionStart;
                this.selStart = start;
                var finish = txtarea.selectionEnd;
                this.selEnd = finish;
                selText = txtarea.value.substring(start, finish);
            } else {
                console.log("pxaiwriter : no known text selecable source");
            }
            return selText;

        }.bind(this);

        $(".actions-container").on("click", '#pxaiwriter-expand-selection', function (e) {

            const currentStep = $(e.target).data("step");
            this.currStep = currentStep;
            // let stepData = this.steps.find(({ step }) => step == currentStep);
            const textElement = $('textarea[name="pxaiwriter-data-step-' + currentStep + '"]');
            const selectedText = getSelectedTextareaText("pxaiwriter-data-step-" + currentStep);

            if (!selectedText) {
                $('#text-selection-required-warning-modal').modal('show');
                return;
            }

            let formData = { 'text': selectedText, 'assignmentid': this.assignmentid };

            $(':button').prop('disabled', true);
            var promises = Ajax.call([
                {
                    methodname: "mod_mod_assign_submission_pxaiwriter_expand",
                    args: { contextid: this.contextId, jsondata: JSON.stringify(formData) },
                    done: this.handleExpandResponse.bind(this),
                    fail: this.handleExpandFailure.bind(this),
                },
            ]);

        }.bind(this));

        $(".actions-container").on("click", '#pxaiwriter-do-ai-magic', function (e) {

            const currentStep = $(e.target).data("step");
            this.currStep = currentStep;

            // let stepData = this.steps.find(({ step }) => step == currentStep);

            const titleText = $("#pxaiwriter-title").val();

            if (!titleText) {
                $('#title-required-warning-modal').modal('show');
                return;
            }

            let formData = { 'text': titleText, 'assignmentid': this.assignmentid };

            $(':button').prop('disabled', true);
            var promises = Ajax.call([
                {
                    methodname: "mod_mod_assign_submission_pxaiwriter_doaimagic",
                    args: { contextid: this.contextId, jsondata: JSON.stringify(formData) },
                    done: this.handleAiMagicResponse.bind(this),
                    fail: this.handleAiMagicFailure.bind(this),
                },
            ]);

        }.bind(this));

    }

    EventCreator.prototype.handleAiMagicResponse = function (response) {
        $(':button').prop('disabled', false);
        let responseObj = JSON.parse(response);
        if (responseObj.success == true) {
            var element = $('textarea[name="pxaiwriter-data-step-' + this.currStep + '"]').val(responseObj.data);
            $('textarea[name="pxaiwriter-data-step-' + this.currStep + '"]').trigger("change");
        } else {
            alert(responseObj.message);
        }
    }

    EventCreator.prototype.handleAiMagicFailure = function (response) {
        $(':button').prop('disabled', false);
        let responseObj = JSON.parse(response);
        alert(responseObj.message);
        // $('#error-text').css('display', 'block');
        // $('#error-text').text("An error occurred during creating event");
        // $('[data-action="save"]').prop('disabled', false);
    }




    EventCreator.prototype.handleExpandResponse = function (response) {
        $(':button').prop('disabled', false);
        let responseObj = JSON.parse(response);
        if (responseObj.success == true) {

            let expandedText = responseObj.data;
            let elementText = $('textarea[name="pxaiwriter-data-step-' + this.currStep + '"]').val();

            let finalText = elementText.substring(0, this.selStart) + expandedText + elementText.substring(this.selEnd);
            var element = $('textarea[name="pxaiwriter-data-step-' + this.currStep + '"]').val(finalText);
            $('textarea[name="pxaiwriter-data-step-' + this.currStep + '"]').trigger("change");
            // let error = responseObj.message;
            // if (responseObj.errors && responseObj.errors.overlapping_events_error) {
            //     error = responseObj.errors.overlapping_events_error;
            // }
            // $('#error-text').css('display', 'block');
            // $('#error-text').text(error);
            // $('[data-action="save"]').prop('disabled', false);
        } else {
            alert(responseObj.message);
        }
    }

    EventCreator.prototype.handleExpandFailure = function (data) {
        $(':button').prop('disabled', false);
        let responseObj = JSON.parse(response);
        alert(responseObj.message);
        // $('#error-text').css('display', 'block');
        // $('#error-text').text("An error occurred during creating event");
        // $('[data-action="save"]').prop('disabled', false);
    }

    return {
        init: function (id, cmid, contextid, steps_data, assignmentid) {
            return new EventCreator(id, cmid, contextid, steps_data, assignmentid);
        }
    };
});