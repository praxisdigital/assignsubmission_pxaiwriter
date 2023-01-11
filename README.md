# Name: Praxis Cohort News

## Type

Submission

## Dependencies

See [version.php](version.php)

## Description

AI text submission plugin that allows admins to define submission scope of steps, and students to generate AI assisted content based on assignment title.

## Global settings

PXAIWriter requires some setup to be complated before used, forgetting this step will cause the plugin not to work as supposed to be. Here, its mandatory that you complete all the sections Open API request settings, Open API text comparer settings and Assignment Settings.
You can access these set of settings here : https://<site>/admin/settings.php?section=assignsubmission_pxaiwriter.

- URL **(url)**
- Content Type **(content_type)**
- Dashboard view mode **(view_news_mode)**
- Authorization **(authorization)**
- Model **(model)**
- Temperature **(temperature)**
- Max tokens **(max_tokens)**
- Top p **(top_p)**
- Frequency Penalty **(frequency_penalty)**
- Precence penalty **(presence_penalty)**
- API key **(api_key)**
- Last modified by **(last_modified_by)**

- Granularity **(granularity)**

- Attempt count **(attempt_count)**

## Setup

- Install plugin
- Go to https://<site>/admin/settings.php?section=assignsubmission_pxaiwriter and complete the settings and save. 
- Go to the Course view and add a new assignment. On the edit view, you can choose 'AI writer submission' as submission type. 
- Change/add steps. You cannot delete the mandatory (frist two) steps.  
- You may select 'Annotate PDF' feedback type, in case you may want to see the AI generated text vs final submission text by the student.
- Either click on 'Save and return to course' or 'Save and display' button to save the submission configuration.


**Database tables:**

- assignsubmission_pxaiwriter - This records the course assignment submissions for the pxai writer.
- pxaiwriter_api_attempts - This records the API endpoint attempts by users by the assignment.

## Release notes

- **v1.0.0.16** (2023011001)
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

- All AJAX files is protected by login.
- Privacy API implemented
