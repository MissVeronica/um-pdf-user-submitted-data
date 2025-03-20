# UM PDF User Submitted data - version 2.0.0
Extension to Ultimate Member for creating PDF files with Submitted User Registration Data and an option to attach PDF file links to notification emails.

## UM Settings -> Extensions -> Submitted PDF
* PDF User Submitted data Enable - Enable/disable the extension for creating PDF with Submitted User Registration Data.
* PDF User Submitted data Recreate - Recreate non-existent PDF files with Submitted User Registration Data for Users.
* -> PDF file recreation number of Users - Select number of Users in each batch of PDF file creations. - Find the seach/create result at the checkbox description line above. - Find total without PDF
* -> Admin email enable - Send an email to site admin with batch PDF creation summary.
* PDF Header text - The header text may include HTML allowed for WP posts.
* PDF Footer text - The footer text may include HTML allowed for WP posts.
* PDF pre text comment - The comment text before the submitted fields may include HTML allowed for WP posts.
* PDF post text comment - The comment text after the submitted fields may include HTML allowed for WP posts.
* Compress submitted text - Enable compressed mode ie remove extra blank lines between fields in the PDF text.
* Include Page Numbers - Enable "Page Number of Number of Pages" at the bottom right corner of each PDF page.
* Page in "A4" format - Enable Page in "A4" format instead of the default North America standard "letter" format.
* URL link text for the email placeholder - Link text for: {pdf_submitted_link}, default if field is empty; "PDF file"
* PDF external font Enable - Enable/disable external fonts for PDF.
* -> PDF External font URL - Enter external URL address like Google fonts.
* -> PDF External font family - The font family to use from the external URL.
* PDF reader core font family - Select one of: Courier, DejaVu Sans, DejaVu Serif, DejaVu Sans Mono, Helvetica, Times.
* PDF reader core or external font size - Use this font size regardless of font family select from 6px to 26px.
### PDF file recreation
Enabling this setting will find number of users without a Submitted PDF file (empty meta_key value) and you have also an option to create the Submitted PDF file for these Users in the speed you want from 1 at a time to all in one batch job. Note that this is a long time job and will give a high impact on your site's performance. For each batch job you will get an email sent to site admin with User IDs being updated and any more info about errors fixed or not fixed.
### Site admin email example
Status PDF file creation at [site name] 2025/03/19

1 Users without submitted PDF files got PDF created and total remaining Users are now 1318, Found empty 9 - error 0 - fixed 1

Created PDF files for user IDs 96

Failure unknown error in submitted field for user IDs

Empty submitted field for user IDs 3 6 7 10 11 12 13 14 16

Fixed submitted field format for user IDs 96
## UM Email placeholder - option
1. Notification email placeholder: {pdf_submitted_link} with link text from settings of "URL link text for the email placeholder"
2. Example: <code>Link to <a href="https://.../wp-content/uploads/ultimatemember/ID/file_submitted_HASH.pdf" target="_blank" rel="noreferrer">your submitted form</a> PDF</code>

## UM Form Builder Shortcode
Display of the Submitted PDF by the shortcode [show_submitted_pdf icon="fas fa-file-pdf"]URL link text[/show_submitted_pdf]

## UM meta_key
1. The meta-key used for storing the submitted PDF file name is: um_pdf_submitted
2. Submitted PDF file is saved in the UM upload directory and each user ID folder with file names like: file_submitted_HASH.pdf where the HASH is 40 characters long.

## Reference
The dompdf HTML to PDF converter code library version 3.1.0 is being used for creating the PDF source. https://github.com/dompdf/dompdf/wiki

## Updates
Version 2.0.0 date 2025-03-20 Changed from being an email placeholder function to be a Registration function and updates of Users without a submitted PDF file.

## Installation
1. Download the plugin ZIP file at the green Code button
2. Install as a WP Plugin, activate the plugin.
3. Copy any formatting HTML of Headers etc in Version 1.1.0 as version 2.0.0 is using another UM options storage. 
4. Deactivate and Delete the version 1.1.0 of the plugin.

