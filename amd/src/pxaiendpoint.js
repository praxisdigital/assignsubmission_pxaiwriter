import $ from "jquery";
import Ajax from "core/ajax";
import Notification from "core/notification";

/**
 * @typedef TextAreaData
 * @property {string} text
 * @property {string} selectedText
 * @property {number} selectionStart
 * @property {number} selectionEnd
 */

/**
 * @param {number} assignmentId
 * @param {number} submissionId
 * @param {number} stepNumber
 */
export const init = (
    assignmentId,
    submissionId,
    stepNumber = 1
) => {

    const defaultStep = 1;

    /**
     * @param {number} assignmentId
     * @param {number} submissionId
     * @param {number} stepNumber
     * @constructor
     */
    let EventCreator = function (assignmentId, submissionId, stepNumber) {
        this.currentStep = stepNumber;
        this.selectedStart = 0;
        this.selectedEnd = 0;
        this.assignmentId = assignmentId;
        this.submissionId = submissionId;
        this.init();
    };

    const component = 'assignsubmission_pxaiwriter';

    const eventList = {
        pageChange: 'page-change',
        stepTextSave: 'step-save'
    };

    const selectors = {
        wrapper: '.assignsubmission_pxaiwriter',
        doAIMagic: '#pxaiwriter-do-ai-magic',
        expandSelection: '#pxaiwriter-expand-selection',
        input: '.pxaiwriter-student-data[data-input-step]',
    };

    /**
     * @param {string} target
     */
    const preventPasting = (target) => {
        const elements = document.querySelectorAll(target);
        if (!elements) {
            return;
        }
        for (const element of elements) {
            element.addEventListener('keydown', (event) => {
                if (event.ctrlKey && event.key === 'v') {
                    event.preventDefault();
                    event.stopPropagation();
                    return false;
                }
            });
            element.addEventListener('paste', (event) => {
                event.preventDefault();
                event.stopPropagation();
                return false;
            });
        }
    };

    /**
     * @return {boolean}
     */
    const isDebugMode = () => {
        return !!M?.cfg?.developerdebug;
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
    const getStepNumber = (element) => {
        const currentStep = Number.parseInt(element?.dataset?.step);
        if (Number.isNaN(currentStep)) {
            return 0;
        }
        return currentStep;
    };

    /**
     * @param {number} step
     * @return {TextAreaData|null}
     */
    const getStepTextAreaData = (step) => {
        const textArea = getStepTextArea(step);
        if (!(textArea instanceof HTMLTextAreaElement)) {
            return null;
        }
        return {
            text: textArea.value,
            selectedText: textArea.value.substring(textArea.selectionStart, textArea.selectionEnd),
            selectionStart: textArea.selectionStart,
            selectionEnd: textArea.selectionEnd
        };
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
    const getCurrentStepByPageChangeEvent = (event) => {
        return event?.detail?.currentStep;
    };

    /**
     *
     * @param {CustomEvent|Event} event
     * @return {number}
     */
    const getPreviousStepByPageChangeEvent = (event) => {
        const currentStep = getCurrentStepByPageChangeEvent(event);
        const prevStep = event?.detail?.prevStep;
        return currentStep === prevStep ? 0 : prevStep;
    };

    /**
     * @param {string} text
     * @return {Promise<string|null>}
     */
    const getHashCode = async (text) => {
        if (!text) {
            return null;
        }
        try {
            const encoder = new TextEncoder();
            const buffer = encoder.encode(text);
            const raw = await crypto.subtle.digest("SHA-256", buffer);
            return Array.from(new Uint8Array(raw)).map(b => b.toString(16).padStart(2, "0")).join("");
        }
        catch (e) {}
        return null;
    };

    /**
     * @template T
     * @param {string} methodName
     * @param {*} parameters
     * @return {Promise<T>}
     */
    const requestAIApi = (methodName, parameters = {}) => {
        return Ajax.call([
            {
                methodname: methodName,
                args: parameters
            },
        ])[0];
    };

    const api = {
        /**
         * @template T
         * @param {number} assignmentId
         * @param {number} submissionId
         * @param {string} text
         * @param {string} selectedText
         * @param {number} selectStart
         * @param {number} step
         * @return {Promise<T>}
         */
        expandText: (assignmentId, submissionId, text, selectedText, selectStart, step = 1) => {
            return requestAIApi(
                'assignsubmission_pxaiwriter_expand_ai_text', {
                assignment_id: assignmentId,
                submission: submissionId,
                text: text,
                selected_text: selectedText,
                select_start: selectStart,
                step: step
            });
        },
        /**
         * @template T
         * @param {number} assignmentId
         * @param {number} submissionId
         * @param {string} text
         * @param {number} step
         * @return {Promise<T>}
         */
        generateText: (assignmentId, submissionId, text, step = 1) => {
            return requestAIApi('assignsubmission_pxaiwriter_generate_ai_text', {
                assignment_id: assignmentId,
                submission: submissionId,
                text: text,
                step: step
            });
        },
        /**
         * @template T
         * @param {number} assignmentId
         * @param {number} submissionId
         * @param {string} text
         * @param {number} step
         * @return {Promise<T>}
         */
        recordHistory: (assignmentId, submissionId, text, step = 1) => {
            return requestAIApi('assignsubmission_pxaiwriter_record_history', {
                assignment_id: assignmentId,
                submission: submissionId,
                text: text,
                step: step
            });
        }
    };

    EventCreator.prototype.init = function () {

        preventPasting(selectors.input);

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

        const wrapper = document.querySelector(selectors.wrapper);

        wrapper?.addEventListener(eventList.pageChange, (e) => {

            if (isDebugMode()) {
                window.console.log(`${component}: Step switched...`);
            }

            let step = getCurrentStepByPageChangeEvent(e);
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

        wrapper?.addEventListener(eventList.pageChange, async (e) => {
            if (isDebugMode()) {
                window.console.log(`${component}: Page changed`);
            }

            const step = getPreviousStepByPageChangeEvent(e);
            await recordHistory(step);
        });

        document.querySelector(selectors.expandSelection)?.addEventListener("click", async (e) => {

            if (isDebugMode()) {
                window.console.log(`${component}: Expand selected text...`);
            }
            const step = getStepNumber(e.target);
            const textData = getStepTextAreaData(step);

            const text = textData.text;
            const selectedText = textData?.selectedText;

            if (!validateInputText(selectedText)) {
                if (isDebugMode()) {
                    window.console.warn(`${component}: No selection detected`);
                }
                return;
            }

            loadingData();

            try {
                const response = await api.expandText(
                    this.assignmentId,
                    this.submissionId,
                    text,
                    selectedText,
                    textData.selectionStart
                );
                setApiResponseToInput(this.currentStep, response);
                await dispatchHistoryFromInput();
            }
            catch (exception) {
                await Notification.exception(exception);
            }
        });

        document.querySelector(selectors.doAIMagic)?.addEventListener("click", async (e) => {

            if (isDebugMode()) {
                window.console.log(`${component}: Do AI magic...`);
            }

            const text = getStepInputText(getStepNumber(e.target));

            if (!validateInputText(text)) {
                if (isDebugMode()) {
                    window.console.warn(`${component}: Input text is empty`);
                }
                return;
            }

            loadingData();

            try {
                const response = await api.generateText(
                    this.assignmentId,
                    this.submissionId,
                    text,
                    defaultStep
                );
                setApiResponseToInput(this.currentStep, response);
                await dispatchHistoryFromInput();
            }
            catch (exception) {
                await Notification.exception(exception);
            }
        });
    };

    /**
     * @param {number} step
     * @param {*} response
     */
    const setApiResponseToInput = (step, response) => {
        $(':button').prop('disabled', false);
        if (response.hasOwnProperty('attempt_text')) {
            setRemainingAttemptText(response.attempt_text);
        }
        if (response.hasOwnProperty('data')) {
            const textArea = getStepTextArea(step);
            if (textArea instanceof HTMLTextAreaElement) {
                if (step === defaultStep) {
                    distributeTextToInputs(response.data);
                }
                else {
                    textArea.value = response.data;
                    textArea.dispatchEvent(new Event("change"));
                }
            }
        }
        $('#loader').addClass('d-none');
    };

    /**
     * @param {string} text
     */
    const distributeTextToInputs = (text) => {
        const elements = document.querySelectorAll(selectors.input);
        for (const element of elements) {
            if (element instanceof HTMLTextAreaElement) {
                element.value = text;
                element.dispatchEvent(new Event("change"));
            }
        }
    };

    /**
     * @param {number} step
     * @return {Promise<void>}
     */
    const recordHistory = async (step) => {
        if (isDebugMode()) {
            window.console.log(`${component}: Saving history...`);
        }

        if (step < 1) {
            if (isDebugMode()) {
                window.console.log(`${component}: Nothing to be save...`);
            }
            return;
        }
        const text = getStepInputText(step);
        const response = await api.recordHistory(this.assignmentId, this.submissionId, text, step);

        if (isDebugMode() && response.hasOwnProperty("checksum")) {
            if (!response.checksum) {
                window.console.log(`${component}: Cannot determine the changes in the data`);
                return;
            }

            let textChecksum = await getHashCode(text);
            textChecksum = textChecksum.toLocaleLowerCase();
            if (response.checksum === textChecksum) {
                window.console.log(`${component}: Input text got recorded`);
            }
            else {
                window.console.log(`${component}: Nothing has been changed`);
            }
        }
    };

    const dispatchHistoryFromInput = async () => {
        const elements = document.querySelectorAll(selectors.input);
        for (const element of elements) {
            if (element instanceof HTMLTextAreaElement) {
                await recordHistory(getStepNumber(element));
            }
        }
    };

    return new EventCreator(assignmentId,  submissionId, stepNumber);
};
