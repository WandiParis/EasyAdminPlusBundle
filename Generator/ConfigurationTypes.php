<?php

namespace Wandi\EasyAdminPlusBundle\Generator;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Gedmo\Mapping\Annotation\SortablePosition;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Ip;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Bic;
use Symfony\Component\Validator\Constraints\Iban;
use Symfony\Component\Validator\Constraints\Isbn;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Luhn;
use Symfony\Component\Validator\Constraints\Currency;
use Symfony\Component\Validator\Constraints\Country;
use Symfony\Component\Validator\Constraints\Language;
use Symfony\Component\Validator\Constraints\Locale;
use Symfony\Component\Validator\Constraints\CardScheme;
use Symfony\Component\Validator\Constraints\Issn;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\NotIdenticalTo;
use Symfony\Component\Validator\Constraints\NotEqualTo;
use Symfony\Component\Validator\Constraints\IdenticalTo;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Time;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\Date;
use Vich\UploaderBundle\Mapping\Annotation\UploadableField;
use Wandi\EasyAdminPlusBundle\Generator\Exception\EAException;
use Wandi\EasyAdminPlusBundle\Generator\Type\DoctrineType;
use Wandi\EasyAdminPlusBundle\Generator\Type\EasyAdminGeneratorType;
use Wandi\EasyAdminPlusBundle\Generator\Type\EasyAdminType;

class ConfigurationTypes
{
    /**
     * Configuration par default d'un EasyAdminGeneratorType
     */
    private static $defaultConfigType = [
        //Tous ces classes sont obligatoires
        'mandatoryClasses' => [],
        //Juste une des classes est obligatoire pour matcher
        'noMandatoryClasses' => [],
        //Priorité lors du match
        'priority' => 4,
        //Type easyAdmin
        'easyAdminType' => null,
        //Si on force le type
        'typeForced' => false,
        //Méthodes ou le type ne sera pas forcé (peu importe la valeur de 'typeForce')
        'methodsTypeForced' => [],
        //Méthodes ou la propriété ne sera pas affiché
        'methodsNoAllowed' => [],
        //Type doctrine nécessaire, si diff de null
        'doctrineColumnType' => null,
        'easyAdminGeneratorType' => EasyAdminGeneratorType::STRING
    ];

