<?php
ini_set('memory_limit', '1024M');
require 'vendor/autoload.php';

use Google\Cloud\Retail\V2\BigQuerySource;
use Google\Cloud\Retail\V2\GcsSource;
use Google\Cloud\Retail\V2\ImportErrorsConfig;
use Google\Cloud\Retail\V2\Product;
use Google\Cloud\Retail\V2\ProductInlineSource;
use Google\Cloud\Retail\V2\ProductInputConfig;
use Google\Cloud\Retail\V2\ProductServiceClient;

# TODO(developer): update these variables with your information
$endpoint = 'CHANGE_ME';
$project_id = 'CHANGE_ME';
$input_bucket = 'CHANGE_ME';
$errors_bucket = 'CHANGE_ME';
$location = 'global';
$catalog = 'default_catalog';
$branch = 'default_branch';

# [START create product service client]
function createClient()
{
    global $endpoint;
    return new ProductServiceClient([
        'apiEndpoint' => $endpoint
    ]);
}

# [END create product service client]

# [START import products inline source]
function importProductsInline($products)
{
    global $client, $project_id, $location, $catalog, $branch;
    $source = new ProductInlineSource([
        'products' => $products
    ]);

    $inputConfig = new ProductInputConfig();
    $inputConfig->setProductInlineSource($source);

    return $client->importProducts(
        ProductServiceClient::branchName($project_id, $location, $catalog, $branch),
        $inputConfig
    );
}

# [END import products inline source]

# [START import products bigQuery source]
function importProductsBigQuery($dataSet, $tableId, $schema)
{
    global $client, $project_id, $location, $catalog, $branch;
    $source = new BigQuerySource([
        'project_id' => $project_id,
        'dataset_id' => $dataSet,
        'table_id' => $tableId,
        'data_schema' => $schema
    ]);

    $inputConfig = new ProductInputConfig();
    $inputConfig->setBigQuerySource($source);

    return $client->importProducts(
        ProductServiceClient::branchName($project_id, $location, $catalog, $branch),
        $inputConfig
    );
}

# [END import products bigQuery source]

# [START import products Gcs source]
function importProductsGcs($inputUri, $errorUri, $schema)
{
    global $client, $project_id, $location, $catalog, $branch;
    $source = new GcsSource([
        'input_uris' => [$inputUri],
        'data_schema' => $schema
    ]);

    $inputConfig = new ProductInputConfig();
    $inputConfig->setGcsSource($source);

    $errorConfig = new ImportErrorsConfig();
    $errorConfig->setGcsPrefix($errorUri);

    return $client->importProducts(
        ProductServiceClient::branchName($project_id, $location, $catalog, $branch),
        $inputConfig,
        ['errorsConfig' => $errorConfig]
    );
}

# [END import products Gcs source]

# [START usage examples]
$client = createClient();

print '1. Import products inline source' . PHP_EOL;
$product_id = uniqid();
$product = new Product([
    'title' => 'Product title',
    'type' => 0,
    'categories' => ['category> subcategory'],
    'uri' => 'http://product-uri.com',
    'id' => $product_id,
    'name' => ProductServiceClient::productName($project_id, $location, $catalog, $branch, $product_id)
]);
$operation = importProductsInline([$product]);
print $operation->getName() . PHP_EOL;

print '2. Import products BigQuery source' . PHP_EOL;
$operation = importProductsBigQuery('INTEGRATION_TESTS', 'v2alpha_inventory', 'product');
print $operation->getName() . PHP_EOL;

print '3. Import products GCS source' . PHP_EOL;
$operation = importProductsGcs($input_bucket, $errors_bucket, 'product');
print $operation->getName() . PHP_EOL;

# [END usage examples]