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

class Api {
    #requestAIApi(methodName, parameters = {}) {
        return Ajax.call([
            {
                methodname: methodName,
                args: parameters
            },
        ])[0];
    }

    expandText(assignmentId, submissionId, text, selectedText, selectStart, step = 1) {
        return this.#requestAIApi(
            'assignsubmission_pxaiwriter_expand_ai_text', {
                assignment_id: assignmentId,
                submission: submissionId,
                text: text,
                selected_text: selectedText,
                select_start: selectStart,
                step: step
            });
    }

    generateText(assignmentId, submissionId, text, step = 1) {
        return this.#requestAIApi('assignsubmission_pxaiwriter_generate_ai_text', {
            assignment_id: assignmentId,
            submission: submissionId,
            text: text,
            step: step
        });
    }

    recordHistory(assignmentId, submissionId, text, step = 1) {
        return this.#requestAIApi('assignsubmission_pxaiwriter_record_history', {
            assignment_id: assignmentId,
            submission: submissionId,
            text: text,
            step: step
        });
    }
}

class EventCreator {
    defaultStep = 1;

    properties = {
        checksumList: {},
    };

    component = 'assignsubmission_pxaiwriter';

    eventList = {
        pageChange: 'page-change',
        stepTextSave: 'step-save'
    };

    selectors = {
        wrapper: '.assignsubmission_pxaiwriter',
        doAIMagic: '#pxaiwriter-do-ai-magic',
        expandSelection: '#pxaiwriter-expand-selection',
        input: '.pxaiwriter-student-data[data-input-step]',
        maxAttemptsErrorMessage: '#assignsubmission_pxaiwriter_max_attempts_message'
    };

    /**
     * @param {number} assignmentId
     * @param {number} submissionId
     * @param {number} stepNumber
     * @param {number} maxAttempts
     * @param {number} attemptsCount
     * @constructor
     */
    constructor(assignmentId, submissionId, stepNumber, maxAttempts, attemptsCount) {
        this.currentStep = stepNumber;
        this.maxAttempts = maxAttempts;
        this.attemptsCount = attemptsCount;
        this.assignmentId = assignmentId;
        this.submissionId = submissionId;
        this.api = new Api();
        this.init();
    }

    init() {
        this.preventPasting(this.selectors.input);

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

        const wrapper = document.querySelector(this.selectors.wrapper);

        wrapper?.addEventListener(this.eventList.pageChange, (e) => {

            if (this.isDebugMode()) {
                window.console.log(`${this.component}: Step switched...`);
            }

            let step = this.getCurrentStepByPageChangeEvent(e);
            const currentStepButton = document.querySelector(`.step-page-button[data-step-number="${step}"]`);

            if (!currentStepButton) {
                return;
            }

            const allStepButtons = document.querySelectorAll(`.step-page-button[data-step-number]`);
            for (const button of allStepButtons) {
                blurStepButton(button);
            }
            highlightStepButton(currentStepButton);

            this.copyTextFromPreviousStep(step);
        });

        wrapper?.addEventListener(this.eventList.pageChange, async (e) => {
            if (this.isDebugMode()) {
                window.console.log(`${this.component}: Page changed`);
            }

            const step = this.getPreviousStepByPageChangeEvent(e);
            await this.recordHistory(step);
        });

        document.querySelector(this.selectors.expandSelection)?.addEventListener("click", async (e) => {
            if (this.isDebugMode()) {
                window.console.log(`${this.component}: Expand selected text...`);
            }
            const step = this.getStepNumber(e.target);
            const textData = this.getStepTextAreaData(step);

            const text = textData.text;
            const selectedText = textData?.selectedText;

            if (!this.validateInputText(selectedText)) {
                if (this.isDebugMode()) {
                    window.console.warn(`${this.component}: No selection detected`);
                }
                return;
            }

            this.loadingData();

            try {
                const response = await this.api.expandText(
                    this.assignmentId,
                    this.submissionId,
                    text,
                    selectedText,
                    textData.selectionStart
                );
                this.setApiResponseToInput(this.currentStep, response);
                this.updateAIButtonState();
                await this.dispatchHistoryFromInput();
            } catch (exception) {
                await Notification.exception(exception);
            }
        });

        document.querySelector(this.selectors.doAIMagic)?.addEventListener("click", async (e) => {

            if (this.isDebugMode()) {
                window.console.log(`${this.component}: Do AI magic...`);
            }

            const text = this.getStepInputText(this.getStepNumber(e.target));

            if (!this.validateInputText(text)) {
                if (this.isDebugMode()) {
                    window.console.warn(`${this.component}: Input text is empty`);
                }
                return;
            }

            this.loadingData();

            try {
                const response = await this.api.generateText(
                    this.assignmentId,
                    this.submissionId,
                    text,
                    this.defaultStep
                );
                this.attemptsCount++;
                this.setApiResponseToInput(this.currentStep, response);
                this.updateAIButtonState();
                await this.dispatchHistoryFromInput();
            } catch (exception) {
                await Notification.exception(exception);
            }
        });

        document.querySelectorAll('textarea.pxaiwriter-student-data').forEach(async (element) => {
            const step = this.getNumberFromAttribute(element, 'data-input-step');
            if (step < 1) {
                return;
            }
            const checksum = await this.getHashCode(element.value);
            this.setChecksumByStep(step, checksum);
        });
    }

