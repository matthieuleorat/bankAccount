<?php declare(strict_types=1);

/*
 * This file is part of the BankAccount project.
 *
 * (c) Matthieu Leorat <matthieu.leorat@pm.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace App\EventListener;

use App\AwsBucket\DeleteFile;
use App\Entity\Statement;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class StatementDoctrineSubscriber implements EventSubscriber
{
    /**
     * @var DeleteFile
     */
    private DeleteFile $deleteFile;

    public function __construct(DeleteFile $deleteFile)
    {
        $this->deleteFile = $deleteFile;
    }

    public function getSubscribedEvents() : array
    {
        return [
            Events::postRemove,
        ];
    }

    public function postRemove(LifecycleEventArgs $args) : void
    {
        $this->deleteSourceStatementFile($args);
    }

    private function deleteSourceStatementFile(LifecycleEventArgs $args) : void
    {
        if ($args->getObject() instanceof Statement) {
            /** @var Statement $statement */
            $statement = $args->getObject();
            if (null !== $statement->getRemoteFile()) {
                $result = $this->deleteFile->execute($statement->getRemoteFile());
                if (true !== $result) {
                    throw new \Exception();
                }
            }
        }
    }
}
