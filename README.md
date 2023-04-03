# Name: Praxis AI Writer

## Type

Submission

## Dependencies

See [version.php](version.php)

## Description

AI text submission plugin that allows admins to define submission scope of steps, and students to generate AI assisted content based on assignment title.

## Global settings

PXAIWriter requires some setup to be completed before used, forgetting this step will cause the plugin not to work as supposed to be. Here, its mandatory that you complete all the sections Open API request settings, Open API text comparer settings and Assignment Settings.
You can access these set of settings here : https://<site>/admin/settings.php?section=assignsubmission_pxaiwriter.

- URL **(url)**
- Authorization **(authorization)**
- Model **(model)**
- Temperature **(temperature)**
- Max tokens **(max_tokens)**
- Top p **(top_p)**
- Frequency Penalty **(frequency_penalty)**
- Presence penalty **(presence_penalty)**
- API key **(api_key)**
- Last modified by **(last_modified_by)**

- Granularity **(granularity)**

- Attempt count **(attempt_count)**

## Setup

- Install plugin
- Go to https://<site>/admin/settings.php?section=assignsubmission_pxaiwriter and complete the settings and save. 
- Go to the Course view and add a new assignment. On the edit view, you can choose 'AI writer submission' as submission type. 
- Change/add steps. You cannot delete the mandatory (first two) steps.  
- You may select 'Annotate PDF' feedback type, in case you may want to see the AI generated text vs final submission text by the student.
- Either click on 'Save and return to course' or 'Save and display' button to save the submission configuration.


**Database tables:**

- assignsubmission_pxaiwriter - This records the course assignment submissions for the pxai writer.
- pxaiwriter_api_attempts - This records the API endpoint attempts by users by the assignment.

## Release notes
- **v1.1.0** (2023040300)
  - New feature:
    - Add step number overview that can indicate which step user is current on and tell the user how many step there is.    
- **v1.0.0.23** (2023013100)
  - Bug fix: When "Do AI magic" is pressed the entire content of the text area needs to be sent together with the prompt
  - Bug fix: Missing language string for "Expand Selection"
  - Removed input field for Do AI Magic
  - Removed multiple linebreaks in the input whenever AI magic is done
  - Added a loading icon to display while fetching AI data
- **v1.0.0.22** (2023012001)
  - Cleaning up typos and unutilized text 
  - Bug fix: Steps config doesn't allow to paste text
- **v1.0.0.20** (2023011900)
  - Admin settings content type removed
  - Added API attempt count visible for students
- **v1.0.0.17** (2023011001)
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
