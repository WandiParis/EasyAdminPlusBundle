<?php

namespace Wandi\EasyAdminPlusBundle\Generator\Type;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Gedmo\Mapping\Annotation\SortablePosition;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Ip;
use Symfony\Component\Validator\Constraints\Url;
use Vich\UploaderBundle\Mapping\Annotation\UploadableField;
use Wandi\EasyAdminPlusBundle\Generator\Helper\PropertyHelper;

class TypeGuesser
{
    /**
     * Default configuration of an EasyAdminGeneratorType.
     */
    public static $defaultConfigType = [
        'mandatoryClasses' => [],
        'noMandatoryClasses' => [],
        'priority' => 4,
        'easyAdminType' => null,
        'typeForced' => false,
        'methodsTypeForced' => [],
        'methodsNoAllowed' => [],
        'doctrineColumnType' => null,
    ];

    /**
     * List of EasyAdminType configurations.
     */
    public static $generatorTypesConfiguration = [
        EasyAdminType::VICH_IMAGE => [
            'mandatoryClasses' => [
                Image::class,
                UploadableField::class,
            ],
            'priority' => 1,
            'easyAdminType' => EasyAdminType::VICH_IMAGE,
            'typeForced' => true,
            'methodsNoAllowed' => [
                'list',
                'show',
            ],
        ],
        EasyAdminType::STRING => [
            'priority' => 3,
            'easyAdminType' => EasyAdminType::STRING,
            'doctrineColumnType' => DoctrineType::STRING,
        ],
        EasyAdminType::INTEGER => [
            'priority' => 3,
            'easyAdminType' => EasyAdminType::INTEGER,
            'doctrineColumnType' => DoctrineType::INTEGER,
        ],
        EasyAdminType::RAW => [
            'doctrineColumnType' => DoctrineType::TEXT,
            'priority' => 3,
            'easyAdminType' => EasyAdminType::RAW,
            'typeForced' => true,
            'methodsNoAllowed' => [
                'list',
            ],
            'methodsTypeForced' => [
                'new',
                'edit',
            ],
        ],
        EasyAdminType::IMAGE => [
            'doctrineColumnType' => DoctrineType::STRING,
            'priority' => 4,
            'easyAdminType' => EasyAdminType::IMAGE,
            'typeForced' => true,
            'methodsNoAllowed' => [
                'new',
                'edit',
            ],
        ],
        EasyAdminType::BOOLEAN => [
            'doctrineColumnType' => DoctrineType::BOOLEAN,
            'priority' => 3,
            'easyAdminType' => EasyAdminType::BOOLEAN,
            'typeForced' => true,
            'methodsTypeForced' => [
                'new',
                'edit',
            ],
        ],
        EasyAdminType::POSITION => [
            'mandatoryClasses' => [
                SortablePosition::class,
            ],
            'priority' => 2,
            'easyAdminType' => EasyAdminType::POSITION,
            'methodsNoAllowed' => [
                'add',
            ],
        ],
        EasyAdminType::VICH_FILE => [
            'mandatoryClasses' => [
                UploadableField::class,
                File::class,
            ],
            'priority' => 1,
            'easyAdminType' => EasyAdminType::VICH_FILE,
            'typeForced' => true,
            'methodsNoAllowed' => [
                'list',
                'show',
            ],
        ],
        EasyAdminType::FILE => [
            'doctrineColumnType' => DoctrineType::STRING,
            'priority' => 4,
            'easyAdminType' => EasyAdminType::FILE,
            'typeForced' => true,
            'methodsNoAllowed' => [
                'new',
                'edit',
            ],
        ],
        EasyAdminType::COLLECTION => [
            'priority' => 6,
            'easyAdminType' => EasyAdminType::COLLECTION,
            'typeForced' => true,
        ],
        EasyAdminType::AUTOCOMPLETE => [
            'noMandatoryClasses' => [
                ManyToOne::class,
                OneToOne::class,
                ManyToMany::class,
                OneToMany::class,
            ],
            'priority' => 1,
            'easyAdminType' => EasyAdminType::AUTOCOMPLETE,
            'typeForced' => true,
            'methodsTypeForced' => [
                'list',
                'show',
            ],
        ],
        EasyAdminType::EMAIL => [
            'mandatoryClasses' => [
                Email::class,
            ],
            'priority' => 2,
            'easyAdminType' => EasyAdminType::EMAIL,
            'typeForced' => true,
            'methodsTypeForced' => [
                'edit',
                'new',
            ],
        ],
        EasyAdminType::URL => [
            'mandatoryClasses' => [
                Url::class,
            ],
            'priority' => 2,
            'easyAdminType' => EasyAdminType::URL,
            'typeForced' => true,
            'methodsTypeForced' => [
                'edit',
                'new',
            ],
        ],
        EasyAdminType::IP => [
            'mandatoryClasses' => [
                Ip::class,
            ],
            'priority' => 2,
            'easyAdminType' => EasyAdminType::STRING,
        ],
        EasyAdminType::CHOICE => [
            'mandatoryClasses' => [
                Choice::class,
            ],
            'priority' => 2,
            'easyAdminType' => EasyAdminType::CHOICE,
            'typeForced' => true,
            'methodsTypeForced' => [
                'list',
                'show',
            ],
        ],
        EasyAdminType::FLOAT => [
            'doctrineColumnType' => DoctrineType::FLOAT,
            'priority' => 3,
            'easyAdminType' => EasyAdminType::FLOAT,
        ],
        EasyAdminType::BIGINT => [
            'doctrineColumnType' => DoctrineType::BIGINT,
            'priority' => 3,
            'easyAdminType' => EasyAdminType::BIGINT,
        ],
        EasyAdminType::ARRAY => [
            'doctrineColumnType' => DoctrineType::ARRAY,
            'priority' => 3,
            'easyAdminType' => EasyAdminType::ARRAY,
        ],
        EasyAdminType::SIMPLE_ARRAY => [
            'doctrineColumnType' => DoctrineType::SIMPLE_ARRAY,
            'priority' => 3,
            'easyAdminType' => EasyAdminType::SIMPLE_ARRAY,
        ],
        EasyAdminType::JSON_ARRAY => [
            'doctrineColumnType' => DoctrineType::JSON_ARRAY,
            'priority' => 3,
            'easyAdminType' => EasyAdminType::JSON_ARRAY,
        ],
        EasyAdminType::TIME => [
            'doctrineColumnType' => DoctrineType::TIME,
            'priority' => 3,
            'easyAdminType' => EasyAdminType::TIME,
        ],
        EasyAdminType::DATE => [
            'doctrineColumnType' => DoctrineType::DATE,
            'priority' => 3,
            'easyAdminType' => EasyAdminType::DATE,
        ],
        EasyAdminType::DATETIME => [
            'doctrineColumnType' => DoctrineType::DATETIME,
            'priority' => 3,
            'easyAdminType' => EasyAdminType::DATETIME,
        ],
        EasyAdminType::DATETIMETZ => [
            'doctrineColumnType' => DoctrineType::DATETIMETZ,
            'priority' => 3,
            'easyAdminType' => EasyAdminType::DATETIMETZ,
        ],
        EasyAdminType::OBJECT => [
            'doctrineColumnType' => DoctrineType::OBJECT,
            'priority' => 3,
            'easyAdminType' => EasyAdminType::OBJECT,
        ],
        EasyAdminType::SMALLINT => [
            'doctrineColumnType' => DoctrineType::SMALLINT,
            'priority' => 3,
            'easyAdminType' => EasyAdminType::SMALLINT,
        ],
        EasyAdminType::DECIMAL => [
            'doctrineColumnType' => DoctrineType::DECIMAL,
            'priority' => 3,
            'easyAdminType' => EasyAdminType::DECIMAL,
        ],
    ];

