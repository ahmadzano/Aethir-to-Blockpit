<?php

if (empty($argv[1])) {
    exit('Missing year parameter. Expected: php main.php "year=2024"');
}
parse_str($argv[1], $_GET);

$historyFile = json_decode(file_get_contents('aethir.json'), true);
if (!is_array($historyFile) || empty($historyFile['data']['records'])) {
    exit('Aethir history file is invalid.');
}

$year = $_GET['year']; #date('Y');
$from = mktime(0, 0, 0, 1, 1, $year);
$to = mktime(23, 59, 59, 12, 31, $year);

$fileData = [];
$finalData = [
    [
        "Date (UTC)",
        "Integration Name",
        "Label",
        "Outgoing Asset",
        "Outgoing Amount",
        "Incoming Asset",
        "Incoming Amount",
        "Fee Asset (optional)",
        "Fee Amount (optional)",
        "Comment (optional)",
        "Trx. ID (optional)",
    ]
];

/**
 * $airdropTransaction
 * {
 *      "key": "123",
 *      "type": 1,
 *      "status": 2,
 *      "licenseId": "xxxx",
 *      "time": 1735693305,
 *      "amount": "32.57719256"
 * }
 */
foreach ($historyFile['data']['records'] as $airdropTransaction) {
    $epoch = $airdropTransaction['time'];

    if ($epoch > $to || $epoch < $from) {
        continue;
    }

    $type = $airdropTransaction['type'];
    // types found in js file https://app.aethir.com/js/Activity.73a180e9.js
    switch ($type) {
        case 1:
            $transactionType = "Base Reward";
            $label = "Masternode"; //Blockpit label
            break;
        case 2:
            $transactionType = "Bonus Reward";
            $label = "Masternode"; //Blockpit label
            break;
        case 3:
            $transactionType = "Claim";
            $label = "Withdrawal"; //Blockpit label
            break;
        case 6:
            $transactionType = "Withdraw";
            $label = "Withdrawal"; //Blockpit label
            break;
        default:
            $transactionType = "Unknown type " . $type;
            $label = "Unknown"; //Blockpit label
            break;
    }

    $statusCode = $airdropTransaction['status'];
    // statues found in js file https://app.aethir.com/js/Activity.73a180e9.js
    switch ($statusCode) {
        case 1:
            $status = "Pending";
            break;
        case 2:
            if ($type == 6) {
                $status = "Pending";
            } else {
                $status = "Success";
            }
            break;
        case 3:
            $status = "Failed";
            break;
        case 4: # just for 3, 4, 5 (1===t.status?"Pending":4===t.status?"Failed":"Success")
            if (in_array($type, [3, 4, 5])) {
                $status = "Failed";
                break;
            }
        default:
            $status = "Unknown Status " . $statusCode . ". Type: " . $type;
            break;
    }

    $datetime = new DateTime("@$epoch");

    $finalData[] = [
        "Date (UTC)" => $datetime->format('Y-m-d H:i:s'),
        "Integration Name" => "Aethir Manual History Import",
        "Label" => $label,
        "Outgoing Asset" => "",
        "Outgoing Amount" => "",
        "Incoming Asset" => "ATH",
        "Incoming Amount" => $airdropTransaction['amount'],
        "Fee Asset (optional)" => "",
        "Fee Amount (optional)" => "",
        "Comment (optional)" => "Status " . $status . ". License ID. " . $airdropTransaction['licenseId'],
        "Trx. ID (optional)" => $transactionType . " " . $airdropTransaction['key'],
    ];
}


$finalFile = fopen("blockpit.csv","w");

foreach ($finalData as $finalDataLine) {
    fputcsv($finalFile, $finalDataLine);
}

fclose($finalFile);

echo 'Done';
