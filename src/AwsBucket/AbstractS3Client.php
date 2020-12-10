<?php declare(strict_types=1);

/**
 * This file is part of the BankAccount project.
 *
 * (c) Matthieu Leorat <matthieu.leorat@pm.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace App\AwsBucket;

use Aws\S3\S3Client;

class AbstractS3Client
{
    const AWS_VERSION = '2006-03-01';
    const AWS_REGION = 'eu-west-3';

    protected string $s3_bucket_name;

    protected string $aws_access_key_id;

    protected string $aws_secret_access_key;

    protected S3Client $client;

    public function __construct(string $s3_bucket_name, string $aws_access_key_id, string $aws_secret_access_key)
    {
        $this->s3_bucket_name = $s3_bucket_name;
        $this->aws_access_key_id = $aws_access_key_id;
        $this->aws_secret_access_key = $aws_secret_access_key;
        $this->setClient();
    }

    private function setClient()
    {
        $this->client = new S3Client([
            'version'  => self::AWS_VERSION,
            'region'   => self::AWS_REGION,
            'credentials' => [
                'key'    => $this->aws_access_key_id,
                'secret' => $this->aws_secret_access_key,
            ]
        ]);
    }
}