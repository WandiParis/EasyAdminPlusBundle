<?php
namespace Lle\EasyAdminPlusBundle\Form\DataTransformer;

use App\Entity\Medecin;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class EntityToIdTransformer implements DataTransformerInterface
{

    /**
     * 
     * @var EntityManagerInterface
     */
    private $entityManager;
    
    /**
     * 
     * @var string
     */
    private $class;

    public function __construct(EntityManagerInterface $entityManager, string $class = null)
    {
        $this->entityManager = $entityManager;
        $this->class = $class;
    }
    
    /**
     * 
     * @param string $class
     * @return \App\Form\DataTransformer\EntityToIdTransformer
     */
    public function setClass(string $class) :self
    {
        $this->class = $class;
        return $this;
    }

    /**
     * Transforms an object (issue) to a string (number).
     *
     * @param object|null $entity
     * @return string
     */
    public function transform($entity)
    {
        if (null === $entity) {
            return '';
        }

        return $entity->getId();
    }

    /**
     * Transforms a string (number) to an object (medecin).
     *
     * @param string $entityId
     * @return object|null
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function reverseTransform($entityId)
    {
        if (! $entityId) {
            return null;
        }

        $entity = $this->entityManager->getRepository($this->class)->find($entityId);

        if (null === $entity) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf('A '.$this->class.' with id "%s" does not exist!', $entityId));
        }

        return $entity;
    }
}

