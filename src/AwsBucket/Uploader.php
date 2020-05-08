<?php declare(strict_types=1);

namespace App\AwsBucket;

use Aws\S3\S3Client;

class Uploader
{
    /**
     * @var string
     */
    private $s3_bucket_name;
    /**
     * @var string
     */
    private $aws_access_key_id;
    /**
     * @var string
     */
    private $aws_secret_access_key;

    public function __construct(string $s3_bucket_name, string $aws_access_key_id, string $aws_secret_access_key)
    {
        $this->s3_bucket_name = $s3_bucket_name;
        $this->aws_access_key_id = $aws_access_key_id;
        $this->aws_secret_access_key = $aws_secret_access_key;
    }

    public function execute(string $remoteFolder, string $extension, string $filePath) : string
    {
        $s3 = new S3Client([
            'version'  => '2006-03-01',
            'region'   => 'eu-west-3',
            'credentials' => [
                'key'    => $this->aws_access_key_id,
                'secret' => $this->aws_secret_access_key,
            ]
        ]);

        $filename = $remoteFolder.$this->generateFileName($extension);

        $s3->putObject([
            'Bucket' => $this->s3_bucket_name,
            'Key'    => $filename,
            'Body'   => fopen($filePath, 'r'),
            'ACL'    => 'private',
        ]);

        return $filename;
    }

    private function generateFileName(string $extension)
    {
        $token = openssl_random_pseudo_bytes(16, $cstrong);

        if (false === $cstrong || false === $token) {
            throw new \RuntimeException('IV generation failed');
        }

        return bin2hex($token).'.'.$extension;
    }
}
