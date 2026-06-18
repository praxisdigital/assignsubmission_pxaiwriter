import $ from 'jquery';
import Template from 'core/templates';

let stepConfig = {};
stepConfig.template = null;
stepConfig.hasUsed = false;
stepConfig.hasRaised = false;
stepConfig.steps = [];

stepConfig.init = async function (stepConfig) {
    this.steps = stepConfig.steps;
    this.hasUsed = stepConfig.hasUsed;

    for(let i = 0; i < this.steps.length;i++){
        let rendered = await Template.render("assignsubmission_pxaiwriter/assignsubmission_pxaiwriter_step_config",this.steps[i]);
        $("#ai_writer_submisson_steps").append(rendered);
    }

    $('#id_assignsubmission_pxaiwriter_enabled').click(function () {
        // -> display/hide aiwriter section upon selecting the ai writer submission checkbox
        $("#ai_writer_submisson_steps_section").toggle(this.checked);
    });

    $("#add_step_btn").toggle(true); // enable add button after rendering the template

    $("#ai_writer_submisson_steps_loader").toggle(false); // hide loader after rendering template

    // set defualt value to the ai writer steps. this is the value stored in the db
    $('input[name="assignsubmission_pxaiwriter_steps"]').val(JSON.stringify(this.steps));

    $('#add_step_btn').click(function(e) {
        e.preventDefault();
        new Promise((resolve, reject) => {
            if (this.hasRaised == false && this.hasUsed == true) {
                $('#steps-change-warning-modal').modal('show');
                $('#confirm-ai-writer-change-action').click(function () {
                    resolve("user clicked");
                });
                $('#confirm-ai-writer-change-action-cancel').click(function () {
                    reject("user clicked cancel");
                });
            } else {
                resolve("user clicked");
            }
        }).then(async () => {
            let newStep = {
                step: this.steps.length + 1,
                description: null,
                mandatory: true,
                type: 'text',
                removable: true,
                readonly: '',
                isreadonly: false,
                ai_element: false,
                ai_expand_element: false,
                value: ""
            };
            this.steps.push(newStep);
            let rendered = await Template.render("assignsubmission_pxaiwriter/assignsubmission_pxaiwriter_step_config",newStep);
            $("#ai_writer_submisson_steps").append(rendered);
            this.hasRaised = true;
        }).catch(() => {
            // Ignore !
        });
    }.bind(this));

    $('#steps-change-warning-modal').click(function() {
        $('#steps-change-warning-modal').modal('hide');
    });

    $('#ai_writer_submisson_steps').on('focus change keyup paste', '.step-des', function (e) {
        new Promise((resolve, reject) => {
            if (this.hasRaised == false && this.hasUsed == true) {
                e.preventDefault();
                $('#steps-change-warning-modal').modal('show');
                $('#confirm-ai-writer-change-action').click(function () {
                    resolve("user clicked");
                });
                $('#confirm-ai-writer-change-action-cancel').click(function () {
                    reject("user clicked cancel");
                });
            } else {
                resolve("user clicked");
            }
        }).then(() => {
            const stepId = $(e.currentTarget).attr('data-id');
            let step = this.steps.find(e => e.step == stepId);
            step.description = $(e.currentTarget).val();
            $('input[name="assignsubmission_pxaiwriter_steps"]').val(JSON.stringify(this.steps));
            this.hasRaised = true;
        }).catch(() => {
            // Ignore !
            console.log("error");
        });
    }.bind(this));

    $('#ai_writer_submisson_steps').on('click', '.remove-btn', function (e) {
        e.preventDefault();
        new Promise((resolve, reject) => {
            if (this.hasRaised == false && this.hasUsed == true) {
                $('#steps-change-warning-modal').modal('show');
                $('#confirm-ai-writer-change-action').click(function () {
                    resolve("user clicked");
                });
                $('#confirm-ai-writer-change-action-cancel').click(function () {
                    reject("user clicked cancel");
                });
            } else {
                resolve("user clicked");
            }
        }).then(async () => {

            const stepId = $(e.currentTarget).attr("data-id");
            this.steps = this.steps.filter((st) => {
                if (st.step != stepId) {
                    return st;
                }
            });

            $("#ai_writer_submisson_steps").empty();

            for (let i = 0; i < this.steps.length; i++) {
                if (stepId && this.steps[i].step > stepId) {
                    this.steps[i].step = this.steps[i].step - 1;
                }
                let rendered = await Template.render("assignsubmission_pxaiwriter/assignsubmission_pxaiwriter_step_config", this.steps[i]);
                $("#ai_writer_submisson_steps").append(rendered);
            }

            $('input[name="assignsubmission_pxaiwriter_steps"]').val(JSON.stringify(this.steps));
            this.hasRaised = true;

        }).catch(() => {
            // Ignore !
        });

    }.bind(this));
};

export default stepConfig;