
export default class AISubmissionPageChangeEvent {
    /** @type {CustomEvent|Event} */
    #event;

    /**
     * @param {Event} event
     */
    constructor(event= null) {
        this.#event = event;
    }

    /**
     * @return {number}
     */
    get currentStep() {
        return AISubmissionPageChangeEvent.#getStepNumber(this.#event?.detail?.currentStep);
    }

    /**
     * @return {number}
     */
    get previousStep() {
        return AISubmissionPageChangeEvent.#getStepNumber(this.#event?.detail?.prevStep);
    }

    static #getStepNumber(value) {
        const stepNumber = Number.parseInt(value);
        if (Number.isNaN(stepNumber)) {
            return -1;
        }
        return stepNumber;
    }
}
