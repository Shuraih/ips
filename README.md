# Assignment

Create an API endpoint that adds the correct “Start module reminder” tag to Infusionsoft contact based on order in which they bought courses and their progress in modules. 

Order is always starting from first bought course (first in Infusionsoft field “products”) and first module of it, to last course and last module of it. 

i.e. If customer has bought “ipa,iaa”, then order is:
IPA M1, IPA M2, ... , IPA M7, IAA M1, IAA M2, … , IAA M7

Module completion order doesn't matter.

Decision rules:
1. If no modules are completed - attach first tag in order.
2. If any of first course modules are completed - attach next uncompleted module after the last completed of the first course. (e.g.. M1, M2 & M4 are completed, then attach M5 tag)
3. If all (or last) first course modules are completed - attach next uncompleted module after the last completed of the second course. Same applies in case of a third course.
4. If all (or last) modules of all courses are completed - attach “Module reminders completed” tag.

This **API Endpoint should**:
1. Expect HTTP POST to “/api/module_reminder_assigner” request with “contact_email” as a parameter
2. Calculate & Add correct tag to the customer in Infusionsoft
3. Return JSON response with “success” parameter that is either true or false & “message” 

To get “Start module reminders” tag Ids use *getAllTags() InfusionsoftHelper* function.
**Please store them in a database** - they won't change and shouldn't be requested from Infusionsoft on each call.

Also please write **Feature/HTTP tests** (https://laravel.com/docs/5.6/http-tests) that **cover each different variation of the possible scenarios**. Use Mocking to avoid external API calls from InfusinosoftHelper.

Feel free to adjust any part of the project according to your needs while maintaining clear, **simple and efficient structure**.