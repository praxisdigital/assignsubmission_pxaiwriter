stepConfig = {};
stepConfig.template = null;
stepConfig.hasUsed = false;
stepConfig.hasRaised = false;
stepConfig.steps = [];

stepConfig.init = function(config, stepConfig) {

    this.template = stepConfig.template;
    this.steps = stepConfig.steps;
    this.hasUsed = stepConfig.hasUsed;

    this.steps.forEach(step => {
        let rendered = Mustache.render(this.template,step);
        $("#ai_writer_submisson_steps").append(rendered);
    });
    
    $('#id_assignsubmission_pxaiwriter_enabled').click(function() {
        $("#ai_writer_submisson_steps_section").toggle(this.checked); // -> display/hide aiwriter section upon selecting the ai writer submission checkbox
    });

    $("#add_step_btn").toggle(true); // enable add button after rendering the template

    $("#ai_writer_submisson_steps_loader").toggle(false); // hide loader after rendering template

    $('input[name="assignsubmission_pxaiwriter_steps"]').val(JSON.stringify(this.steps)); // set defualt value to the ai writer steps. this is the value stored in the db

    $('#add_step_btn').click(function(e) {
        raiseValidator();
        const newStepId = this.steps.length + 1;
        e.preventDefault();
        let newStep = {
            step : this.steps.length + 1,
            description : null,
            mandatory : true,
            type : 'text',
            removable : true
        };
        this.steps.push(newStep);
        let rendered = Mustache.render(this.template,newStep);
        $("#ai_writer_submisson_steps").append(rendered);
    }.bind(this));

    var raiseValidator = function() {
        if (this.hasRaised == false && this.hasUsed == true) {
            $('#steps-change-warning-modal').modal('show');
            this.hasRaised = true;
        }
    }.bind(this);

    $('#steps-change-warning-modal').click(function(e) {
        $('#steps-change-warning-modal').modal('hide');
    });

    $('#ai_writer_submisson_steps').on('change keyup paste', '.step-des', function(e) {
        raiseValidator();
        const stepId = $(e.currentTarget).attr('data-id');
        let step = this.steps.find(e => e.step == stepId);
        step.description = $(e.currentTarget).val();
        $('input[name="assignsubmission_pxaiwriter_steps"]').val(JSON.stringify(this.steps));
    }.bind(this));

    $('#ai_writer_submisson_steps').on('click', '.remove-btn', function(e) {
        e.preventDefault();
        raiseValidator();
        const stepId = $(e.currentTarget).attr("data-id");
        this.steps = this.steps.filter((st) => {
            if (st.step != stepId) {
                return st;
            }
        });
        $("#ai_writer_submisson_steps").empty();
        this.steps.forEach((step) => {
            if (stepId && step.step > stepId) {
                step.step = step.step - 1;
            }
            let rendered = Mustache.render(this.template, step);
            $("#ai_writer_submisson_steps").append(rendered);
        });
        $('input[name="assignsubmission_pxaiwriter_steps"]').val(JSON.stringify(this.steps));
    }.bind(this));
}