    /**
     * @param {number} step
     * @return {string}
     */
    getChecksumByStep(step) {
        return this.properties.checksumList[step] ?? '';
    }

    /**
     * @param {number} step
     * @param {string} checksum
     */
    setChecksumByStep(step, checksum) {
        this.properties.checksumList[step] = checksum;
    }

    getNumberFromAttribute(element, attribute) {
        const value = element?.getAttribute(attribute);
        if (!value) {
            return undefined;
        }
        const number = Number.parseInt(value);
        return Number.isNaN(number) ? undefined : number;
    }

    /**
     * @param {number} step
     * @return {HTMLTextAreaElement|null}
     */
    getStepInput(step) {
        const element = document.querySelector(`${this.selectors.input}[data-input-step="${step}"]`);
        if (element instanceof HTMLTextAreaElement) {
            return element;
        }
        return null;
    }

    /**
     * @param {number} step
     */
    copyTextFromPreviousStep(step) {

        if (this.isDebugMode()) {
            window.console.log(`${this.component}: Try to copy the text from the previous step to step ${step}...`);
        }

        const currentStepInput = this.getStepInput(step);
        if (currentStepInput === null) {
            if (this.isDebugMode()) {
                window.console.log(`${this.component}: Cannot find the current step ${step}`);
            }
            return;
        }

        if (currentStepInput.value.trim().length !== 0) {
            if (this.isDebugMode()) {
                window.console.log(`${this.component}: Cannot copy because the current step (${step}) is not empty`);
            }
            return;
        }

        const previousStep = step - 1;
        const previousStepInput = this.getStepInput(previousStep);

        if (previousStepInput === null) {
            if (this.isDebugMode()) {
                window.console.log(`${this.component}: Cannot find the previous step ${previousStep}`);
            }
            return;
        }

        if (previousStepInput.value.trim().length === 0) {
            if (this.isDebugMode()) {
                window.console.log(`${this.component}: Cannot copy because the previous step (${step}) is empty`);
            }
            return;
        }

        currentStepInput.value = previousStepInput.value;

        if (this.isDebugMode()) {
            window.console.log(`${this.component}: Copied the text from step ${previousStep} to ${step}`);
        }
    }

    /**
     * @param {string} target
     */
    preventPasting(target) {
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
    }

    /**
     * @return {boolean}
     */
    isDebugMode() {
        return !!M?.cfg?.developerdebug;
    }

    /**
     * @param {string} text
     */
    setRemainingAttemptText(text) {
        if (!text) {
            return;
        }
        const label = document.querySelector('.remaining-ai-attempts');
        if (label instanceof HTMLElement) {
            label.innerHTML = text;
        }
    }

    /**
     * @param {number} step
     * @return {HTMLTextAreaElement|null}
     */
    getStepTextArea(step) {
        return document.querySelector(`textarea[name="pxaiwriter-data-step-${step}"]`);
    }

    /**
     * @param {HTMLElement} element
     * @return {number}
     */
    getStepNumber(element) {
        const currentStep = Number.parseInt(element?.dataset?.step);
        if (Number.isNaN(currentStep)) {
            return 0;
        }
        return currentStep;
    }

