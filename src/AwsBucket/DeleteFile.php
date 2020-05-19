<?php declare(strict_types=1);

namespace App\AwsBucket;

class DeleteFile extends AbstractS3Client
{
    public function __construct(string $s3_bucket_name, string $aws_access_key_id, string $aws_secret_access_key)
    {
        parent::__construct($s3_bucket_name, $aws_access_key_id, $aws_secret_access_key);
    }

    public function execute(string $file)
    {
        try {
            $this->client->deleteObject([
                'Bucket' => $this->s3_bucket_name,
                'Key'    => $file,
            ]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return true;
    }
}
