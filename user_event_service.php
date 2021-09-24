<?php
ini_set('memory_limit', '1024M');
require 'vendor/autoload.php';

use Google\ApiCore\PathTemplate;
use Google\Cloud\Retail\V2\BigQuerySource;
use Google\Cloud\Retail\V2\GcsSource;
use Google\Cloud\Retail\V2\ImportErrorsConfig;
use Google\Cloud\Retail\V2\RejoinUserEventsRequest\UserEventRejoinScope;
use Google\Cloud\Retail\V2\UserEvent;
use Google\Cloud\Retail\V2\UserEventInlineSource;
use Google\Cloud\Retail\V2\UserEventInputConfig;
use Google\Cloud\Retail\V2\UserEventServiceClient;
use Google\Protobuf\Timestamp;

# TODO(developer): update these variables with your information
$endpoint = 'CHANGE_ME';
$project_id = 'CHANGE_ME';
$location = 'global';
$catalog = 'default_catalog';

function catalogName($project, $location, $catalog)
{
    $catalogTemplate = new PathTemplate(
        'projects/{project}/locations/{location}/catalogs/{catalog}');
    return $catalogTemplate->render([
        'project' => $project,
        'location' => $location,
        'catalog' => $catalog
    ]);
}

# [START create user event service client]
function createClient()
{
    global $endpoint;
    return new UserEventServiceClient([
        'apiEndpoint' => $endpoint
    ]);
}

# [END create user event service client]

# [START import userEvents inline source]
function importUserEventsInline($userEvents)
{
    global $client, $project_id, $location, $catalog;
    $source = new UserEventInlineSource([
        'user_events' => $userEvents
    ]);

    $inputConfig = new UserEventInputConfig();
    $inputConfig->setUserEventInlineSource($source);

    return $client->importUserEvents(
        catalogName($project_id, $location, $catalog),
        $inputConfig
    );
}

# [END import userEvents inline source]

# [START import userEvents bigQuery source]
function importUserEventsBigQuery($dataSet, $tableId, $schema)
{
    global $client, $project_id, $location, $catalog;
    $source = new BigQuerySource([
        'project_id' => $project_id,
        'dataset_id' => $dataSet,
        'table_id' => $tableId,
        'data_schema' => $schema
    ]);

    $inputConfig = new UserEventInputConfig();
    $inputConfig->setBigQuerySource($source);

    return $client->importUserEvents(
        catalogName($project_id, $location, $catalog),
        $inputConfig
    );
}

# [END import userEvents bigQuery source]

# [START import userEvents Gcs source]
function importUserEventsGcs($inputUri, $errorUri, $schema)
{
    global $client, $project_id, $location, $catalog;
    $source = new GcsSource([
        'input_uris' => [$inputUri],
        'data_schema' => $schema
    ]);

    $inputConfig = new UserEventInputConfig();
    $inputConfig->setGcsSource($source);

    $errorConfig = new ImportErrorsConfig();
    $errorConfig->setGcsPrefix($errorUri);

    return $client->importUserEvents(
        catalogName($project_id, $location, $catalog),
        $inputConfig,
        ['errorsConfig' => $errorConfig]
    );
}

# [END import userEvents Gcs source]

# [START purge userEvents]
function purgeUserEvents($filter)
{
    global $client, $project_id, $location, $catalog;
    return $client->purgeUserEvents(
        catalogName($project_id, $location, $catalog),
        $filter
    );
}

# [END purge userEvents]

# [START rejoin userEvents]
function rejoinUserEvents($scope)
{
    global $client, $project_id, $location, $catalog;
    return $client->rejoinUserEvents(
        catalogName($project_id, $location, $catalog),
        ['userEventRejoinScope' => $scope]
    );
}

# [END rejoin userEvents]

# [START write userEvents]
function writeUserEvent($userEvent)
{
    global $client, $project_id, $location, $catalog;
    return $client->writeUserEvent(
        catalogName($project_id, $location, $catalog),
        $userEvent
    );
}

# [END write userEvents]

# [START usage examples]
$client = createClient();

$timestamp = new Timestamp(
    ['seconds' => time(),
        'nanos' => 0]
);
$userEvent = new UserEvent([
    'event_type' => 'home-page-view',
    'visitor_id' => uniqid(),
    'event_time' => $timestamp
]);

function waitForImport($operation)
{
    $operation->pollUntilComplete();
    if ($operation->operationSucceeded()) {
        $result = $operation->getResult();
        print 'joined events count: ' . $result->getImportSummary()->getJoinedEventsCount() . PHP_EOL;
        print 'unjoined events count: ' . $result->getImportSummary()->getUnjoinedEventsCount() . PHP_EOL;
    } else {
        $error = $operation->getError();
        print 'got error: ' . $error->getMessage() . PHP_EOL;
    }
}

print '1. Import user events from inline source' . PHP_EOL;
$operation = importUserEventsInline(array($userEvent));
waitForImport($operation);

print '2. Import user events from BigQuery source' . PHP_EOL;
$dataSet = 'CHANGE_ME';
$tableId = 'CHANGE_ME';
$operation = importUserEventsBigQuery($dataSet, $tableId, 'user_event');
waitForImport($operation);

print '3. Import user events from Gcs source' . PHP_EOL;
$input_bucket = 'CHANGE_ME';
$errors_bucket = 'CHANGE_ME';
$operation = importUserEventsGcs($input_bucket, $errors_bucket, 'user_event');
waitForImport($operation);

print '4. write user event' . PHP_EOL;
$writtenEvent = writeUserEvent($userEvent);
print $writtenEvent->getEventType() . PHP_EOL;

print '5. rejoin user events' . PHP_EOL;
rejoinUserEvents(UserEventRejoinScope::UNJOINED_EVENTS);

print '6. purge user events' . PHP_EOL;
purgeUserEvents('visitorId ="abc"');

# [END usage examples]