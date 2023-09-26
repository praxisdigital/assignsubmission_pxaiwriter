/* eslint-disable camelcase */
import $ from "jquery";
import Ajax from "core/ajax";
import Notification from "core/notification";

/**
 * @typedef {Object} HistoryRecordResponse
 * @property {string} checksum
 * @property {string} timecreated
 * @property {string} timemodified
 */

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
 * @return {EventCreator}
 */
export const init = (
    assignmentId,
    submissionId,
    stepNumber = 1
) => {

    const defaultStep = 1;

    let properties = {
        checksumList: {},
    };

    /**
     * @param {number} step
     * @return {string}
     */
    const getChecksumByStep = (step) => {
        return properties.checksumList[step] ?? '';
    };

    /**
     * @param {number} step
     * @param {string} checksum
     */
    const setChecksumByStep = (step, checksum) => {
        properties.checksumList[step] = checksum;
    };

    const getNumberFromAttribute = (element, attribute) => {
        const value = element?.getAttribute(attribute);
        if (!value) {
            return undefined;
        }
        const number = Number.parseInt(value);
        return Number.isNaN(number) ? undefined : number;
    };

    /**
     * @param {number} assignmentId
     * @param {number} submissionId
     * @param {number} stepNumber
     * @constructor
     */
    let EventCreator = function(assignmentId, submissionId, stepNumber) {
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
     * @param {number} step
     * @return {HTMLTextAreaElement|null}
     */
    const getStepInput = (step) => {
        const element = document.querySelector(`${selectors.input}[data-input-step="${step}"]`);
        if (element instanceof HTMLTextAreaElement) {
            return element;
        }
        return null;
    };

    /**
     * @param {number} step
     */
    const copyTextFromPreviousStep = (step) => {

        if (isDebugMode()) {
            window.console.log(`${component}: Try to copy the text from the previous step to step ${step}...`);
        }

        const currentStepInput = getStepInput(step);
        if (currentStepInput === null) {
            if (isDebugMode()) {
                window.console.log(`${component}: Cannot find the current step ${step}`);
            }
            return;
        }

        if (currentStepInput.value.trim().length !== 0) {
            if (isDebugMode()) {
                window.console.log(`${component}: Cannot copy because the current step (${step}) is not empty`);
            }
            return;
        }

        const previousStep = step - 1;
        const previousStepInput = getStepInput(previousStep);

        if (previousStepInput === null) {
            if (isDebugMode()) {
                window.console.log(`${component}: Cannot find the previous step ${previousStep}`);
            }
            return;
        }

        if (previousStepInput.value.trim().length === 0) {
            if (isDebugMode()) {
                window.console.log(`${component}: Cannot copy because the previous step (${step}) is empty`);
            }
            return;
        }

        currentStepInput.value = previousStepInput.value;

        if (isDebugMode()) {
            window.console.log(`${component}: Copied the text from step ${previousStep} to ${step}`);
        }
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
                return true;
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
     * @return {Promise<string>}
     */
    const getHashCode = async(text) => {
        if (!text) {
            return null;
        }
        try {
            const encoder = new TextEncoder();
            const buffer = encoder.encode(text);
            const raw = await crypto.subtle.digest("SHA-256", buffer);
            return Array.from(new Uint8Array(raw)).map(b => b.toString(16).padStart(2, "0")).join("");
        } catch (e) {
            return '';
        }
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
         * @param {number} assignmentId
         * @param {number} submissionId
         * @param {string} text
         * @param {number} step
         * @return {Promise<HistoryRecordResponse>}
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

    EventCreator.prototype.init = function() {

        preventPasting(selectors.input);

        /**
         * @param {HTMLElement} button
         */
        const highlightStepButton = (button) => {
            if (!(button instanceof HTMLElement)) {
                return;
            }
            button.classList.add('current');
        };

        /**
         * @param {HTMLElement} button
         */
        const blurStepButton = (button) => {
            if (!(button instanceof HTMLElement)) {
                return;
            }
            button.classList.remove('current');
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

            copyTextFromPreviousStep(step);
        });

        wrapper?.addEventListener(eventList.pageChange, async(e) => {
            if (isDebugMode()) {
                window.console.log(`${component}: Page changed`);
            }

            const step = getPreviousStepByPageChangeEvent(e);
            await recordHistory(step);
        });

        document.querySelector(selectors.expandSelection)?.addEventListener("click", async(e) => {

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
                    assignmentId,
                    submissionId,
                    text,
                    selectedText,
                    textData.selectionStart
                );
                setApiResponseToInput(this.currentStep, response);
                await dispatchHistoryFromInput();
            } catch (exception) {
                await Notification.exception(exception);
            }
        });

        document.querySelector(selectors.doAIMagic)?.addEventListener("click", async(e) => {

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
                    assignmentId,
                    submissionId,
                    text,
                    defaultStep
                );
                setApiResponseToInput(this.currentStep, response);
                await dispatchHistoryFromInput();
            } catch (exception) {
                await Notification.exception(exception);
            }
        });

        document.querySelectorAll('textarea.pxaiwriter-student-data').forEach(async(element) => {
            const step = getNumberFromAttribute(element, 'data-input-step');
            if (step < 1) {
                return;
            }
            const checksum = await getHashCode(element.value);
            setChecksumByStep(step, checksum);
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
                textArea.value = response.data;
                textArea.dispatchEvent(new Event("change"));
            }
        }
        $('#loader').addClass('d-none');
    };

    /**
     * @param {number} step
     * @return {Promise<void>}
     */
    const recordHistory = async(step) => {
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
        const checksum = await getHashCode(text);

        if (checksum === getChecksumByStep(step)) {
            if (isDebugMode()) {
                window.console.log(`${component}: Nothing has been changed`);
            }
            return;
        }

        const response = await api.recordHistory(assignmentId, submissionId, text, step);
        setChecksumByStep(step, response.checksum);

        if (isDebugMode()) {
            window.console.log(`${component}: Input text got recorded`);
        }
    };

    const dispatchHistoryFromInput = async() => {
        const elements = document.querySelectorAll(selectors.input);
        for (const element of elements) {
            if (element instanceof HTMLTextAreaElement) {
                await recordHistory(getStepNumber(element));
            }
        }
    };

    return new EventCreator(assignmentId, submissionId, stepNumber);
};
