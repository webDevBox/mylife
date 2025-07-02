MyLifeID Sample Project - Deliverable Report

Prepared by: Muhammad Khubaib Waheed
Date: 1 July 2025

1. Database Objects
A table `datafile_staging` was created to hold parsed XML data.

Fields:
- account_id  
- language_id  
- datafile_source  
- file_creation_date  
- datasource  
- section_name  
- xml_tag_name  
- value_set  
- xml_data_value  
- is_processed  
- created_at / processed_date

See: `create_table.sql` for full structure.

Note: At the project root we have our parse script file name parse_upload_file.php in this file at line 2 you just need to give correct credentials of your MariaDB.


2. Cron Job Launch Script
-------------------------
Cron expression (every minute):
* * * * * /usr/bin/php /home/ubuntu/mylifeid_project/parse_upload_file.php /home/ubuntu/mylifeid_project/uploads/*.xml >> /home/ubuntu/parser.log 2>&1
Working:
•	Runs the PHP parser every minute
•	Reads all .xml files from /home/ubuntu/uploads/
•	To see records in MariaDB use below commands
o	sudo mysql -u mylife_user -p mylife
o	Password: “StrongPassword123”
o	SELECT * FROM datafile_staging\G

3. Parsing Code
---------------
The parser script `parse_upload_file.php` performs the following:
- Loads and parses the XML file which take from uploads folder from project root.
- Dynamically extracts metadata:
  - `accountId`, `languageId`, `fileCreationDate`, `datafileSource`, `datasource`
- Parses `Allergies` and `Medications` sections
- Uses `value_set` to number repeat records like `1`, `2`, `3`, etc.
- Moves parsed XML to `/uploads/processed/`


4. Project Documentation
a. Parsing Logic
----------------
- `accountId` → from `<patientRole><id extension="..."/>`
- `languageId` → from `<languageCode code="eng"/>` → mapped as `1`
- `datafileSource` → from `<softwareName>`
- `datasource` → from `<providerOrganization><name>`
- `fileCreationDate` → parsed from `<effectiveTime value="...">`
- Allergy section extracted from `<section><text><content>`
- Medications extracted from `<list><item><content>` and `<paragraph>`
- Each medication or allergy uses incremental `value_set` (`1`, `2`, `3`, ...)


b. Manual Launch Instructions
To run the parser manually from CLI:
php /home/ubuntu/mylifeid_project/parse_upload_file.php /home/ubuntu/uploads/sample.xml

Note: Replace sample.xml with your actual file name

c. Suggestions
•	Use laravel framework instead of core PHP
•	python converter to make more intelligent
•	Convert file into JSON and then save it in processed folder so human can read processed files.