    /**
     * Liste des configurations EasyAdminGenerator
     */
    private static $generatorTypesConfiguration = [
        EasyAdminGeneratorType::VICH_IMAGE => [
            'mandatoryClasses' => [
                Image::class,
                UploadableField::class
            ],
            'priority' => 1,
            'easyAdminType' => EasyAdminType::VICH_IMAGE,
            'typeForced' => true,
            'easyAdminGeneratorType' => EasyAdminGeneratorType::VICH_IMAGE,
            'methodsNoAllowed' => [
                'list',
                'show',
            ],
        ],
        EasyAdminGeneratorType::STRING => [
            'priority' => 3,
            'easyAdminType' => EasyAdminType::STRING,
            'doctrineColumnType' => DoctrineType::STRING,
            'easyAdminGeneratorType' => EasyAdminGeneratorType::STRING,
        ],
        EasyAdminGeneratorType::INTEGER => [
            'priority' => 3,
            'easyAdminType' => EasyAdminType::INTEGER,
            'doctrineColumnType' => DoctrineType::INTEGER,
            'easyAdminGeneratorType' => EasyAdminGeneratorType::INTEGER,
        ],
        EasyAdminGeneratorType::TEXT => [
            'doctrineColumnType' => DoctrineType::TEXT,
            'priority' => 3,
            'easyAdminType' => EasyAdminType::RAW,
            'easyAdminGeneratorType' => EasyAdminGeneratorType::TEXT,
            'typeForced' => true,
            'methodsNoAllowed' => [
                'list',
            ],
            'methodsTypeForced' => [
                'new',
                'edit'
            ],
        ],
        EasyAdminGeneratorType::IMAGE => [
            'doctrineColumnType' => DoctrineType::STRING,
            'priority' => 4,
            'easyAdminType' => EasyAdminType::IMAGE,
            'easyAdminGeneratorType' => EasyAdminGeneratorType::IMAGE,
            'typeForced' => true,
            'methodsNoAllowed' => [
                'new',
                'edit'
            ],
        ],
        EasyAdminGeneratorType::BOOLEAN => [
            'doctrineColumnType' => DoctrineType::BOOLEAN,
            'priority' => 3,
            'easyAdminType' => EasyAdminType::BOOLEAN,
            'easyAdminGeneratorType' => EasyAdminGeneratorType::BOOLEAN,
            'typeForced' => true,
            'methodsTypeForced' => [
                'new',
                'edit'
            ],
        ],
        EasyAdminGeneratorType::POSITION => [
            'mandatoryClasses' => [
                SortablePosition::class
            ],
            'priority' => 2,
            'easyAdminType' => EasyAdminType::POSITION,
            'easyAdminGeneratorType' => EasyAdminGeneratorType::POSITION,
            'methodsNoAllowed' => [
                'add',
            ],
        ],
        EasyAdminGeneratorType::VICH_FILE => [
            'mandatoryClasses' => [
                UploadableField::class,
                File::class
            ],
            'priority' => 1,
            'easyAdminType' => EasyAdminType::VICH_FILE,
            'easyAdminGeneratorType' => EasyAdminGeneratorType::VICH_FILE,
            'typeForced' => true,
            'methodsNoAllowed' => [
                'list',
                'show'
            ],
        ],
        EasyAdminGeneratorType::FILE => [
            'doctrineColumnType' => DoctrineType::STRING,
            'priority' => 4,
            'easyAdminType' => EasyAdminType::FILE,
            'easyAdminGeneratorType' => EasyAdminGeneratorType::FILE,
            'typeForced' => true,
            'methodsNoAllowed' => [
                'new',
                'edit',
            ],
        ],
        EasyAdminGeneratorType::COLLECTION =>  [
            'priority' => 6,
            'easyAdminType' => EasyAdminType::COLLECTION,
            'easyAdminGeneratorType' => EasyAdminGeneratorType::COLLECTION,
            'typeForced' => true,
        ],
        EasyAdminGeneratorType::AUTOCOMPLETE =>  [
            'noMandatoryClasses' => [
                ManyToOne::class,
                OneToOne::class,
                ManyToMany::class,
                OneToMany::class,

            ],
            'priority' => 1,
            'easyAdminType' => EasyAdminType::AUTOCOMPLETE,
            'easyAdminGeneratorType' => EasyAdminGeneratorType::AUTOCOMPLETE,
            'typeForced' => true,
            'methodsTypeForced' => [
                'list',
                'show'
            ],
        ],
        EasyAdminGeneratorType::EMAIL => [
            'mandatoryClasses' => [
                Email::class
            ],
            'priority' => 2,
            'easyAdminType' => EasyAdminType::EMAIL,
            'easyAdminGeneratorType' => EasyAdminGeneratorType::EMAIL,
            'typeForced' => true,
            'methodsTypeForced' => [
                'edit',
                'new'
            ],
        ],
        EasyAdminGeneratorType::URL => [
            'mandatoryClasses' => [
                Url::class
            ],
            'priority' => 2,
            'easyAdminType' => EasyAdminType::URL,
            'easyAdminGeneratorType' => EasyAdminGeneratorType::URL,
            'typeForced' => true,
            'methodsTypeForced' => [
                'edit',
                'new'
            ],
        ],
        EasyAdminGeneratorType::IP => [
            'mandatoryClasses' => [
                Ip::class
            ],
            'priority' => 2,
            'easyAdminType' => EasyAdminType::STRING,
            'easyAdminGeneratorType' => EasyAdminGeneratorType::STRING,
        ],
        EasyAdminGeneratorType::CHOICE =>  [
            'mandatoryClasses' => [
                Choice::class
            ],
            'priority' => 2,
            'easyAdminType' => EasyAdminType::CHOICE,
            'easyAdminGeneratorType' => EasyAdminGeneratorType::CHOICE,
            'typeForced' => true,
            'methodsTypeForced' => [
                'list',
                'show'
            ],
        ],
        EasyAdminGeneratorType::FLOAT => [
            'doctrineColumnType' => DoctrineType::FLOAT,
            'priority' => 3,
            'easyAdminType' => EasyAdminType::FLOAT,
            'easyAdminGeneratorType' => EasyAdminGeneratorType::FLOAT,
        ],
        EasyAdminGeneratorType::BIGINT =>  [
            'doctrineColumnType' => DoctrineType::BIGINT,
            'priority' => 3,
            'easyAdminType' => EasyAdminType::BIGINT,
            'easyAdminGeneratorType' => EasyAdminGeneratorType::BIGINT,
        ],
        EasyAdminGeneratorType::ARRAY => [
            'doctrineColumnType' => DoctrineType::ARRAY,
            'priority' => 3,
            'easyAdminType' => EasyAdminType::ARRAY,
            'easyAdminGeneratorType' => EasyAdminGeneratorType::ARRAY,
        ],
        EasyAdminGeneratorType::SIMPLE_ARRAY =>  [
            'doctrineColumnType' => DoctrineType::SIMPLE_ARRAY,
            'priority' => 3,
            'easyAdminType' => EasyAdminType::SIMPLE_ARRAY,
            'easyAdminGeneratorType' => EasyAdminGeneratorType::SIMPLE_ARRAY,
        ],
        EasyAdminGeneratorType::JSON_ARRAY =>  [
            'doctrineColumnType' => DoctrineType::JSON_ARRAY,
            'priority' => 3,
            'easyAdminType' => EasyAdminType::JSON_ARRAY,
            'easyAdminGeneratorType' => EasyAdminGeneratorType::JSON_ARRAY,
        ],
        EasyAdminGeneratorType::TIME =>  [
            'doctrineColumnType' => DoctrineType::TIME,
            'priority' => 3,
            'easyAdminType' => EasyAdminType::TIME,
            'easyAdminGeneratorType' => EasyAdminGeneratorType::TIME,
        ],
        EasyAdminGeneratorType::DATE =>  [
            'doctrineColumnType' => DoctrineType::DATE,
            'priority' => 3,
            'easyAdminType' => EasyAdminType::DATE,
            'easyAdminGeneratorType' => EasyAdminGeneratorType::DATE
        ],
        EasyAdminGeneratorType::DATETIME => [
            'doctrineColumnType' => DoctrineType::DATETIME,
            'priority' => 3,
            'easyAdminType' => EasyAdminType::DATETIME,
            'easyAdminGeneratorType' => EasyAdminGeneratorType::DATETIME,
        ],
        EasyAdminGeneratorType::DATETIMETZ =>  [
            'doctrineColumnType' => DoctrineType::DATETIMETZ,
            'priority' => 3,
            'easyAdminType' => EasyAdminType::DATETIMETZ,
            'easyAdminGeneratorType' => EasyAdminGeneratorType::DATETIMETZ,
        ],
        EasyAdminGeneratorType::OBJECT =>  [
            'doctrineColumnType' => DoctrineType::OBJECT,
            'priority' => 3,
            'easyAdminType' => EasyAdminType::OBJECT,
            'easyAdminGeneratorType' => EasyAdminGeneratorType::OBJECT,
        ],
        EasyAdminGeneratorType::SMALLINT =>  [
            'doctrineColumnType' => DoctrineType::SMALLINT,
            'priority' => 3,
            'easyAdminType' => EasyAdminType::SMALLINT,
            'easyAdminGeneratorType' => EasyAdminGeneratorType::SMALLINT
        ],
        EasyAdminGeneratorType::DECIMAL => [
            'doctrineColumnType' => DoctrineType::DECIMAL,
            'priority' => 3,
            'easyAdminType' => EasyAdminType::DECIMAL,
            'easyAdminGeneratorType' => EasyAdminGeneratorType::DECIMAL,
        ],
    ];

