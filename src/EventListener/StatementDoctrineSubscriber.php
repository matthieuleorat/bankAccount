<?php declare(strict_types=1);

namespace App\EventListener;

use App\AwsBucket\DeleteFile;
use App\Entity\Statement;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class StatementDoctrineSubscriber implements EventSubscriber
{
    /**
     * @var DeleteFile
     */
    private DeleteFile $deleteFile;
    /**
     * @var SessionInterface
     */
    private SessionInterface $session;

    public function __construct(DeleteFile $deleteFile, SessionInterface $session)
    {
        $this->deleteFile = $deleteFile;
        $this->session = $session;
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
                    $this->session->getFlashBag()->add('warning', $result);
                }
            }
        }
    }
}
