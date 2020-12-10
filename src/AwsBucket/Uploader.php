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

class Uploader extends AbstractS3Client
{
    public function __construct(string $s3_bucket_name, string $aws_access_key_id, string $aws_secret_access_key)
    {
        parent::__construct($s3_bucket_name, $aws_access_key_id, $aws_secret_access_key);
    }

    public function execute(string $remoteFolder, string $extension, string $filePath) : string
    {
        $filename = $remoteFolder.$this->generateFileName($extension);

        $this->client->putObject([
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
