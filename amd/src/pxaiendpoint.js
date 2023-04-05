import $ from "jquery";
import Ajax from "core/ajax";
import AISubmissionEvent from "./app/ai/event";
import AISubmissionPageChangeEvent from "./app/ai/page_change_event";

/**
 * @param {number} id
 * @param {number} cmid
 * @param {number} contextid
 * @param {string} steps_data
 * @param {number} assignmentid
 * @param {string} attempt_text
 */
export const init = (
    id,
    cmid,
    contextid,
    steps_data,
    assignmentid,
    attempt_text
) => {

    let EventCreator = function (stepsData, assignmentId, attemptText) {
        this.currStep = 0;
        this.selStart = 0;
        this.selEnd = 0;
        this.assignmentid = assignmentId;
        this.attempt_text = attemptText;
        this.init();
    };

    EventCreator.prototype.init = function () {

        this.setAttemtCountText(this.attempt_text);
        const container = $(".actions-container");

        /**
         * @param {HTMLElement} button
         */
        const highlightStepButton = (button) => {
            if (!(button instanceof HTMLElement)) {
                return;
            }
            button.classList.add('btn-outline-primary');
            button.classList.remove('btn-outline-secondary');
        };

        /**
         * @param {HTMLElement} button
         */
        const blurStepButton = (button) => {
            if (!(button instanceof HTMLElement)) {
                return;
            }
            button.classList.remove('btn-outline-primary');
            button.classList.add('btn-outline-secondary');
        };

        let getSelectedTextareaText = function (name) {

            let selText = '';
            let textArea = document.getElementsByName(name);
            textArea = (textArea && textArea.length > 0) ? textArea[0] : null;
            if (textArea) {
                let start = textArea.selectionStart;
                this.selStart = start;
                let finish = textArea.selectionEnd;
                this.selEnd = finish;
                selText = textArea.value.substring(start, finish);
            } else {
                window.console.log("pxaiwriter : no known text selecable source");
            }
            return selText;

        }.bind(this);

        document.querySelector('.assignsubmission_pxaiwriter').addEventListener(AISubmissionEvent.EventList.pageChange, (e) => {

            const event = new AISubmissionPageChangeEvent(e);
            const currentStepSelector = `.step-page-button[data-step-number="${event.currentStep}"]`;
            const allStepSelector = `.step-page-button[data-step-number]`;
            const currentStepButton = document.querySelector(currentStepSelector);
            if (!currentStepButton) {
                return;
            }

            const allStepButtons = document.querySelectorAll(allStepSelector);
            for (const button of allStepButtons) {
                blurStepButton(button);
            }
            highlightStepButton(currentStepButton);
        });

        container.on("click", '#pxaiwriter-expand-selection', function (e) {

            const currentStep = $(e.target).data("step");
            this.currStep = currentStep;
            const selectedText = getSelectedTextareaText("pxaiwriter-data-step-" + currentStep);

            if (!selectedText) {
                $('#text-selection-required-warning-modal').modal('show');
                return;
            }

            $(':button').prop('disabled', true);
            $('#loader').removeClass('d-none');

            Ajax.call([
                {
                    methodname: "assignsubmission_pxaiwriter_expand_ai_text",
                    args: {
                        assignment_id: this.assignmentid,
                        step: this.currStep,
                        text: selectedText
                    },
                    done: this.handleExpandResponse.bind(this),
                    fail: this.handleExpandFailure.bind(this),
                },
            ]);

        }.bind(this));

        container.on("click", '#pxaiwriter-do-ai-magic', function (e) {

            const currentStep = $(e.target).data("step");
            this.currStep = currentStep;
            const titleText = $('[name="pxaiwriter-data-step-' + currentStep + '"]').val();

            if (!titleText) {
                $('#title-required-warning-modal').modal('show');
                return;
            }

            $(':button').prop('disabled', true);
            $('#loader').removeClass('d-none');

            Ajax.call([
                {
                    methodname: "assignsubmission_pxaiwriter_generate_ai_text",
                    args: {
                        assignment_id: this.assignmentid ?? 2,
                        step: this.currStep,
                        text: titleText
                    },
                    done: this.handleAiMagicResponse.bind(this),
                    fail: this.handleAiMagicFailure.bind(this),
                },
            ]);

        }.bind(this));
    };

    EventCreator.prototype.handleAiMagicResponse = function (response) {
        $(':button').prop('disabled', false);
        if (response.hasOwnProperty('data')) {
            this.setAttemtCountText(response.attempt_text);
            const textArea = $('textarea[name="pxaiwriter-data-step-' + this.currStep + '"]');
            textArea.val(response.data);
            textArea.trigger("change");
        }
        $('#loader').addClass('d-none');
    };

    EventCreator.prototype.handleAiMagicFailure = function (response) {
        $(':button').prop('disabled', false);
        window.console.error(response);
        $('#loader').addClass('d-none');
    };

    EventCreator.prototype.handleExpandResponse = function (response) {
        $(':button').prop('disabled', false);
        if (response.hasOwnProperty('data')) {
            this.setAttemtCountText(response.attempt_text);
            const textArea = $('textarea[name="pxaiwriter-data-step-' + this.currStep + '"]');
            textArea.val(response.data);
            textArea.trigger("change");
        }
        $('#loader').addClass('d-none');
    };

    EventCreator.prototype.handleExpandFailure = function (error) {
        $(':button').prop('disabled', false);
        let responseObj = JSON.parse(error);
        alert(responseObj.message ?? '');
        $('#loader').addClass('d-none');
    };

    EventCreator.prototype.setAttemtCountText = function (text) {
        if (text) {
            $(".remaining-ai-attempts").text(text);
        }
    };

    return new EventCreator(steps_data, assignmentid, attempt_text);
};
