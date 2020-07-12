Suggested pre-requisites
------------------------
1. Install Imagemagick
2. Install Ghostscript

3. Add following directories and chmod 777 them:

	//uploads/pageflip/pdfs
	/uploads/pageflip/images
	uploads/pageflip/covers
	/uploads/pageflip/binder

--------------------------------------------------------------------------------------

Suggested addition to .htaccess file to allow big uploads
----------------------------------------------------------

php_value upload_max_filesize 20M
php_value post_max_size 20M
php_value max_execution_time 9993600
php_value max_input_time 9993600



Instructions on how to Add page flips
--------------------------------------------------------------------------------------
1. Go to Pageflip Admin 
2. Click on Brochure manager to set up a pageflip
2.1. Input Name, Description, Category, No of Pages, Image sizes, Pageflip height, Resolution
2.2. Click on Modify
3. Click Upload images to add images to the flip book
3. Ensure correct flip book has been selected from drop down box first and then click Select
4. To convert PDF to images for PDF book, click on Convert PDF and then: 
4.1. Ensure correct flip book has been selected from drop down box first and then click Select
4.2 Upload PDF file (you can upload more than one)
4.3 Select the you wish to convert first
4.4 Input page prefix if there is more than pdf
4.5 Click on "Convert PDF"
5. To Publish Flip book, click on Publish and then
5.1. Ensure correct flip book has been selected from drop down box first and then click  
5.2 Assign images to each page of the book
5.3 Click on Save pages and Publish to save the order of the pages and to Publish the Flip book Else just click on Publish if the page order is already correct.
6. Now view here: /modules/pageflip/index.php



Instructions on how to View page flip
--------------------------------------------------------------------------------------
To view all pageflips goto : /modules/pageflip/index.php
To view single pageflip goto : /modules/pageflip/page-flip.php?brochure_id=<brochure_id>
To view latest pageflip goto : /modules/pageflip/latest.php