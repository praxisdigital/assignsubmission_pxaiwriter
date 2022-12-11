test = {};
test.template = null;
test.steps = [];

test.init = function(config, stepConfig) {

    this.template = stepConfig.template;
    this.steps = stepConfig.steps;

    this.steps.forEach(step => {
        let rendered = Mustache.render(this.template,step);
        $("#submisson_steps").append(rendered);
    });
    
    $('#id_assignsubmission_aiwriter_enabled').click(function() {
        $("#submisson_steps_section").toggle(this.checked); // -> display/hide aiwriter section upon selecting the ai writer submission checkbox
    });

    $("#add_step_btn").toggle(this.checked); // enable add button after rendering the steps

    $('input[name="assignsubmission_aiwriter_steps"]').val(JSON.stringify(this.steps)); // set defualt value to the ai writer steps. this is the value stored in the db

    $('#add_step_btn').click(function(e) {
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
        $("#submisson_steps").append(rendered);
    }.bind(this));

    $('#submisson_steps').on('click', '.remove-btn', function(e) {
        e.preventDefault();
        const stepId = $(e.currentTarget).attr('data-id');
        alert(stepId);
        this.steps = this.steps.filter(e => {
            console.log(e.step);
            if (e.step != stepId) {
                return e;
            }
        });
        console.log(this.steps);
        $("#submisson_steps").empty();
        this.steps.forEach(step => {
            if(stepId && step.step > stepId) {
                step.step = step.step-1;
            }
            let rendered = Mustache.render(this.template,step);
            $("#submisson_steps").append(rendered);
        });
        $('input[name="assignsubmission_aiwriter_steps"]').val(JSON.stringify(this.steps));
    }.bind(this));

    // var addRemoveBehavior = function(id) {
    //     alert(this.steps);
    //     alert(id);
    //     $(id).click(function(e) {
    //         // e.preventDefault();
    //         // const stepId = $(e.currentTarget).attr('data-id');
    //         // alert(stepId);
    //         // this.steps = this.steps.filter(e => e.step !== stepId);
    //         // console.log(this.steps);
    //         // $("#submisson_steps").empty();
    //         // //createElements(steps, template, stepId);
    
    //         // this.steps.forEach(step => {
    //         //     if(stepId && step.step > stepId) {
    //         //         step.step = step.step-1;
    //         //     }
    //         //     let rendered = Mustache.render(this.template,step);
    //         //     console.log(rendered);
    //         //     $("#submisson_steps").append(rendered);
    //         //     id = '#remove_'+step.step;
    //         //     addRemoveBehavior(id);
    //         // });
    //     }.bind(this));
    // }

    // $('#remove_3').click(function(e) {
    //     alert(123);
    //     console.log(1234);
    //     e.preventDefault();
    //     const stepId = $(e.currentTarget).attr('data-id');
    //     console.log("id",stepId);
    //     const stepConfig = this.steps.find(e => e.step == stepId);
    //     console.log("step",stepConfig);
    //     const index = this.steps.indexOf(stepConfig);
    //     this.steps.splice(index,1);
    //     console.log(this.steps);

    //     $("#submisson_steps").empty();

    //     this.steps.forEach(step => {
    //         if(step.step > stepId) {
    //             step.step = step.step-1;
    //         }
    //         let rendered = Mustache.render(this.template,step);
    //         console.log(rendered);
    //         $("#submisson_steps").append(rendered);
    //     });
    // }.bind(this));

//     const el = document.getElementById('remove_3');
//     el.addEventListener("click", function(e) {
//         alert(123);
//         console.log(1234);
//         e.preventDefault();
//     })
}

// test.createElements = function(steps, template, stepId) {
//     steps.forEach(step => {
//         if(stepId && step.step > stepId) {
//             step.step = step.step-1;
//         }
//         let rendered = Mustache.render(template,step);
//         console.log(rendered);
//         $("#submisson_steps").append(rendered);
//         const id = '#remove_'+step.step;
//         addRemoveBehavior(id, steps, template);
//     });
// }.bind(this);

// test.addRemoveBehavior = function(this) {
//     alert(this.steps);
//     alert(this.processId);
//     $(this.processId).click(function(e) {
//         e.preventDefault();
//         const stepId = $(e.currentTarget).attr('data-id');
//         alert(stepId);
//         this.steps = this.steps.filter(e => e.step !== stepId);
//         console.log(this.steps);
//         $("#submisson_steps").empty();
//         //createElements(steps, template, stepId);

//         this.steps.forEach(step => {
//             if(stepId && step.step > stepId) {
//                 step.step = step.step-1;
//             }
//             let rendered = Mustache.render(this.template,step);
//             console.log(rendered);
//             $("#submisson_steps").append(rendered);
//             const id = '#remove_'+step.step;
//             this.addRemoveBehavior(this);
//         });
//     }.bind(this));
// }.bind(this);