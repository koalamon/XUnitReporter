<?php

include_once __DIR__ . '/../vendor/autoload.php';

// INPUT VALIDATION

if (count($argv) < 6) {
    die("\n   Usage: XUnitReporter.phar XUnitFile.xml project_api_key system event_identifier tool message \n\n");
}

$xUnitFile = $argv[1];
$projectApiKey = $argv[2];
$system = $argv[3];
$identifier = $argv[4];
$tool = $argv[5];
$message = $argv[6];

if (!file_exists($xUnitFile)) {
    die("\n   File not found: " . $xUnitFile . "\n\n");
}

// XML PROCESSING

$doc = new DOMDocument;
$doc->load($xUnitFile);

$xpath = new DOMXPath($doc);
$query = '//testsuites/testsuite/testcase/failure';

$failures = $xpath->query($query);

$incidents = array();
$message .= "<ul>";
$errorOccured = false;

foreach ($failures as $failure) {
    $message .= '<li>Type: ' . $failure->getAttribute('type') . ' - ' . $failure->nodeValue . '</li>';
    $errorOccured = true;
}

$message .= "</ul>";

// PREPARE REPORT

if (!$errorOccured) {
    $status = \Koalamon\Client\Reporter\Event::STATUS_SUCCESS;
    $message = '';
} else {
    $status = \Koalamon\Client\Reporter\Event::STATUS_FAILURE;
}

$reporter = new \Koalamon\Client\Reporter\Reporter('', $projectApiKey, new GuzzleHttp\Client());
$event = new \Koalamon\Client\Reporter\Event($identifier, $system, $status, $tool, $message, count($failures));

$reporter->sendEvent($event);

die("\n   Incidents send to koalamon.\n\n");
