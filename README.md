# UM PDF User Submitted data
Extension to Ultimate Member for creating PDF files with Submitted User Registration Data and attach PDF file links to Emails.

## Installation

1. Download zip file. 
2. Unzip and upload as a plugin to Wordpress the zip file um-pdf-submitted.zip
3. Activate the plugin
4. The PDF file creation is enabled per email template
5. Modify the email template you want to have a link to the user submitted PDF file and add the placeholder {pdf_submitted_link}
6. Add to the email template page your Header and Footer text for the PDF page (may include HTML allowed for WP posts)
7. Add to the email template page your PDF URL link text and select PDF font family and font size
8. The meta-key used for storing the PDF file name is: um_pdf_submitted
9. Location for storing the PDF file is the users uploads folder.
10. The PDF file can be viewed in the UM User Profile by adding a field with the the meta-key um_pdf_submitted

## Reference

The dompdf software is being used for creating the PDF source.

https://github.com/dompdf/dompdf/wiki
