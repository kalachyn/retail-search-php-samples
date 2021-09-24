<?php
require 'vendor/autoload.php';

use Google\Cloud\Retail\V2\CatalogServiceClient;

# TODO(developer): update these variables with your information
$endpoint = 'CHANGE_ME';
$project_id = 'CHANGE_ME';
$location = 'global';
$catalog = 'default_catalog';

# [START create catalog service client]
function createClient()
{
    global $endpoint;
    return new CatalogServiceClient([
        'apiEndpoint' => $endpoint
    ]);
}

# [END create catalog service client]

# [START list catalogs]
function listCatalogs()
{
    global $client, $project_id, $location;
    return $client->listCatalogs(
        CatalogServiceClient::locationName($project_id, $location)
    );
}

# [END list catalogs]

# [START update catalog]
function updateCatalog($catalog, array $optionalArgs = [])
{
    global $client, $project_id, $location;
    return $client->updateCatalog($catalog, $optionalArgs);
}

# [END update catalog]

# [START usage examples]
$client = createClient();

print '1. List catalogs' . PHP_EOL;
$catalogs = listCatalogs();
foreach ($catalogs as $catalog) {
    print 'Catalog: ' . $catalog->getName() . PHP_EOL;
}

# [END usage examples]