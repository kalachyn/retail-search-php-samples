<?php
ini_set('memory_limit', '1024M');
require 'vendor/autoload.php';

use \Google\Protobuf\Value;
use Google\ApiCore\PathTemplate;
use Google\Cloud\Retail\V2\UserEvent;
use Google\Cloud\Retail\V2\PredictionServiceClient;

# TODO(developer): update these variables with your information
$endpoint = 'CHANGE_ME';
$project_id = 'CHANGE_ME';
$placement = 'CHANGE_ME';
$location = 'global';
$catalog = 'default_catalog';

function placementName($project, $location, $catalog, $placement)
{
    $placementTemplate = new PathTemplate(
        'projects/{project}/locations/{location}/catalogs/{catalog}/placements/{placement}');
    return $placementTemplate->render([
        'project' => $project,
        'location' => $location,
        'catalog' => $catalog,
        'placement' => $placement,
    ]);
}

# [START create prediction service client]
function createClient()
{
    global $endpoint;
    return new PredictionServiceClient([
        'apiEndpoint' => $endpoint
    ]);
}

# [END create prediction service client]

# [START get prediction]
function getPrediction($userEvent) {
    global $client, $project_id, $location, $catalog, $placement;
    return $client->predict(
        placementName($project_id, $location, $catalog, $placement),
        $userEvent
    );
}
# [END get prediction]

# [START get prediction with filter]
function getPredictionWithFilter($userEvent, $filter) {
    global $client, $project_id, $location, $catalog, $placement;
    return $client->predict(
        placementName($project_id, $location, $catalog, $placement),
        $userEvent,
        ['filter'=>$filter]
    );
}
# [END get prediction with filter]

# [START get prediction with page size]
function getPredictionWithPageSize($userEvent, $pageSize) {
    global $client, $project_id, $location, $catalog, $placement;
    return $client->predict(
        placementName($project_id, $location, $catalog, $placement),
        $userEvent,
        ['pageSize'=>$pageSize]
    );
}
# [END get prediction with page size]

# [START get prediction with params]
function getPredictionWithParams($userEvent, $params) {
    global $client, $project_id, $location, $catalog, $placement;
    return $client->predict(
        placementName($project_id, $location, $catalog, $placement),
        $userEvent,
        ['params'=>$params]
    );
}
# [END get prediction with params]

# [START get prediction with labels]
function getPredictionWithLabels($userEvent, $labels) {
    global $client, $project_id, $location, $catalog, $placement;
    return $client->predict(
        placementName($project_id, $location, $catalog, $placement),
        $userEvent,
        ['labels'=>$labels]
    );
}
# [END get prediction with labels]

# [START usage examples]
$client = createClient();

$userEvent = new UserEvent([
    'event_type' => 'home-page-view',
    'visitor_id' => uniqid()
]);

print '1. Get prediction' . PHP_EOL;
$prediction = getPrediction($userEvent);
foreach ($prediction->getResults() as $result) {
    print 'Result: ' . $result->getId() . PHP_EOL;
}

print '2. Get prediction with filter' . PHP_EOL;
$prediction = getPredictionWithFilter($userEvent, 'filterOutOfStockItems');
foreach ($prediction->getResults() as $result) {
    print 'Result: ' . $result->getId() . PHP_EOL;
}

print '3. Get prediction with page size' . PHP_EOL;
$prediction = getPredictionWithPageSize($userEvent, 5);
foreach ($prediction->getResults() as $result) {
    print 'Result: ' . $result->getId() . PHP_EOL;
}

print '4. Get prediction with params' . PHP_EOL;
$value = new Value();
$value->setBoolValue(true);
$prediction = getPredictionWithParams($userEvent, ['returnProduct' => $value]);
foreach ($prediction->getResults() as $result) {
    print 'Result: ' . $result->getId() . PHP_EOL;
}

print '5. Get prediction with labels' . PHP_EOL;
$prediction = getPredictionWithLabels($userEvent, ['key' =>'value']);
foreach ($prediction->getResults() as $result) {
    print 'Result: ' . $result->getId() . PHP_EOL;
}
# [END usage examples]