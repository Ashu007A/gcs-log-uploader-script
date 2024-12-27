<?php

require 'vendor/autoload.php';

use Google\Cloud\Storage\StorageClient;

/**
 * Checks if a bucket exists.
 */
function bucket_exists($storage, $bucket_name)
{
    try {
        $bucket = $storage->bucket($bucket_name);
        return $bucket->exists();
    } catch (Exception $e) {
        echo "Error checking if bucket exists: " . $e->getMessage() . PHP_EOL;
        return false;
    }
}

/**
 * Creates a new bucket with specified configuration.
 */
function create_bucket($storage, $bucket_name)
{
    try {
        $bucket = $storage->createBucket($bucket_name, [
            'location' => 'ASIA',
            'uniformBucketLevelAccess' => true
        ]);
        echo "Bucket $bucket_name created in 'ASIA' with uniform access control." . PHP_EOL;
    } catch (Exception $e) {
        echo "Error creating bucket: " . $e->getMessage() . PHP_EOL;
    }
}

/**
 * Uploads a file to the Google Cloud Storage bucket.
 */
function upload_to_gcs($service_account_file, $bucket_name, $source_file_name, $destination_blob_name)
{
    // Initialize the Google Cloud Storage client with the service account file
    $storage = new StorageClient([
        'keyFilePath' => $service_account_file
    ]);

    // Check if the bucket exists, create it if it doesn't
    if (!bucket_exists($storage, $bucket_name)) {
        create_bucket($storage, $bucket_name);
    }

    // Access the bucket and set public access to "Not public"
    $bucket = $storage->bucket($bucket_name);
    $bucket->update(['iamConfiguration' => [
        'uniformBucketLevelAccess' => ['enabled' => true]
    ]]);

    // Upload the file
    $object = $bucket->upload(
        fopen($source_file_name, 'r'),
        [
            'name' => $destination_blob_name
        ]
    );

    echo "File $source_file_name uploaded to $destination_blob_name." . PHP_EOL;
}

// Configuration

// $service_account_file = 'okcredit-42-741029c0795d.json';
// $bucket_name = 'okloan_deepijatel_recordings';

$service_account_file = 'still-habitat-444611-p3-8190b1c5c82d.json';
$bucket_name = 'logs-upload-testing1';
$source_file_name = 'sample_log.log';
$destination_blob_name = 'test_logs/sample_log.log';

// Upload the file to GCS
upload_to_gcs($service_account_file, $bucket_name, $source_file_name, $destination_blob_name);

?>