    /**
     * Liste les types de type vich
     */
    private static $vichTypes = [
        EasyAdminGeneratorType::VICH_FILE,
        EasyAdminGeneratorType::VICH_IMAGE,
    ];

    /**
     * Liste des fonctions à éxécuter si une class est présente dans les annos d'une propriété
     */
    private static $typeHelpers = [
        EasyAdminGeneratorType::IMAGE =>  [
            'function' => 'handleImage',
            'methods' => [

            ]
        ],
        EasyAdminGeneratorType::DECIMAL =>  [
            'function' => 'handleDecimal',
            'methods' => [
                'list',
                'show',
            ]
        ],
        EasyAdminGeneratorType::AUTOCOMPLETE =>  [
            'function' => 'handleAutoComplete',
        ],
        EasyAdminGeneratorType::DATETIMETZ => [
            'function' => 'handleDatetimetz'
        ],
    ];

    /**
     * Array mask pour les helpers (classe et type)
     */
    private static $helperMask = [
        'function' => '',
        'methods' => [],
    ];

    /**
     * Liste des fonctions à éxécuter pour chaque type présent
     */
    private static $classHelpers = [
        SortablePosition::class => [
            'function' => 'handlePosition',
        ],
        Choice::class => [
            'function' => 'handleChoice',
        ],
        Image::class => [
            'function' => 'handleImage',
        ],
        UploadableField::class => [
            'function' => 'handleUploadableField',
        ],
        Range::class => [
            'function' => 'handleRange',
        ],
        Count::class => [
            'function' => 'handleCount',
        ],
        Bic::class => [
            'function' => 'handleBic',
        ],
        Iban::class => [
            'function' => 'handleIban',
        ],
        Isbn::class => [
            'function' => 'handleIsbn',
        ],
        Email::class => [
            'function' => 'handleEmail',
        ],
        url::class => [
            'function' => 'handleUrl',
        ],
        Regex::class => [
            'function' => 'handleRegex',
        ],
        Length::class => [
            'function' => 'handleLength',
        ],
        Luhn::class => [
            'function' => 'handleLuhn',
        ],
        Currency::class => [
            'function' => 'handleCurrency',
        ],
        Country::class => [
            'function' => 'handleCountry',
        ],
        Ip::class => [
            'function' => 'handleIp',
        ],
        Language::class => [
            'function' => 'handleLanguage',
        ],
        Locale::class => [
            'function' => 'handleLocale',
        ],
        CardScheme::class => [
            'function' => 'handleCardScheme',
        ],
        Issn::class => [
            'function' => 'handleIssn',
        ],
        EqualTo::class => [
            'function' => 'handleEqualTo',
        ],
        NotEqualTo::class => [
            'function' => 'handleNotEqualTo',
        ],
        IdenticalTo::class => [
            'function' => 'handleIdenticalTo',
        ],
        NotIdenticalTo::class => [
            'function' => 'handleNotIdenticalTo',
        ],
        LessThan::class => [
            'function' => 'handleLessThan',
        ],
        LessThanOrEqual::class => [
            'function' => 'handleLessThanOrEqual',
        ],
        GreaterThan::class => [
            'function' => 'handleGreaterThan',
        ],
        GreaterThanOrEqual::class => [
            'function' => 'handleGreaterThanOrEqual',
        ],
        Time::class => [
            'function' => 'handleTime',
        ],
        DateTime::class => [
            'function' => 'handleDateTime',
        ],
        Date::class => [
            'function' => 'handleDate',
        ],
    ];

