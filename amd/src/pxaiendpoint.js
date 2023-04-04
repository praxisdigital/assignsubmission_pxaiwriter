import $ from "jquery";
import Ajax from "core/ajax";
import Notification from "core/notification";

/**
 * @param {number} assignmentId
 */
export const init = (
    assignmentId
) => {

    let EventCreator = function (assignmentId) {
        this.currentStep = 0;
        this.selectedStart = 0;
        this.selectedEnd = 0;
        this.assignmentId = assignmentId;
        this.init();
    };

    const eventList = {
        pageChange: 'page-change'
    };

    /**
     * @param {string} text
     */
    const setRemainingAttemptText = (text) => {
        if (!text) {
            return;
        }
        const label = document.querySelector('.remaining-ai-attempts');
        if (label instanceof HTMLElement) {
            label.innerHTML = text;
        }
    };

    /**
     * @param {number} step
     * @return {HTMLTextAreaElement|null}
     */
    const getStepTextArea = (step) => {
        return document.querySelector(`textarea[name="pxaiwriter-data-step-${step}"]`);
    };

    /**
     * @param {HTMLElement} element
     * @return {number}
     */
    const getCurrentStep = (element) => {
        const currentStep = Number.parseInt(element?.dataset?.step);
        if (Number.isNaN(currentStep)) {
            return 0;
        }
        return currentStep;
    };

    /**
     * @param {number} step
     * @return {string}
     */
    const getStepInputText = (step) => {
        return getStepTextArea(step)?.value ?? '';
    };

    /**
     * @param {string} text
     * @return {boolean}
     */
    const validateInputText = (text) => {
        if (!text || text.length === 0) {
            $('#title-required-warning-modal').modal('show');
            return false;
        }
        return true;
    };

    const loadingData = () => {
        $(':button').prop('disabled', true);
        $('#loader').removeClass('d-none');
    };

    /**
     * @param {CustomEvent|Event} event
     * @return {number}
     */
    const getCurrentStepFromPageChangeEvent = (event) => {
        return event?.detail?.currentStep;
    };

    /**
     * @template T
     * @param {string} methodName
     * @param {number} assignmentId
     * @param {string} text
     * @param {number} step
     * @return {Promise<T>}
     */
    const requestAIApi = (
        methodName,
        assignmentId,
        text,
        step = 1
    ) => {
        return Ajax.call([
            {
                methodname: methodName,
                args: {
                    assignment_id: assignmentId,
                    step: step,
                    text: text
                }
            },
        ])[0];
    };

    EventCreator.prototype.init = function () {
        const actionsContainer = $(".actions-container");
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

        const getSelectedTextAreaText = (step) => {
            const textArea = getStepTextArea(step);
            if (textArea instanceof HTMLTextAreaElement) {
                this.selectedStart = textArea.selectionStart;
                this.selectedEnd = textArea.selectionEnd;
                return textArea.value.substring(this.selectedStart, this.selectedEnd);
            }
            else {
                window.console.warn("Error: No text was selected");
            }
            return '';
        };

        document.querySelector('.assignsubmission_pxaiwriter').addEventListener(eventList.pageChange, (e) => {
            let step = getCurrentStepFromPageChangeEvent(e);
            const currentStepButton = document.querySelector(`.step-page-button[data-step-number="${step}"]`);
            if (!currentStepButton) {
                return;
            }

            const allStepButtons = document.querySelectorAll(`.step-page-button[data-step-number]`);
            for (const button of allStepButtons) {
                blurStepButton(button);
            }
            highlightStepButton(currentStepButton);
        });

        actionsContainer.on("click", '#pxaiwriter-expand-selection', async (e) => {

            const selectedText = getSelectedTextAreaText(getCurrentStep(e.target));
            if (!selectedText) {
                $('#text-selection-required-warning-modal').modal('show');
                return;
            }

            loadingData();

            try {
                const response = await requestAIApi(
                    'assignsubmission_pxaiwriter_expand_ai_text',
                    this.assignmentId,
                    selectedText,
                    this.currentStep
                );
                setApiResponseToInput(this.currentStep, response);
            }
            catch (exception) {
                await Notification.exception(exception);
            }
        });

        actionsContainer.on("click", '#pxaiwriter-do-ai-magic', async (e) => {

            const titleText = getStepInputText(getCurrentStep(e.target));
            if (!validateInputText(titleText)) {
                return;
            }

            loadingData();

            try {
                const response = await requestAIApi(
                    'assignsubmission_pxaiwriter_generate_ai_text',
                    this.assignmentId,
                    titleText,
                    this.currentStep
                );
                setApiResponseToInput(this.currentStep, response);
            }
            catch (exception) {
                await Notification.exception(exception);
            }
        });
    };

    const setApiResponseToInput = (step, response) => {
        $(':button').prop('disabled', false);
        if (response.hasOwnProperty('attempt_text')) {
            setRemainingAttemptText(response.attempt_text);
        }
        if (response.hasOwnProperty('data')) {
            const textArea = getStepTextArea(step);
            if (textArea instanceof HTMLTextAreaElement) {
                textArea.value = response.data;
                textArea.dispatchEvent(new Event("change"));
            }
        }
        $('#loader').addClass('d-none');
    };

    return new EventCreator(assignmentId);
};
