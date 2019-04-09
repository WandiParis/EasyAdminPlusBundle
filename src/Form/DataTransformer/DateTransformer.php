<?php
namespace Lle\EasyAdminPlusBundle\Form\DataTransformer;

use App\Entity\Medecin;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class DateTransformer implements DataTransformerInterface
{
    /**
     * Transforms an object (issue) to a string (number).
     *
     * @param object|null $entity
     * @return string
     */
    public function transform($date)
    {
        return $date;
    }

    /**
     * Transforms a string (number) to an object (medecin).
     *
     * @param string $entityId
     * @return object|null
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function reverseTransform($date)
    {
        $pattern = "/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/";
        if (preg_match($pattern, $date) == 0) {
            throw new TransformationFailedException();
        };

        return $date;
    }
}

