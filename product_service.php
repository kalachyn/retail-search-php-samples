<?php
ini_set('memory_limit', '1024M');
require 'vendor/autoload.php';

use Google\Cloud\Retail\V2\Product;
use Google\Cloud\Retail\V2\ProductServiceClient;
use Google\Protobuf\FieldMask;

# TODO(developer): update these variables with your information
$endpoint = 'CHANGE_ME';
$project_id = 'CHANGE_ME';
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

# [START example product]
$product = new Product([
    'title' => 'Product title',
    'type' => 0,
    'categories' => ['category> subcategory'],
    'uri' => 'http://product-uri.com'
]);
# [END example product]

# [START create product]
function createProduct($product, $id)
{
    global $client, $project_id, $location, $catalog, $branch;
    return $client->createProduct(
        ProductServiceClient::branchName($project_id, $location, $catalog, $branch),
        $product,
        $id);
}

# [END create product]

# [START update product]
function updateProduct($product, $updateMask)
{
    global $client;
    return $client->updateProduct(
        $product,
        ['updateMask' => $updateMask]);
}

# [END update product]

# [START get product]
function getProduct($productName)
{
    global $client;
    return $client->getProduct($productName);
}

# [END get product]

# [START delete product]
function deleteProduct($productName)
{
    global $client;
    $client->deleteProduct($productName);
}

# [END delete product]

# [START usage examples]
$client = createClient();

print '1. Create product' . PHP_EOL;
$createdProduct = createProduct($product, uniqid());
print $createdProduct->getName() . PHP_EOL;

print '2. Get product' . PHP_EOL;
$receivedProduct = getProduct($createdProduct->getName());
print $receivedProduct->getName() . PHP_EOL;

print '3. Update product' . PHP_EOL;
$fieldMask = new FieldMask([
    'paths' => ['title']
]);
$createdProduct->setTitle('Updated product title');
updateProduct($createdProduct, $fieldMask);
$receivedProduct = getProduct($createdProduct->getName());
print $receivedProduct->getTitle() . PHP_EOL;

print '4. Delete product' . PHP_EOL;
deleteProduct($createdProduct->getName());
try {
    getProduct($createdProduct->getName());
} catch (Exception $e) {
    print 'Product deleted successfully' . PHP_EOL;
}

# [END usage examples]