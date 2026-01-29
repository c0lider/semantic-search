<?php

namespace App\EventListener;

use App\Message\ProductDeleteMessage;
use App\Message\ProductUpdateMessage;
use Pimcore\Event\DataObjectEvents;
use Pimcore\Event\Model\DataObjectEvent;
use Pimcore\Model\DataObject\Product;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class ProductListener
{
    public function __construct(
        private MessageBusInterface $messageBus,
    ) {
    }

    #[AsEventListener(event: DataObjectEvents::POST_ADD)]
    #[AsEventListener(event: DataObjectEvents::POST_UPDATE)]
    public function onProductUpdateOrCreation(DataObjectEvent $event): void
    {
        $object = $event->getObject();

        if ($object instanceof Product) {
            $this->messageBus->dispatch(new ProductUpdateMessage($object->getId()));
        }
    }

    #[AsEventListener(event: DataObjectEvents::POST_DELETE)]
    public function onProductDelete(DataObjectEvent $event): void
    {
        $object = $event->getObject();

        if ($object instanceof Product) {
            $this->messageBus->dispatch(new ProductDeleteMessage($object->getId()));
        }
    }
}
