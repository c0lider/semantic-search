<?php

namespace App\Service;

use Pimcore\Model\DataObject\Listing;
use Psr\Log\LoggerInterface;

abstract readonly class AbstractHydrator
{
    public function __construct(
        private string $objectClass,
        private string $listingClass,
        private LoggerInterface $logger
    ) {
    }

    public function hydrate(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $foundObjects = [];
        $listingClass = $this->listingClass;

        /* @var $listingClass Listing */
        $list = new $listingClass();
        $list->setCondition('id IN (?)', [$ids]);
        // force OpenSearch order
        $list->setOrderKey('FIELD(oo_id, ' . implode(',', $ids) . ')', false);

        foreach ($list as $object) {
            if ($object instanceof $this->objectClass && $object->isPublished()) {
                $foundObjects[] = $object;
            }
        }

        if (count($foundObjects) !== count($ids)) {
            $foundIds = array_map(fn($object) => (int)$object->getId(), $foundObjects);
            $missingIds = array_diff($ids, $foundIds);

            $this->logger->warning(
                'Some ids could not be mapped to pimcore dataobjects of type ' . $this->objectClass,
                ['missing_ids' => array_values($missingIds)]);
        }

        return $foundObjects;
    }
}