    /**
     * Sorts the types in order of priority.
     */
    public static function getTypesOrderedByPriorities(): void
    {
        uasort(self::$generatorTypesConfiguration, function (array $a, array $b) {
            return $a['priority'] <=> $b['priority'];
        });
    }

    /**
     * Return the guessed type of the property of the class.
     *
     * @param string $property
     * @param string $class
     *
     * @return string
     */
    public static function getGuessType(string $property, string $class): string
    {
        $annotationReader = new AnnotationReader();
        $propertyAnnotations = $annotationReader->getPropertyAnnotations(new \ReflectionProperty($class, $property));
        self::getTypesOrderedByPriorities();

        foreach (self::$generatorTypesConfiguration as $type => &$configuration) {
            $configuration = array_replace(self::$defaultConfigType, $configuration);
            $check = true;
            foreach ($configuration['mandatoryClasses'] as $class) {
                if (!PropertyHelper::hasClass($propertyAnnotations, $class)) {
                    $check = false;
                    break;
                }
            }

            if (!$check) {
                continue;
            }

            $check = false;
            foreach ($configuration['noMandatoryClasses'] as $class) {
                if (PropertyHelper::hasClass($propertyAnnotations, $class)) {
                    $check = true;
                    break;
                }
            }

            if (!$check && count($configuration['noMandatoryClasses']) > 0) {
                continue;
            }

            if (null !== $configuration['doctrineColumnType'] && !(PropertyHelper::hasDoctrineColumnType($propertyAnnotations, $configuration['doctrineColumnType']))) {
                continue;
            }

            return $configuration['easyAdminType'];
        }

        return EasyAdminType::STRING;
    }
}
