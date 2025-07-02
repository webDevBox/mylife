#!/bin/bash
# Add to crontab: * * * * * /home/scripts/run_parser.sh

FILES=/home/uploads/*.xml
for file in $FILES
do
  php /var/www/html/mylifeid/parse_upload_file.php "$file"
done
