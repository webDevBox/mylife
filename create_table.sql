CREATE TABLE `datafile_staging` (
  `datafile_staging_id` INT NOT NULL AUTO_INCREMENT,
  `account_id` VARCHAR(20) NOT NULL,
  `language_id` INT NOT NULL,
  `datafile_source` VARCHAR(200) NOT NULL,
  `file_creation_date` DATE NOT NULL,
  `datasource` VARCHAR(200) NOT NULL,
  `section_name` VARCHAR(200) NOT NULL,
  `xml_tag_name` VARCHAR(200) NOT NULL,
  `value_set` VARCHAR(5) NOT NULL,
  `xml_data_value` TEXT NOT NULL,
  `is_processed` INT NOT NULL DEFAULT 0,
  `processed_date` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`datafile_staging_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
