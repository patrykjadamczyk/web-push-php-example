<?php
require 'vendor/autoload.php';
$subscription = json_decode(file_get_contents('php://input'), true);
if (!isset($subscription['endpoint'])) {
    echo 'Error: not a subscription';
    return;
}
$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
    case 'POST':
        // create a new subscription entry in your database (endpoint is unique)
        break;
    case 'PUT':
        // update the key and token of subscription corresponding to the endpoint
        break;
    case 'DELETE':
        // delete the subscription corresponding to the endpoint
        break;
    default:
        echo "Error: method not handled";
        return;
}

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
// here I'll get the subscription endpoint in the POST parameters
// but in reality, you'll get this information in your database
// because you already stored it (cf. push_subscription.php)
$subscription = Subscription::create(json_decode(file_get_contents('php://input'), true));
$auth = array(
    'VAPID' => array(
        'subject' => 'mailto: patrykadamczyk@patrykadamczyk.net',
        'publicKey' => 'BDwYyNLBYIyNOBFX3M27uTAUXLrUxgHVyBJPjxJj3aQR7ghxC_MetHpzgTspdk4e4Iq9E0LCzeAtbCPOcdclxCk',
        'privateKey' => 'rOHBJ0AGjSf37QW-mPRScGNr_0Bqn6Ouk-1nQPUUPpI'
    ),
);
$webPush = new WebPush($auth);
$res = $webPush->sendNotification(
    $subscription,
    json_encode(array(
        "title" => "Push Test from PHP",
        "content" => "Push Content from PHP"
    ))
);
// handle eventual errors here, and remove the subscription from your server if it is expired
foreach ($webPush->flush() as $report) {
    $endpoint = $report->getRequest()->getUri()->__toString();
    if ($report->isSuccess()) {
        echo "[v] Message sent successfully for subscription {$endpoint}.";
    } else {
        echo "[x] Message failed to sent for subscription {$endpoint}: {$report->getReason()}";
    }
}
