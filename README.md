# Name: Praxis AI Writer

## Type

Submission

## Dependencies

See [version.php](version.php)

## 3rd-party library dependencies
* [php-htmldiff](https://github.com/caxy/php-htmldiff)

## Description

AI text submission plugin that allows admins to define submission scope of steps, and students to generate AI assisted content based on assignment title.

## Global settings

Praxis AI writer requires some setup to be completed before used, forgetting this step will cause the plugin not to work as supposed to be. Here, its mandatory that you complete all the sections Open API request settings, Open API text comparer settings and Assignment Settings.
You can access these set of settings here : https://<site>/admin/settings.php?section=assignsubmission_pxaiwriter.

- OpenAI API token **(openai_token)**
- Model **(model)**
- Temperature **(temperature)**
- Max tokens **(max_tokens)**
- Top p **(top_p)**
- Frequency Penalty **(frequency_penalty)**
- Presence penalty **(presence_penalty)**
- Last modified by **(last_modified_by)**
- Attempt count **(attempt_count)**

## Setup

- Install plugin
- Go to https://<site>/admin/settings.php?section=assignsubmission_pxaiwriter and complete the settings and save. 
- Go to the Course view and add a new assignment. On the edit view, you can choose 'AI writer submission' as submission type. 
- Change/add steps. You cannot delete the mandatory (first two) steps.  
- You may select 'Annotate PDF' feedback type, in case you may want to see the AI generated text vs final submission text by the student.
- Either click on 'Save and return to course' or 'Save and display' button to save the submission configuration.

## Release notes
- **1.4.0** (2024022300)
  - New feature:
    - Add new models
      - "gpt-4"
      - "gpt-4-turbo-preview"
    - Remove deprecated models
      - "text-davinci-003"
      - "text-davinci-002"
- **1.3.2** (2023100600)
  - Fixes:
    - Fix user unable to delete the assignment when AI writer submission is enabled.
- **1.3.1** (2023091501)
  - Changes:
    - Add privacy provider for the history records.
    - Add step guide information for the AI step.
    - Change the design of step number in submission edit view.
      - Add highlight around the step number and the summary text.
- **1.3.0** (2023090800)
  - Changes:
    - Switched text diff library from d4h/finediff to caxy/php-htmldiff
  - Fixes:
    - Fixed issue with text diff not working properly.
    - Fixed Moodle 4.1 assignfeedback_editpdf cause an error
      when submission_created & submission_updated event doesn't set the assignment instance to the event.
      After the event got triggered.
    
- **1.2.1** (2023071800)
  - Changes:
    - Removed assignment due date validation.
- **1.2.0** (2023060100)
  - New feature:
    - Add new AI model "GPT-3.5-turbo" to the plugin.
  - Changes:
    - Removed API URL settings.
    - Change from "Authorization" to "OpenAI API token".
- **1.1.0** (2023040301)
  - New feature:
    - Record the history when user press AI text generator "Do AI magic" and the next button.
      - The history will show in the grading overview as step 1.1, 1.2 etc. 
    - Add step number overview that can indicate which step user is current on and tell the user how many step there is.    
- **1.0.0.23** (2023013100)
  - Bug fix: When "Do AI magic" is pressed the entire content of the text area needs to be sent together with the prompt
  - Bug fix: Missing language string for "Expand Selection"
  - Removed input field for Do AI Magic
  - Removed multiple linebreaks in the input whenever AI magic is done
  - Added a loading icon to display while fetching AI data
- **1.0.0.22** (2023012001)
  - Cleaning up typos and unutilized text 
  - Bug fix: Steps config doesn't allow to paste text
- **1.0.0.20** (2023011900)
  - Admin settings content type removed
  - Added API attempt count visible for students
- **1.0.0.17** (2023011001)
  - Admin settings Open API request settings
  - Admin settings Open API text comparer settings 
  - Admin settings Assignment Settings 
  - Assignment submission admin view for creating dynamic steps
  - Assignment submission student view for inserting and dynamically generating the student steps
  - Open API integration. Generate and expand selection
  - Back-up and re-store submissions data
- **1.0.0** (2022121400)
  - First version of the plugin

## GDPR

- All AJAX files protected by login.
- Privacy API implemented
