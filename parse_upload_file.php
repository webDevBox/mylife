<?php
$mysqli = new mysqli('localhost', 'root', '', 'mylife');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$xmlFile = $argv[1];
if (!file_exists($xmlFile)) {
    die("XML file not found.");
}

$xml = simplexml_load_file($xmlFile, "SimpleXMLElement", LIBXML_NOCDATA);
$namespaces = $xml->getNamespaces(true);
$xml->registerXPathNamespace('hl7', $namespaces['']);

function getValue($nodes, $attribute = null) {
    if (!empty($nodes)) {
        if ($attribute && isset($nodes[0][$attribute])) {
            return (string)$nodes[0][$attribute];
        }
        return (string)$nodes[0];
    }
    return '';
}

// Dynamically extract metadata
$accountId = getValue($xml->xpath('//hl7:recordTarget/hl7:patientRole/hl7:id'), 'extension');
$languageCode = getValue($xml->xpath('//hl7:languageCommunication/hl7:languageCode'), 'code');
$languageId = strtolower($languageCode) === 'eng' ? 1 : 0;
$datafileSource = getValue($xml->xpath('//hl7:assignedAuthor/hl7:assignedAuthoringDevice/hl7:softwareName'));
$datasource = trim(getValue($xml->xpath('//hl7:recordTarget/hl7:patientRole/hl7:providerOrganization/hl7:name')));
$fileCreationRaw = getValue($xml->xpath('//hl7:effectiveTime'), 'value');
$fileCreationDate = date('Y-m-d', strtotime(substr($fileCreationRaw, 0, 8)));

// Allergies
$allergyEntries = $xml->xpath("//hl7:section[hl7:code[@displayName='Allergies and adverse reactions Document']]/hl7:text/hl7:content");
$index = 1;
foreach ($allergyEntries as $entry) {
    $text = (string)$entry;
    $section = "Allergies";
    $tag = "content";
    $valset = (string)$index;

    $stmt = $mysqli->prepare("INSERT INTO datafile_staging 
        (account_id, language_id, datafile_source, file_creation_date, datasource, section_name, xml_tag_name, value_set, xml_data_value)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sisssssss", $accountId, $languageId, $datafileSource, $fileCreationDate, $datasource, $section, $tag, $valset, $text);
    $stmt->execute();
    if ($stmt->error) {
        file_put_contents('error_log.txt', "Allergies DB Error: " . $stmt->error . PHP_EOL, FILE_APPEND);
    }
    $index++;
}

// Medications
$medications = $xml->xpath("//hl7:section[hl7:code[@displayName='History of Medication use Narrative']]/hl7:text/hl7:list/hl7:item");
$index = 1;
foreach ($medications as $med) {
    $section = "Medications";
    $valset = (string)$index;

    $name = (string)$med->content;
    $instructions = (string)$med->paragraph;

    // Insert name
    $stmt = $mysqli->prepare("INSERT INTO datafile_staging 
        (account_id, language_id, datafile_source, file_creation_date, datasource, section_name, xml_tag_name, value_set, xml_data_value)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $tag = "name";
    $stmt->bind_param("sisssssss", $accountId, $languageId, $datafileSource, $fileCreationDate, $datasource, $section, $tag, $valset, $name);
    $stmt->execute();
    if ($stmt->error) {
        file_put_contents('error_log.txt', "Medications Name DB Error: " . $stmt->error . PHP_EOL, FILE_APPEND);
    }

    // Insert instructions
    $stmt = $mysqli->prepare("INSERT INTO datafile_staging 
        (account_id, language_id, datafile_source, file_creation_date, datasource, section_name, xml_tag_name, value_set, xml_data_value)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $tag = "instructions";
    $stmt->bind_param("sisssssss", $accountId, $languageId, $datafileSource, $fileCreationDate, $datasource, $section, $tag, $valset, $instructions);
    $stmt->execute();
    if ($stmt->error) {
        file_put_contents('error_log.txt', "Medications Instructions DB Error: " . $stmt->error . PHP_EOL, FILE_APPEND);
    }

    $index++;
}

// Move file to processed
$processedDir = '/home/ubuntu/mylifeid_project/uploads/processed/';
rename($xmlFile, $processedDir . basename($xmlFile));
echo "Done parsing " . basename($xmlFile) . PHP_EOL;
?>
