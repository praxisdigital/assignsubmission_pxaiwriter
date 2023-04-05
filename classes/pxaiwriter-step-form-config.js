stepConfigForm = {};
stepConfigForm.steps = [];
stepConfigForm.currentStep = 1;

stepConfigForm.init = function (config, stepConfig) {

    // this.template = stepConfig.template;
    this.steps = stepConfig.steps;
    this.backStepContent = "";


    $(".actions-container").on("click", '#pxaiwriter-expand-selection', function (e) {
        this.steps.forEach((element, index) => {
            if (this.steps[index]['step'] != this.currentStep) {
                this.steps[index]['value'] = "";
            }
        });
    }.bind(this));

    $(".actions-container").on("click", '#pxaiwriter-do-ai-magic', function (e) {
        this.steps.forEach((element, index) => {
            if (this.steps[index]['step'] != this.currentStep) {
                this.steps[index]['value'] = "";
            }
        });
    }.bind(this));

    $("#pxaiwriter-input-steps-component").on("click", '#go-back', function () {
        let currentVal = $('[name="pxaiwriter-data-step-' + (this.currentStep - 1) + '"]').val();
        changeCurrentStep(-1);
        setElementsVisibility();
    }.bind(this));

    $("#pxaiwriter-input-steps-component").on("click", '#advance', function () {
        let currentVal = $('[name="pxaiwriter-data-step-' + this.currentStep + '"]').val();

        if (this.currentStep == 1) {
            currentVal = currentVal.replace(/((?:\r\n?|\n)+)$|(?:\r\n?|\n){2,}/g, '\n\n');
        }

        changeCurrentStep(1, currentVal);
        setElementsVisibility();
    }.bind(this));

    $('.steps textarea').bind('mouseup mousemove', function (e) {
        var innerHeight = $(e.target).height();
        const elements = $("textarea").each(function (index, item) {
            $(item).height(innerHeight);
        });
    }.bind(this));

    $('.pxaiwriter-student-data').bind('paste keyup keypress blur change', function (e) {

        const currentStep = $(e.target).data("input-step");
        const value = $(e.target).val();
        this.steps[currentStep - 1]['value'] = value;
        $('[name="assignsubmission_pxaiwriter_student_data"]').val(JSON.stringify(this.steps));

    }.bind(this));

    var setSaveContent = function () {

    }.bind(this);

    const triggerPageChangeEvent = (newStep, oldStep = 0) => {
        window.console.log("Page changed");
        var wrapper = document.querySelector('.assignsubmission_pxaiwriter');
        if (wrapper instanceof HTMLElement) {
            window.console.log(wrapper);
            var stepPageChange = new CustomEvent('page-change', {
                detail: {
                    prevStep: oldStep,
                    currentStep: newStep
                }
            });
            wrapper.dispatchEvent(stepPageChange);
        }
    }

    var changeCurrentStep = function (incoming, value = null) {
        var oldStep = this.currentStep;
        var newStep = oldStep + incoming;
        if (!(this.steps.length > (newStep)) || !((newStep) < 1)) {
            this.currentStep = newStep;
        }
        if (value) {
            if (!this.steps[this.currentStep - 1]['value']) {
                $('[name="pxaiwriter-data-step-' + this.currentStep + '"]').val(value);
                this.steps[this.currentStep - 1]['value'] = value;
                $('[name="assignsubmission_pxaiwriter_student_data"]').val(JSON.stringify(this.steps));
            }
        }
    }.bind(this);

    var setElementsVisibility = function () {
        this.steps.forEach(element => {
            if (element['step'] == this.currentStep) {
                let elements = document.querySelectorAll('[data-step="' + element['step'] + '"]');
                elements.forEach(e => { // meka pennanna
                    // element.classList.add("mystyle");
                    e.classList.remove("d-none");
                });
            } else {
                let elements = document.querySelectorAll('[data-step="' + element['step'] + '"]');
                elements.forEach(e => {
                    e.classList.add("d-none");
                    // element.classList.remove("mystyle");
                });
            }
        });

        let back = document.querySelectorAll('[id="go-back"]');
        let advance = document.querySelectorAll('[id="advance"]');

        if (this.currentStep == 1) {
            back.forEach(e => {
                e.classList.add("d-none");
            });
            advance.forEach(e => {
                e.classList.remove("d-none");
            });
        }

        if (this.currentStep > 1) {
            back.forEach(e => {
                e.classList.remove("d-none");
            });
            advance.forEach(e => {
                e.classList.remove("d-none");
            });
        }

        if (this.currentStep == this.steps.length) {
            advance.forEach(e => {
                e.classList.add("d-none");
            });
            back.forEach(e => {
                e.classList.remove("d-none");
            });
        }

        triggerPageChangeEvent(this.currentStep);

    }.bind(this);

    setElementsVisibility();
}