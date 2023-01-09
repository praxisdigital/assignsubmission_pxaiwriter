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
- Goto https://\<site>/my/indexsys.php
- Insert the new block
- Turn on editing
- Setup "Instance setting" above and hit save
- Now create a post, with an image as the admin user and set it to a cohort a student can see.
- Log in as a student and validate that the image is visible and the post is viewable.
- If there is needed for change Archive page title
  - Goto http://\<site>**/admin/tool/customlang/**
  - Choose language that you wish to be edit
  - Select **block_pxcohortnews** & type string name **"archive_header"**
    - For the button in the block string name is **"goto_archive_button"**
  - Change language string and save
- Create user profile fields (in order to use department label on the news)
  1. Go to /user/profile/index.php
  2. Create a new "text input" field
  3. Fill short name with "department"
  4. Fill full name after customer see fit (Niels Brock set it as "Tilknyt din afdeling:")

**Database tables:**

- assignsubmission_pxaiwriter - This records the course assignment submissions for the pxai writer

## Release notes

- **v1.0.0.14** (2023010901)
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
