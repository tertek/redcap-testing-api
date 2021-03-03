# REDCap Testing API
Extends the REDCap API to expose system and server info for enabling automated E2E-Testing with Cypress. This module is made to be used with **Cypress REDCap**. [Learn more about Cypress REDCap](#tbd).

## Setup

- Install REDCap Testing API external module via [Official REDCap Module Repository](https://redcap.vanderbilt.edu/consortium/modules/)
- Enable REDCap Testing API external module in Control Center
- Navigate to the REDCap Testing API module page which has been created in the Control Center under "External Modules" by clicking on "Testing API"
- Copy & Paste environment variables into your **Cypress REDCap** environment or use them with an HTTP Client such as Postman for API testing

![image](https://user-images.githubusercontent.com/75415872/109808215-20c0b100-7c27-11eb-82e1-071abc8ca9d5.png)


*Info for HTTP Client Testing* 
<br>The full path to the API endpoint is `<APP_PATH_FULL>/?NOAUTH&type=module&prefix=testing_api&page=endpoint`

## Example `json response`

```json
{
    "configuration": {
        "redcap_version": "10.6.9",
        "api_enabled": "1",
        "is_development_server": "0",
        "language_global": "Deutsch",
        "project_language": "English",
        "homepage_contact_email": "email@yoursite.edu",
        "project_contact_email": "email@yoursite.edu"
    },
    "projects": [
        {
            "project_id": "14",
            "project_name": "project_1",
            "status": "0",
            "project_language": "Deutsch",
            "surveys_enabled": "1"
        },
        {
            "project_id": "15",
            "project_name": "project_2",
            "status": "0",
            "project_language": "English",
            "surveys_enabled": "0"
        }
    ],
    "external_modules": [
        {
            "external_module_id": "1",
            "directory_prefix": "admin_dash"
        },
        {
            "external_module_id": "2",
            "directory_prefix": "vanderbilt_auto_record_generation"
        }
    ]
  }
```
