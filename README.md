# Name: Praxis Cohort News

## Type
Block

## Dependencies
See [version.php](version.php)

## Description
News block for the dashboard, enabling news to be visible only for specified
cohorts. The author of the post can choose to notify all users in the cohorts
the post is for.

## Global settings
Setting are describe in language string file, and they can also be found in the admin settings.
In the block you have to setup some settings: https://\<site>/admin/settings.php?section=blocksettingpxcohortnews.

* Max. news posts **(max_view_posts)**
* Min. news posts **(min_view_posts)**
* Dashboard view mode **(view_news_mode)**
    * Show all latest news **(option_view_news_mode_default)**
    * Show only latest unread news **(option_view_news_mode_unread_news)**
* Manually "mark as read" **(manually_mark_as_read)**
* Show limit button **(show_post_limit)**
* Show author (Dashboard) **(show_post_author_name_default)**
* Show author (Archive) **(show_post_author_name_archive)**
* Show cohort (Dashboard) **(show_post_cohorts_default)**
* Show cohort (Archive) **(show_post_cohorts_archive)**
* Show date (Dashboard) **(show_post_date_default)**
* Show date (Archive) **(show_post_date_archive)**
* Show department (Dashboard) **(show_post_department_default)**
* Show department (Archive) **(show_post_department_archive)**
* Show image (Dashboard) **(show_image_on_frontpage)**
* Choose default language for the archive page **(default_language_for_admin_archive)**
* Choose image height **(header_image_size)**
* Display banner image **(header_image_visibility)**
* Upload banner image **(header_background_image)**

## Instance settings
- Goto instance settings
- Set the following settings:

- Original block location
System (cannot be set in Moodle, but should be system - if not then you have to change it directly in database)

- Display on page types
Dashboard page (should be correct, since you put this in /my/indexsys.php)

- Select pages
Any page matching the above

- Default region
content

- Default weight
0

## Capabilities

* **block/pxcohortnews:myaddinstance**
    * Add a new cohort news group block to "My Moodle Page"

* **block/pxcohortnews:addinstance**
    * Add a new cohort news group block

* **block/pxcohortnews:dashboard_view_all_posts**
    * View all news posts in the Dashboard

* **block/pxcohortnews:dashboard_view_author_and_cohort_posts**
    * View news posts that user have created or have cohort memberships in the Dashboard

* **block/pxcohortnews:dashboard_add_post**
    * Show "Add new post" button in the Dashboard

* **block/pxcohortnews:archive_view_all_posts**
    * View all news posts in the Archive

* **block/pxcohortnews:archive_view_author_and_cohort_posts**
    * View news posts that user have created or have cohort memberships in the Archive

* **block/pxcohortnews:archive_allow_filter_all_cohorts**
    * Allow to search all cohorts in the Archive

* **block/pxcohortnews:archive_allow_filter_sender**
    * Allow to filter on senders in the Archive

* **block/pxcohortnews:archive_allow_filter_department**
    * Allow to filter on departments in the Archive

* **block/pxcohortnews:create_post**
    * Allow to create news posts to their own cohorts

* **block/pxcohortnews:create_post_all_cohorts**
    * Allow to create news posts to all cohorts

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
- block_pxcohortnews - the table with all the news
- block_praxis_cohort_pivot - a table for keeping track of which news belongs to which cohorts
- block_praxis_cohort_read - defines if a user has read a given post

## Release notes
- **1.6.7** (2022050100)
    - Added privacy provider
- **1.6.6** (2022021500)
    - Fixed: when user tries to create a new post in the atto editor, it does not clear the content and will remember the draft content from previous action.
      - Cause by atto editor auto save feature. 
- **...** ($version)
    - Some commits
- **1.0.0** (2018111302)
    - First version of the plugin

## GDPR
- All AJAX files is protected by login.
- All AJAX files has been made so userid can not be passed as argument, so you cannot set og get information about other users.
- Privacy API implemented