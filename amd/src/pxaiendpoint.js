define(['jquery', "core/ajax", "core/str", 'core/templates', 'core/modal_factory', "core/modal_events"], function ($, Ajax, Str, Templates, ModalFactory, ModalEvents) {
    var EventCreator = function (id, cmid, contextid, steps_data) {

        this.instanceid = id;
        this.cmid = cmid;
        this.contextId = contextid;
        this.steps = steps_data;

        this.init();
    }

    EventCreator.prototype.init = function () {

        var getSelectedTextareaText = function (name) {

            var selText = '';
            var txtarea = document.getElementsByName(name);
            txtarea = (txtarea && txtarea.length > 0) ? txtarea[0] : null;
            if (txtarea) {
                var start = txtarea.selectionStart;
                var finish = txtarea.selectionEnd;
                selText = txtarea.value.substring(start, finish);
            } else {
                console.log("pxaiwriter : no known text selecable source");
            }
            return selText;

        }.bind(this);


        var getSelectedInput = function (textElement) {
            if (typeof elem != "undefined") {
                s = elem[0].selectionStart;
                e = elem[0].selectionEnd;
                return elem.val().substring(s, e);
            }
            else {
                return '';
            }
        }.bind(this);

        $(".actions-container").on("click", '#pxaiwriter-expand-selection', function (e) {

            const currentStep = $(e.target).data("step");
            stepData = this.steps.find(({ step }) => step == currentStep);
            const textElement = $('textarea[name="pxaiwriter-data-step-' + currentStep + '"]');
            const selectedText = getSelectedTextareaText("pxaiwriter-data-step-" + currentStep);

            if (!selectedText) {
                $('#text-selection-required-warning-modal').modal('show');
                return;
            }

            console.log(selectedText);

            let formData = new FormData();
            formData.append('text', selectedText);

            var promises = Ajax.call([
                {
                    methodname: "mod_mod_assign_submission_pxaiwriter_expand",
                    args: { contextid: this.contextId, jsondata: JSON.stringify(formData) },
                    done: this.handleResponse.bind(this),
                    fail: this.handleFailure.bind(this),
                },
            ]);

        }.bind(this));

        $(".actions-container").on("click", '#pxaiwriter-do-ai-magic', function (e) {

            const currentStep = $(e.target).data("step");
            stepData = this.steps.find(({ step }) => step == currentStep);

            $titleText = $("#pxaiwriter-title").val();

            if (!$titleText) {
                $('#title-required-warning-modal').modal('show');
                return;
            }

            let formData = new FormData();
            formData.append('text', $titleText);

            var promises = Ajax.call([
                {
                    methodname: "mod_mod_assign_submission_pxaiwriter_doaimagic",
                    args: { contextid: this.contextId, jsondata: JSON.stringify(formData) },
                    done: this.handleResponse.bind(this),
                    fail: this.handleFailure.bind(this),
                },
            ]);

        }.bind(this));

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
    }

    EventCreator.prototype.handleFailure = function (data) {
        $('#error-text').css('display', 'block');
        $('#error-text').text("An error occurred during creating event");
        $('[data-action="save"]').prop('disabled', false);
    }

    return {
        init: function (id, cmid, contextid, steps_data) {
            return new EventCreator(id, cmid, contextid, steps_data);
        }
    };
});