MyLifeID Sample Project - Parser Implementation

1. Setup the database:
   - Use the `create_table.sql` to create `datafile_staging` table in MariaDB.

2. Deploy the PHP parser:
   - Place `parse_upload_file.php` in your LAMP server directory (e.g., /var/www/html/mylifeid/).
   - Update DB credentials in the file.

3. Setup cron job:
   - Place `run_parser.sh` in /home/scripts/
   - Run `chmod +x run_parser.sh`
   - Add to crontab: * * * * * /home/scripts/run_parser.sh

4. How it works:
   - When an XML file is dropped in /home/uploads/, the cron job will:
     - Run the PHP parser
     - Parse and insert allergy + medication data
     - Move the file to /home/uploads/processed/

5. Notes:
   - Logs/errors will appear on terminal or cron logs.
   - Adjust XPath or sections to parse more data as needed.
