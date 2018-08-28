# random-code-examples
I can't upload a lot of my work projects to a public repo as my clients would be unhappy but little bits to show as code examples should be ok...

## laravel-multipage-form
These files are from a web based application used by a well known pharmaceutical company.  Clinicians enter details of their clinic via a series of validated input pages and then they can compare that data against other clinics in the system based on user selected filters.  The data is represented via a series of different charts generated using ChartJS.  They can also export the reports in PDF format or print them using a tailored print query layout.  For this sample I have included:

* The main controller PHP files for the input forms
* The database seeder which generates fairly complex test data using the Faker module
* A plugin I wrote for the ChartJS plugin that draws custom backgrounds behind the charts
* The main blade file used for the clinicians interface HTML generation

## wordpress-image-color-picker-plugin
This is the main class file from a WordPress plugin I wrote.  In the WordPress admin the user can select an image from the gallery and the plugin will pull the most prominent colors from it.  The user can customise these colors or just go with the ones that the system picks.  The selection is saved to the database and then used on the front end to allow a website to dynamically change its color theme to match the selected background image.
