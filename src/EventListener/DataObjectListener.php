<?php

namespace App\EventListener;

use App\Message\MovieDeleteMessage;
use App\Message\MovieUpdateMessage;
use App\Message\ProductDeleteMessage;
use App\Message\ProductUpdateMessage;
use Pimcore\Event\DataObjectEvents;
use Pimcore\Event\Model\DataObjectEvent;
use Pimcore\Model\DataObject\Movie;
use Pimcore\Model\DataObject\Product;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class DataObjectListener
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private LoggerInterface $logger
    ) {
    }

    #[AsEventListener(event: DataObjectEvents::POST_ADD)]
    #[AsEventListener(event: DataObjectEvents::POST_UPDATE)]
    public function onObjectUpdateOrCreation(DataObjectEvent $event): void
    {
        $object = $event->getObject();
        $message = null;

        if ($object instanceof Product) {
            $message = new ProductUpdateMessage($object->getId());
        } else if ($object instanceof Movie) {
            $message = new MovieUpdateMessage($object->getId());
        }

        if ($message) {
            try {
                $this->messageBus->dispatch($message);
            } catch (ExceptionInterface $e) {
                $this->logger->error($e);
            }
        }
    }

    #[AsEventListener(event: DataObjectEvents::POST_DELETE)]
    public function onObjectDelete(DataObjectEvent $event): void
    {
        $object = $event->getObject();
        $message = null;

        if ($object instanceof Product) {
            $message = new ProductDeleteMessage($object->getId());
        } else if ($object instanceof Movie) {
            $message = new MovieDeleteMessage($object->getId());
        }

        try {
            $this->messageBus->dispatch($message);
        } catch (ExceptionInterface $e) {
            $this->logger->error($e);
        }
    }
}