    /**
     * @return mixed
     */
    public static function getTypeHelpers(): array
    {
        return self::$typeHelpers;
    }

    /**
     * @return mixed
     */
    public static function getClassHelpers(): array
    {
        return self::$classHelpers;
    }

    public static function setClassHelpers(array $helpers)
    {
        self::$classHelpers = $helpers;
    }

    /**
     * @return mixed
     */
    public static function getMaskHelper(): array
    {
        return self::$helperMask;
    }

    /**
     * Trie les types par ordre de priorité
     */
    public static function getTypesOrderedByPriorities(): void
    {
        uasort(self::$generatorTypesConfiguration, function(array $a, array $b)
        {
            return $a['priority'] <=> $b['priority'];
        });
    }

    /**
     * Devine le type de la propriété par rapport à ces annotations et le retourne
     * TODO: Mettre dans une methodes la gestion des classe obligatoires et non obligatoires
     * @param array $property
     * @return array
     */
    public static function getGuessedType(array $property): array
    {
        foreach (self::$generatorTypesConfiguration as $type => &$configuration)
        {
            $configuration = array_replace(self::$defaultConfigType, $configuration);
            $check = true;
            foreach ($configuration['mandatoryClasses'] as $class)
            {
                if (!self::hasClass($property['annotationClasses'], $class))
                {
                    $check = false;
                    break;
                }
            }

            if (!$check)
                continue;

            $check = false;
            foreach ($configuration['noMandatoryClasses'] as $class)
            {
                if (self::hasClass($property['annotationClasses'], $class))
                {
                    $check = true;
                    break ;
                }
            }

            if (!$check && count($configuration['noMandatoryClasses']) > 0)
                continue;

            if ($configuration['doctrineColumnType'] !== null && !(self::hasDoctrineColumnType($property['annotationClasses'], $configuration['doctrineColumnType'])))
                continue;

            return ['typeConfig' => $configuration];
        }

        //Sinon on retourne la configuration de EasyAdminGeneratorType::STRING
        return ['typeConfig' =>  self::$generatorTypesConfiguration[EasyAdminGeneratorType::STRING]];
    }

