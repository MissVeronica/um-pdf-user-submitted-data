# UM PDF User Submitted data - version 2.0.0 soon
Extension to Ultimate Member for creating PDF files with Submitted User Registration Data and an option to attach PDF file links to notification emails.

## Installation

1. Currently nothing to install

## Email Settings

1. The PDF file creation is enabled per email template
2. Modify the email template you want to have a link to the user submitted PDF file and add the placeholder {pdf_submitted_link}

## PDF Page Settings

1. Header, Footer and Comment text fields may include HTML allowed for WP posts
2. Placeholder link text
3. Select either PDF reader core font family or external font family via URL and font family
4. Select font size for both font family cases

## UM Settings

1. The meta-key used for storing the PDF file name is: um_pdf_submitted
2. Location for storing the PDF file is the users file uploads folder.
3. The PDF file can be viewed in the UM User Profile by adding a field with the the meta-key um_pdf_submitted

## Reference

The dompdf HTML to PDF converter code library version 2.0.0 is being used for creating the PDF source.

https://github.com/dompdf/dompdf/wiki

## Updates

Version 1.1.0 2022-09-27: Update of dompdf to version 2.0.1