    /**
     * @param {number} step
     * @return {TextAreaData|null}
     */
    getStepTextAreaData(step) {
        const textArea = this.getStepTextArea(step);
        if (!(textArea instanceof HTMLTextAreaElement)) {
            return null;
        }
        return {
            text: textArea.value,
            selectedText: textArea.value.substring(textArea.selectionStart, textArea.selectionEnd),
            selectionStart: textArea.selectionStart,
            selectionEnd: textArea.selectionEnd
        };
    }

    /**
     * @param {number} step
     * @return {string}
     */
    getStepInputText(step) {
        return this.getStepTextArea(step)?.value ?? '';
    }

    /**
     * @param {string} text
     * @return {boolean}
     */
    validateInputText(text) {
        if (!text || text.length === 0) {
            $('#title-required-warning-modal').modal('show');
            return false;
        }
        return true;
    }

    loadingData() {
        $(':button').prop('disabled', true);
        $('#loader').removeClass('d-none');
    }

    /**
     * @param {CustomEvent|Event} event
     * @return {number}
     */
    getCurrentStepByPageChangeEvent(event) {
        return event?.detail?.currentStep;
    }

    /**
     *
     * @param {CustomEvent|Event} event
     * @return {number}
     */
    getPreviousStepByPageChangeEvent(event) {
        const currentStep = this.getCurrentStepByPageChangeEvent(event);
        const prevStep = event?.detail?.prevStep;
        return currentStep === prevStep ? 0 : prevStep;
    }

    /**
     * @param {string} text
     * @return {Promise<string>}
     */
    async getHashCode(text) {
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
    }

    /**
     * @param {number} step
     * @param {*} response
     */
    setApiResponseToInput(step, response) {
        $(':button').prop('disabled', false);
        if (response.hasOwnProperty('attempt_text')) {
            this.setRemainingAttemptText(response.attempt_text);
        }
        if (response.hasOwnProperty('data')) {
            const textArea = this.getStepTextArea(step);
            if (textArea instanceof HTMLTextAreaElement) {
                textArea.value = response.data;
                textArea.dispatchEvent(new Event("change"));
            }
        }
        $('#loader').addClass('d-none');
    }

    updateAIButtonState() {
        const buttons = document.querySelectorAll(this.selectors.doAIMagic + ', ' + this.selectors.expandSelection);

        if (this.isDebugMode()) {
            window.console.log(
                `${this.component}: Updating button states, attempt ${this.attemptsCount} of ${this.maxAttempts}...`
            );
        }

        const isDisabled = this.attemptsCount >= this.maxAttempts;

        buttons.forEach((button) => {
            button.disabled = isDisabled;
            button.classList.toggle('d-none', isDisabled);
        });

        document.querySelector(this.selectors.maxAttemptsErrorMessage).classList.toggle('d-none', !isDisabled);
    }

    /**
     * @param {number} step
     * @return {Promise<void>}
     */
    async recordHistory(step) {
        if (this.isDebugMode()) {
            window.console.log(`${this.component}: Saving history...`);
        }

        if (step < 1) {
            if (this.isDebugMode()) {
                window.console.log(`${this.component}: Nothing to be save...`);
            }
            return;
        }
        const text = this.getStepInputText(step);
        const checksum = await this.getHashCode(text);

        if (checksum === this.getChecksumByStep(step)) {
            if (this.isDebugMode()) {
                window.console.log(`${this.component}: Nothing has been changed`);
            }
            return;
        }

        const response = await this.api.recordHistory(this.assignmentId, this.submissionId, text, step);
        this.updateAIButtonState();
        this.setChecksumByStep(step, response.checksum);

        if (this.isDebugMode()) {
            window.console.log(`${this.component}: Input text got recorded`);
        }
    }

    async dispatchHistoryFromInput() {
        const elements = document.querySelectorAll(this.selectors.input);
        for (const element of elements) {
            if (element instanceof HTMLTextAreaElement) {
                await this.recordHistory(this.getStepNumber(element));
            }
        }
    }
}

/**
 * @param {number} assignmentId
 * @param {number} submissionId
 * @param {number} stepNumber
 * @param {number} maxAttempts
 * @param {number} attemptsCount
 * @return {EventCreator}
 */
export const init = (
    assignmentId,
    submissionId,
    stepNumber = 1,
    maxAttempts = 2,
    attemptsCount = 0
) => {
    return new EventCreator(assignmentId, submissionId, stepNumber, maxAttempts, attemptsCount);
};