    /**
     * Récupère la classe spécifié dans les annotations
     * @param $arrayClasses
     * @param $classTargeted
     * @return null
     */
    public static function getClassFromArray(array $arrayClasses, string $classTargeted)
    {
        $class = array_filter($arrayClasses, function($class) use ($classTargeted){
            return ($class instanceof $classTargeted);
        });

        return array_values($class)[0] ?? null;
    }

    /**
     * Vérifie si une propriété possède une class dans ces annotations
     * @param $propertyClasses
     * @param $classTargeted
     * @return bool
     */
    public static function hasClass(array $propertyClasses, string $classTargeted): bool
    {
        return self::getClassFromArray($propertyClasses, $classTargeted) != null;
    }

    /**
     * Vérifie si une propriété est une colonne SQL et si elle possède le même type que celui spécifié
     * @param $propertyDoctrineClasses
     * @param $doctrineTargetType
     * @return bool
     */
    private static function hasDoctrineColumnType(array $propertyDoctrineClasses, string $doctrineTargetType): bool
    {
        $column = self::getClassFromArray($propertyDoctrineClasses, Column::class);

        if (!$column)
            return false;

        /** @var Column $column */
        return ($column->type == $doctrineTargetType);
    }

    /**
     * TODO: Revoir l'algo
     * @param array $properties
     * @return array
     * @throws EAException
     */
    public static function setVichPropertiesConfig(array $properties): array
    {
        $vichProperties = array_filter($properties, function($property) {
            return in_array($property['typeConfig']['easyAdminGeneratorType'], self::$vichTypes);
        });

        foreach ($vichProperties as $vichProperty)
        {
            //On recupère UploadableField
            $uploadableField = self::getClassFromArray($vichProperty['annotationClasses'], UploadableField::class);

            /** @var UploadableField $uploadableField */
            if (!$uploadableField)
                continue;

            //On récupère la propriété lié à l'image
            $propertyTargeted = array_values(array_filter($properties, function($property) use ($uploadableField) {
                return ($property['name'] == $uploadableField->getFileNameProperty());
            }));

            $propertyTargeted = $propertyTargeted[0] ?? null;

            if (!$propertyTargeted)
                throw new EAException("Bad fileNameProperty (Vich property)");

            //On récupère la config lié au type
            $config = array_values(array_filter(self::$generatorTypesConfiguration, function($type) {
                return (EasyAdminGeneratorType::IMAGE == $type['easyAdminGeneratorType']);
            }))[0];

            foreach ($properties as &$property)
            {
                if ($property === $propertyTargeted)
                {
                    //On lui attribue sa nouvelle config et la class UploadableField
                    $property['typeConfig'] = array_replace(self::$defaultConfigType, $config);
                    $property['annotationClasses'][] = $uploadableField;
                }
            }
        }

        return $properties;
    }

    /**
     * @param mixed $typeHelpers
     */
    public static function setTypeHelpers(array $typeHelpers): void
    {
        self::$typeHelpers = $typeHelpers;
    }
}