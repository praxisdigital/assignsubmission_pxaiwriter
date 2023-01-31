define(['jquery', "core/ajax", "core/str", 'core/templates', 'core/modal_factory', "core/modal_events"], function ($, Ajax, Str, Templates, ModalFactory, ModalEvents) {
    var EventCreator = function (id, cmid, contextid, steps_data, assignmentid, attempt_text) {

        this.instanceid = id;
        this.cmid = cmid;
        this.contextId = contextid;
        this.steps = steps_data;
        this.currStep = 0;
        this.selStart = 0;
        this.selEnd = 0;
        this.assignmentid = assignmentid
        this.attempt_text = attempt_text

        this.init();

    }

    EventCreator.prototype.init = function () {

        this.setAttemtCountText(this.attempt_text);

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
            const textElement = $('textarea[name="pxaiwriter-data-step-' + currentStep + '"]');
            const selectedText = getSelectedTextareaText("pxaiwriter-data-step-" + currentStep);

            if (!selectedText) {
                $('#text-selection-required-warning-modal').modal('show');
                return;
            }

            let formData = { 'text': selectedText, 'assignmentid': this.assignmentid };

            $(':button').prop('disabled', true);
            $('#loader').removeClass('d-none');

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
            const titleText = $('[name="pxaiwriter-data-step-' + currentStep + '"]').val(); // $("#pxaiwriter-title").val();

            if (!titleText) {
                $('#title-required-warning-modal').modal('show');
                return;
            }

            let formData = { 'text': titleText, 'assignmentid': this.assignmentid };

            $(':button').prop('disabled', true);
            $('#loader').removeClass('d-none');

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

            var elementValue = $('textarea[name="pxaiwriter-data-step-' + this.currStep + '"]').val();

            let finalText = elementValue + responseObj.data;
            finalText = finalText.replace(/((?:\r\n?|\n)+)$|(?:\r\n?|\n){2,}/g, '\n\n');

            var element = $('textarea[name="pxaiwriter-data-step-' + this.currStep + '"]').val(finalText);

            this.setAttemtCountText(responseObj.attempt_text);

            $('textarea[name="pxaiwriter-data-step-' + this.currStep + '"]').trigger("change");
        } else {
            if (responseObj.errors && responseObj.errors.includes("max_attempt_exceed_error")) {
                $('#max-attempt-exceeds-error-msg-modal').modal('show');
            }
        }
        $('#loader').addClass('d-none');
    }

    EventCreator.prototype.handleAiMagicFailure = function (response) {
        $(':button').prop('disabled', false);
        let responseObj = JSON.parse(response);
        alert(responseObj.message);
        $('#loader').addClass('d-none');
    }

    EventCreator.prototype.handleExpandResponse = function (response) {
        $(':button').prop('disabled', false);
        let responseObj = JSON.parse(response);
        if (responseObj.success == true) {

            let expandedText = responseObj.data;
            let elementText = $('textarea[name="pxaiwriter-data-step-' + this.currStep + '"]').val();

            let finalText = elementText.substring(0, this.selStart) + expandedText + elementText.substring(this.selEnd);

            finalText = finalText.replace(/((?:\r\n?|\n)+)$|(?:\r\n?|\n){2,}/g, '\n\n');

            var element = $('textarea[name="pxaiwriter-data-step-' + this.currStep + '"]').val(finalText);

            this.setAttemtCountText(responseObj.attempt_text);

            $('textarea[name="pxaiwriter-data-step-' + this.currStep + '"]').trigger("change");

        } else {
            if (responseObj.errors && responseObj.errors.includes("max_attempt_exceed_error")) {
                $('#max-attempt-exceeds-error-msg-modal').modal('show');
            }
        }
        $('#loader').addClass('d-none');
    }

    EventCreator.prototype.handleExpandFailure = function (data) {
        $(':button').prop('disabled', false);
        let responseObj = JSON.parse(response);
        alert(responseObj.message);
        $('#loader').addClass('d-none');
    }

    EventCreator.prototype.setAttemtCountText = function (text) {
        if (text) {
            $(".remaining-ai-attempts").text(text);
        }
    }

    return {
        init: function (id, cmid, contextid, steps_data, assignmentid, attempt_text) {
            return new EventCreator(id, cmid, contextid, steps_data, assignmentid, attempt_text);
        }
    };
});