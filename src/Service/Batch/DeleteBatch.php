<?php

namespace Lle\EasyAdminPlusBundle\Service\Batch;

use Symfony\Component\HttpFoundation\Response;
use Doctrine\Bundle\DoctrineBundle\Registry;

class DeleteBatch implements BatchInterface
{

    /** @var Registry */
    private $doctrine;
    private $em;

    /**
     * @param Registry          $doctrine
     * @param RequestStack|null $requestStack
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function execute($request, array $entityConfig, $ids, array $data) 
    {
        if (null === $this->em = $this->doctrine->getManagerForClass($entityConfig['class'])) {
            throw new \RuntimeException(sprintf('There is no Doctrine Entity Manager defined for the "%s" class', $entityConfig['class']));
        }

        foreach ($ids as $itemId) {
            $this->deleteItem($entityConfig, $itemId);

        }
        $this->em->flush();

    }

    /**
     * Looks for the object that corresponds to the selected 'id' of the current entity.
     *
     * @param array $entityConfig
     * @param mixed $itemId
     *
     * @return object The entity
     *
     * @throws EntityNotFoundException
     */
    private function deleteItem(array $entityConfig, $itemId)
    {
        

        if (null === $entity = $this->em->getRepository($entityConfig['class'])->find($itemId)) {
            throw new EntityNotFoundException(array('entity_name' => $entityConfig['name'], 'entity_id_name' => $entityConfig['primary_key_field_name'], 'entity_id_value' => $itemId));
        }

        $this->em->remove($entity);
    }

}