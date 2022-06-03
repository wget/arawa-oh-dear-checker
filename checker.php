<?php

/******************************************************************************/
$OH_DEAR_SECRET = "<cf. password manager>";
/******************************************************************************/

/*
 * Check if authorized to answer
 */
if (!isset($_SERVER['HTTP_OH_DEAR_HEALTH_CHECK_SECRET'])) {
    http_response_code(403);
    echo "Health access denied: running from CLI or token not present.";
    exit();
}
$headerStringValue = $_SERVER['HTTP_OH_DEAR_HEALTH_CHECK_SECRET'];
if ($headerStringValue !== $OH_DEAR_SECRET) {
    http_response_code(403);
    echo "Health access denied: running from CLI or token not present.";
    exit();
}

require __DIR__ . '/vendor/autoload.php';
use OhDear\HealthCheckResults\CheckResult;
use OhDear\HealthCheckResults\CheckResults;


/*
 * Check SPF record for Arawa.fr
 */
function arawa_check_dns_spf() {
    global $checkResults;

    $spfRecords = dns_get_record('arawa.fr', DNS_TXT);

    if (empty($spfRecords)) {
        $checkResult = new CheckResult(
            name: 'DnsSpfIsCorrect',
            label: 'DNS SPF record is correct',
            notificationMessage: 'arawa.fr doesn\'t have a SPF record',
            shortSummary: 'N/A',
            status: CheckResult::STATUS_FAILED,
            meta: ['dns_spf_is_is_correct' => false]
        );
        $checkResults->addCheckResult($checkResult);
        return;
    }

    $spfRecordFound = false;
    foreach ($spfRecords as $record) {
        if (strpos($record['txt'], "spf") === false) {
            continue;
        }

        if ($spfRecordFound) {
            $checkResult = new CheckResult(
                name: 'DnsSpfIsCorrect',
                label: 'DNS SPF record is correct',
                notificationMessage: 'arawa.fr has more than one SPF record: \'' . $record['txt'] . '\'',
                shortSummary: 'nb SPF record > 1',
                status: CheckResult::STATUS_FAILED,
                meta: ['dns_spf_is_is_correct' => false]
            );
            $checkResults->addCheckResult($checkResult);
            return;
        }

        $spfRecordFound = true;

        if ($record['txt'] !== 'v=spf1 mx include:spf.sendinblue.com -all') {
            $checkResult = new CheckResult(
                name: 'DnsSpfIsCorrect',
                label: 'DNS SPF record is correct',
                notificationMessage: 'arawa.fr has an unknown SPF record: \'' . $record['txt'] . '\'',
                shortSummary: 'SPF record unknown',
                status: CheckResult::STATUS_FAILED,
                meta: ['dns_spf_is_is_correct' => false]
            );
            $checkResults->addCheckResult($checkResult);
            return;
        }

        $checkResult = new CheckResult(
            name: 'DnsSpfIsCorrect',
            label: 'DNS SPF record is correct',
            notificationMessage: 'arawa.fr SPF record is correct: \'' . $record['txt'] . '\'',
            shortSummary: 'SPF record correct',
            status: CheckResult::STATUS_OK,
            meta: ['dns_spf_is_is_correct' => true]
        );
        $checkResults->addCheckResult($checkResult);
    }
}


/*
 * Add other checks here
 */

/*
 * Main
 */

// var_dump(DateTime::createFromFormat('Y-m-d H:i:s', 'now', new DateTimeZone('Europe/Paris')));
// $checkResults = new CheckResults(DateTime::createFromFormat('Y-m-d H:i:s', '2021-01-01 00:00:00'));
$checkResults = new CheckResults(new DateTime(timezone: new DateTimeZone('Europe/Paris')));

arawa_check_dns_spf();

header("Content-type:application/json");
echo $checkResults->toJson();
