<?php

namespace Wandi\EasyAdminPlusBundle\Generator\Helper;

use Gedmo\Mapping\Annotation\SortablePosition;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Image;
use Vich\UploaderBundle\Mapping\Annotation\UploadableField;
use Doctrine\ORM\Mapping\Column;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Bic;
use Symfony\Component\Validator\Constraints\Iban;
use Symfony\Component\Validator\Constraints\Isbn;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Luhn;
use Symfony\Component\Validator\Constraints\Currency;
use Symfony\Component\Validator\Constraints\Country;
use Symfony\Component\Validator\Constraints\Ip;
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
use Wandi\EasyAdminPlusBundle\Generator\GeneratorTool;
use Wandi\EasyAdminPlusBundle\Generator\Model\Entity;
use Wandi\EasyAdminPlusBundle\Generator\Exception\EAException;
use Wandi\EasyAdminPlusBundle\Generator\Model\Field;
use Wandi\EasyAdminPlusBundle\Generator\Model\Method;

class PropertyClassHelper extends AbstractPropertyHelper
{
    const BIC_REGEX = '[a-zA-Z]{4}[a-zA-Z]{2}[a-zA-Z0-9]{2}([a-zA-Z0-9]{3})';
    const ISBN10_REGEX = '^ISBN:(\d{9}(?:\d|X))$';
    const ISBN13_REGEX = '^ISBN:(\d{12}(?:\d|X))$';
    const ISBN_REGEX = '(?:(?=.{17}$)97[89][ -](?:[0-9]+[ -]){2}[0-9]+[ -][0-9]|97[89][0-9]{10}|(?=.{13}$)(?:[0-9]+[ -]){2}[0-9]+[ -][0-9Xx]|[0-9]{9}[0-9Xx])';
    const DIGIT_REGEX = '^[0-9]+$';
    const IPV4_REGEX = '^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)(\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)){3}$';
    const IPV6_REGEX = '^s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]d|1dd|[1-9]?d)(.(25[0-5]|2[0-4]d|1dd|[1-9]?d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]d|1dd|[1-9]?d)(.(25[0-5]|2[0-4]d|1dd|[1-9]?d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]d|1dd|[1-9]?d)(.(25[0-5]|2[0-4]d|1dd|[1-9]?d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]d|1dd|[1-9]?d)(.(25[0-5]|2[0-4]d|1dd|[1-9]?d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]d|1dd|[1-9]?d)(.(25[0-5]|2[0-4]d|1dd|[1-9]?d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]d|1dd|[1-9]?d)(.(25[0-5]|2[0-4]d|1dd|[1-9]?d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]d|1dd|[1-9]?d)(.(25[0-5]|2[0-4]d|1dd|[1-9]?d)){3}))|:)))(%.+)?s*';
    const IPALL_REGEX = '('.self::IPV4_REGEX.')|('.self::IPV6_REGEX.')';
    const NUMBER_REGEX = '^[0-9]*$';

    /**
     * List of functions to execute for each type present.
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
    public static function getClassHelpers(): array
    {
        return self::$classHelpers;
    }

    public static function setClassHelpers(array $helpers)
    {
        self::$classHelpers = $helpers;
    }

    /**
     * @param SortablePosition $class
     * @param Field            $field
     * @param Entity           $entity
     * @param Method           $method
     */
    public static function handlePosition(SortablePosition $class, Field $field, Entity $entity, Method $method): void
    {
        $typeOptions = $field->getTypeOptions();
        if (!isset($typeOptions['attr']['min'])) {
            $typeOptions['attr']['min'] = 0;
            $field->setTypeOptions($typeOptions);
        }
    }

    /**
     * @param Image  $image
     * @param Field  $field
     * @param Entity $entity
     * @param Method $method
     */
    public static function handleImage(Image $image, Field $field, Entity $entity, Method $method): void
    {
        /** @var Translator $translator */
        $translator = GeneratorTool::getTranslation();
        $helpMessage = [];

        $mimes = self::getMimesToString((array) $image->mimeTypes);
        $helpMessage[] = $translator->trans('generator.image.mime', ['%mimes%' => $mimes]);

        if ($image->minRatio && $image->maxRatio) {
            $helpMessage[] = ($image->minRatio == $image->maxRatio) ? $translator->trans('generator.image.ratio.equal', ['%equal%' => $image->minRatio])
                : $translator->trans('generator.image.ratio.interval', [
                        '%min%' => $image->minRatio,
                        '%max%' => $image->maxRatio,
                    ]
                );
        }

        $helpMessage[] = self::buildImageHelpMessageDimension($image);

        if ($image->minRatio && !$image->maxRatio) {
            $helpMessage[] = $translator->trans('generator.image.ratio.min', ['%min%' => $image->minRatio]);
        }
        if (!$image->minRatio && $image->maxRatio) {
            $helpMessage[] = $translator->trans('generator.image.ratio.max', ['%max%' => $image->maxRatio]);
        }

        $field->setHelp(self::buildHelpMessage($helpMessage));
    }

    /**
     * Generates the help message about the dimensions of an image.
     */
    private static function buildImageHelpMessageDimension(Image $image): string
    {
        $helpMessage = '';

        if ($image->minHeight) {
            if ($image->maxHeight) {
                if ($image->minWidth) {
                    if ($image->maxWidth) {
                        if ($image->minHeight == $image->maxHeight) {
                            if ($image->minWidth == $image->maxWidth) {
                                $helpMessage = 'image doit faire X*Y';
                            } else {
                                $helpMessage = 'Image doit faire X de hauteur et entre X et Y de largeur';
                            }
                        } else {
                            if ($image->minWidth == $image->maxWidth) {
                                $helpMessage = 'Image doit faire entre X et Y de hauteur et Y de largeur';
                            } else {
                                $helpMessage = 'Image doit faire entre X et Y de hauteur et X et Y de largeur';
                            }
                        }
                    } else { //minH, maxH, minW
                        if ($image->minHeight == $image->maxHeight) {
                            $helpMessage = "l'image doit faire X de hauteur et doit dépasser X en largeur";
                        } else {
                            $helpMessage = "L'image doit faire entre X et Y en hauteur et doit dépasser X en largeur";
                        }
                    }
                } else { //minH, maxH, maxW ?
                    if ($image->maxWidth) { //minH, maxH, maxW
                        if ($image->minHeight == $image->maxHeight) {
                            $helpMessage = 'Image doit faire X en hauteur et ne doit pas dépaaser X en largeur';
                        } else {
                            $helpMessage = 'Image doit faire entre X et Y en hauteur et ne doit pas dépasser X en largeur';
                        }
                    } else {
                        if ($image->minHeight == $image->maxHeight) {
                            $helpMessage = 'Image doit faire X en hauteur.';
                        } else {
                            $helpMessage = 'Image doit faire entre X et Y en hauteur.';
                        }
                    }
                }
            } else { //minH, minW ? , maxW ?
                if ($image->minWidth) {
                    if ($image->maxWidth) {
                        if ($image->minWidth == $image->maxWidth) {
                            $helpMessage = 'Image doit dépasser X en largeur et faire X en hauteur';
                        } else {
                            $helpMessage = 'Image doit dépasser X en largeur et doit faire entre X et Y en hauteur';
                        }
                    } else { // minH, minW
                        $helpMessage = 'Image doit dépasser X en hauteur et X en largeur';
                    }
                } else { // minH, maxW ?
                    if ($image->maxWidth) {
                        $helpMessage = 'Image doit dépasser X en hauteur et ne doit pas dépasser X en largeur';
                    } else {
                        $helpMessage = 'Image doit dépasser X en hauteur';
                    }
                }
            }
        } else { //maxH ?, minW ? , maxW ?
            if ($image->maxHeight) {
                if ($image->minWidth) {
                    if ($image->maxWidth) {
                        if ($image->minWidth == $image->maxWidth) {
                            $helpMessage = 'Image doit faire X en largeur et ne doit pas dépaaser X en hauteur';
                        }
                    } else { //maxH, minW
                        $helpMessage = 'Image doit ne doit pas dépaaser X en hauteur et doit faire au minimum X en largeur';
                    }
                } else { //maxH , maxW ?
                    if ($image->maxWidth) {
                        $helpMessage = 'Image ne doit pas dépasser X en hauteur et ne doit pas dépasser X en largeur';
                    } else {
                        $helpMessage = 'Image ne doit pas dépasser X en hauteur';
                    }
                }
            } else { // minW ? maxW ?
                if ($image->minWidth) {
                    if ($image->maxWidth) {
                        if ($image->minWidth == $image->maxWidth) {
                            $helpMessage = 'Image doit faire X en largeur';
                        } else {
                            $helpMessage = 'Image doit faire entre X et Y en largeur';
                        }
                    } else { // minW
                        $helpMessage = 'Image doit faire au minimum X de largeur';
                    }
                } else { //maxW ?
                    if ($image->maxWidth) {
                        $helpMessage = 'Image ne doit pas dépasser X en largeur';
                    }
                }
            }
        }

        return $helpMessage;
    }

    /**
     * Returns the list of mimes in a string.
     *
     * @param array $mimes
     *
     * @return string
     */
    private static function getMimesToString(array $mimes): string
    {
        $mimesString = '';
        foreach ($mimes as $mime) {
            $mimeExploded = explode('/', $mime);
            if (empty($mimeExploded) || 2 != count($mimeExploded)) {
                continue;
            }
            $mimesString .= ('' != $mimesString) ? ', '.$mimeExploded[1] : $mimeExploded[1];
        }

        return $mimesString;
    }

    /**
     * @param array $messages
     *
     * @return string
     */
    private static function buildHelpMessage(array $messages): string
    {
        $helpMessage = '';
        foreach ($messages as $message) {
            $helpMessage .= $message.'</br>';
        }

        return $helpMessage;
    }

    /**
     * @param Choice $choice
     * @param Field  $field
     * @param Entity $entity
     * @param Method $method
     *
     * @throws EAException
     */
    public static function handleChoice(Choice $choice, Field $field, Entity $entity, Method $method): void
    {
        if (!is_array($choice->choices) && !$choice->callback) {
            return;
        }

        if (!is_array($choice->choices) && !$choice->callback) {
            throw new EAException('Either "choices" or "callback" must be specified on constraint Choice');
        }

        if ($choice->callback) {
            $object = $entity->getMetaData()->getName();
            if (!is_callable($choices = array(new $object(), $choice->callback))
                && !is_callable($choice->callback)
            ) {
                throw new EAException('The Choice constraint expects a valid callback');
            }
            $choices = call_user_func($choices);
        } else {
            $choices = $choice->choices;
        }

        if (!self::isAssoc($choices)) {
            $choices = array_combine($choices, $choices);
        }

        $typeOptions = $field->getTypeOptions();
        $typeOptions['choices'] = $choices;
        $field->setTypeOptions($typeOptions);
    }

    /**
     * If the linked field has the nullable option set to false,
     * set the allow_delete option to felse for the field currant.
     *
     * @param UploadableField $uploadableField
     * @param Field           $field
     * @param Entity          $entity
     * @param Method          $method
     *
     * @throws EAException
     */
    public static function handleUploadableField(UploadableField $uploadableField, Field $field, Entity $entity, Method $method): void
    {
        $propertyLinkedName = $uploadableField->getFileNameProperty();
        $propertyLinked = array_values(array_filter($entity->getProperties(), function ($property) use ($propertyLinkedName) {
            return $property['name'] == $propertyLinkedName;
        }));

        if (!isset($propertyLinked[0])) {
            throw new EAException('The UploadableField does not have a valid file name property');
        }
        $column = PropertyHelper::getClassFromArray($propertyLinked[0]['annotationClasses'], Column::class);
        if (!$column->nullable) {
            $typeOptions = $field->getTypeOptions();
            $typeOptions['allow_delete'] = false;
            $field->setTypeOptions($typeOptions);
        }
    }

    /**
     * Check if it is an associative array.
     *
     * @param array $array
     *
     * @return bool
     */
    public static function isAssoc(array $array): bool
    {
        return $array !== array_values($array);
    }

    /**
     * @param Range  $range
     * @param Field  $field
     * @param Entity $entity
     * @param Method $method
     */
    public static function handleRange(Range $range, Field $field, Entity $entity, Method $method): void
    {
        /** @var Translator $translator */
        $translator = GeneratorTool::getTranslation();


        if (null !== $range->min && null !== $range->max) {
            if ($range->min == $range->max) {
                $helpMessage = $translator->trans('generator.range.equal', ['%value%' => $range->min]);
            } else {
                $helpMessage = $translator->trans('generator.range.interval', [
                    '%min%' => $range->min,
                    '%max%' => $range->max,
                ]);
            }
        }

        if (is_numeric($range->min) && null === $range->max) {
            $helpMessage = $translator->trans('generator.range.min', ['%min%' => $range->min]);
        }
        if (null === $range->min && is_numeric($range->max)) {
            $helpMessage = $translator->trans('generator.range.max', ['%max%' => $range->max]);
        }

        $field->setHelp($helpMessage);
    }

    /**
     * @param Count  $count
     * @param Field  $field
     * @param Entity $entity
     * @param Method $method
     */
    public static function handleCount(Count $count, Field $field, Entity $entity, Method $method): void
    {
        /** @var Translator $translator */
        $translator = GeneratorTool::getTranslation();

        if (null !== $count->min && null !== $count->max) {
            if ($count->min == $count->max) {
                $helpMessage = $translator->trans('generator.count.equal', ['%value%' => $count->min]);
            } else {
                $helpMessage = $translator->trans('generator.count.interval', [
                    '%min%' => $count->min,
                    '%max%' => $count->max,
                ]);
            }
        }

        if (is_numeric($count->min) && null === $count->max) {
            $helpMessage = $translator->trans('generator.count.min', ['%min%' => $count->min]);
        }
        if (null === $count->min && is_numeric($count->max)) {
            $helpMessage = $translator->trans('generator.count.max', ['%max%' => $count->max]);
        }

        $field->setHelp($helpMessage);
    }
    
    /**
     * @param Bic    $bic
     * @param Field  $field
     * @param Entity $entity
     * @param Method $method
     */
    public static function handleBic(Bic $bic, Field $field, Entity $entity, Method $method): void
    {
        /** @var Translator $translator */
        $translator = GeneratorTool::getTranslation();
        $helpMessage = $translator->trans('generator.bic.help');
        $field->setHelp($helpMessage);

        $typeOptions = $field->getTypeOptions();
        $typeOptions['attr']['pattern'] = self::BIC_REGEX;
        $field->setTypeOptions($typeOptions);
    }

    /**
     * @param Iban   $iban
     * @param Field  $field
     * @param Entity $entity
     * @param Method $method
     */
    public static function handleIban(Iban $iban, Field $field, Entity $entity, Method $method): void
    {
        /** @var Translator $translator */
        $translator = GeneratorTool::getTranslation();
        $helpMessage = $translator->trans('generator.iban.help');
        $field->setHelp($helpMessage);
    }

    /**
     * @param Isbn   $isbn
     * @param Field  $field
     * @param Entity $entity
     * @param Method $method
     */
    public static function handleIsbn(Isbn $isbn, Field $field, Entity $entity, Method $method): void
    {
        /** @var Translator $translator */
        $translator = GeneratorTool::getTranslation();

        $regex = (null != $isbn->type) ? ('isbn10' == $isbn->type) ? self::ISBN10_REGEX : self::ISBN13_REGEX : self::ISBN_REGEX;
        $idTranslation = (null != $isbn->type) ? ('isbn10' == $isbn->type) ? '10' : '13' : 'both';

        $typeOptions = $field->getTypeOptions();
        $typeOptions['attr']['pattern'] = $regex;
        $field->setTypeOptions($typeOptions);

        $helpMessage = $translator->trans('generator.isbn.'.$idTranslation);
        $field->setHelp($helpMessage);
    }

    /**
     * @param Email  $email
     * @param Field  $field
     * @param Entity $entity
     * @param Method $method
     */
    public static function handleEmail(Email $email, Field $field, Entity $entity, Method $method): void
    {
        /** @var Translator $translator */
        $translator = GeneratorTool::getTranslation();
        $helpMessage = $translator->trans('generator.email.help');
        $field->setHelp($helpMessage);
    }

    /**
     * @param Url    $url
     * @param Field  $field
     * @param Entity $entity
     * @param Method $method
     *
     * @throws EAException
     */
    public static function handleUrl(Url $url, Field $field, Entity $entity, Method $method): void
    {
        /** @var Translator $translator */
        $translator = GeneratorTool::getTranslation();

        if (empty($url->protocols)) {
            throw new EAException('No authorized protocols (property -> '.$field->getName().')');
        }
        $protocols = implode(', ', $url->protocols);
        $helpMessage[] = $translator->trans('generator.url.protocols', ['%protocols' => $protocols]);
        $helpMessage[] = $translator->trans('generator.url.help');
        $field->setHelp(self::buildHelpMessage($helpMessage));
    }

    /**
     * @param Regex  $regex
     * @param Field  $field
     * @param Entity $entity
     * @param Method $method
     */
    public static function handleRegex(Regex $regex, Field $field, Entity $entity, Method $method): void
    {
        if ($regex->match) {
            $typeOptions = $field->getTypeOptions();
            $typeOptions['attr']['pattern'] = $regex->pattern;
            $field->setTypeOptions($typeOptions);
        }
    }

    /**
     * @param Length $length
     * @param Field  $field
     * @param Entity $entity
     * @param Method $method
     */
    public static function handleLength(Length $length, Field $field, Entity $entity, Method $method): void
    {
        /** @var Translator $translator */
        $translator = GeneratorTool::getTranslation();

        if ($length->min && $length->max) {
            if ($length->min == $length->max) {
                $helpMessage = $translator->trans('generator.length.equal', ['%equal%' => $length->min]);
            } else {
                $helpMessage = $translator->trans('generator.length.interval', [
                    '%min%' => $length->min,
                    '%max%' => $length->max,
                ]);
            }
        }
        if ($length->min && !$length->max) {
            $helpMessage = $translator->trans('generator.length.min', ['%min%' => $length->min]);
        }
        if (!$length->min && $length->max) {
            $helpMessage = $translator->trans('generator.length.max', ['%max%' => $length->max]);
        }

        $field->setHelp($helpMessage);
    }

    /**
     * @param Luhn   $luhn
     * @param Field  $field
     * @param Entity $entity
     * @param Method $method
     */
    public static function handleLuhn(Luhn $luhn, Field $field, Entity $entity, Method $method): void
    {
        /** @var Translator $translator */
        $translator = GeneratorTool::getTranslation();
        $helpMessage = $translator->trans('generator.luhn.help');
        $field->setHelp($helpMessage);

        $typeOptions = $field->getTypeOptions();
        $typeOptions['attr']['pattern'] = self::DIGIT_REGEX;
        $field->setTypeOptions($typeOptions);
    }

    /**
     * ISO 4217.
     *
     * @param Currency $currency
     * @param Field    $field
     * @param Entity   $entity
     * @param Method   $method
     */
    public static function handleCurrency(Currency $currency, Field $field, Entity $entity, Method $method): void
    {
        $typeOptions = $field->getTypeOptions();
        $typeOptions['choices'] = array_flip(Intl::getCurrencyBundle()->getCurrencyNames());
        $field->setTypeOptions($typeOptions);
    }

    /**
     * ISO 3166-1.
     *
     * @param Country $country
     * @param Field   $field
     * @param Entity  $entity
     * @param Method  $method
     */
    public static function handleCountry(Country $country, Field $field, Entity $entity, Method $method): void
    {
        $typeOptions = $field->getTypeOptions();
        $typeOptions['choices'] = array_flip(Intl::getRegionBundle()->getCountryNames());
        $field->setTypeOptions($typeOptions);
    }

    /**
     * @param Ip     $ip
     * @param Field  $field
     * @param Entity $entity
     * @param Method $method
     */
    public static function handleIp(Ip $ip, Field $field, Entity $entity, Method $method): void
    {
        /** @var Translator $translator */
        $translator = GeneratorTool::getTranslation();
        $helpMessage = '';
        $pattern = '';

        if ('4' == $ip->version) {
            $helpMessage = $translator->trans('generator.ip.v4');
            $pattern = self::IPV4_REGEX;
        } elseif ('6' == $ip->version) {
            $helpMessage = $translator->trans('generator.ip.v6');
            $pattern = self::IPV6_REGEX;
        } elseif ('all' == $ip->version) {
            $helpMessage = $translator->trans('generator.ip.all');
            $pattern = self::IPALL_REGEX;
        }

        $field->setHelp($helpMessage);
        $typeOptions = $field->getTypeOptions();
        $typeOptions['attr']['pattern'] = $pattern;
        $field->setTypeOptions($typeOptions);
    }

    /**
     * RFC 3066.
     *
     * @param Language $language
     * @param Field    $field
     * @param Entity   $entity
     * @param Method   $method
     */
    public static function handleLanguage(Language $language, Field $field, Entity $entity, Method $method): void
    {
        $typeOptions = $field->getTypeOptions();
        $typeOptions['choices'] = array_flip($languages = Intl::getLocaleBundle()->getLocaleNames());
        $field->setTypeOptions($typeOptions);
    }

    /**
     * ISO 639-1.
     *
     * @param Locale $locale
     * @param Field  $field
     * @param Entity $entity
     * @param Method $method
     */
    public static function handleLocale(Locale $locale, Field $field, Entity $entity, Method $method): void
    {
        $typeOptions = $field->getTypeOptions();
        $typeOptions['choices'] = array_flip($locales = Intl::getLocaleBundle()->getLocaleNames());
        $field->setTypeOptions($typeOptions);
    }

    /**
     * @param CardScheme $cardScheme
     * @param Field      $field
     * @param Entity     $entity
     * @param Method     $method
     */
    public static function handleCardScheme(CardScheme $cardScheme, Field $field, Entity $entity, Method $method): void
    {
        $typeOptions = $field->getTypeOptions();
        $typeOptions['attr']['pattern'] = self::NUMBER_REGEX;
        $field->setTypeOptions($typeOptions);
    }

    /**
     * @param Issn   $issn
     * @param Field  $field
     * @param Entity $entity
     * @param Method $method
     */
    public static function handleIssn(Issn $issn, Field $field, Entity $entity, Method $method): void
    {
        /** @var Translator $translator */
        $translator = GeneratorTool::getTranslation();
        $helpMessage = $translator->trans('generator.issn.help');
        $field->setHelp($helpMessage);
    }

    /**
     * @param EqualTo $equalTo
     * @param Field   $field
     * @param Entity  $entity
     * @param Method  $method
     */
    public static function handleEqualTo(EqualTo $equalTo, Field $field, Entity $entity, Method $method): void
    {
        /** @var Translator $translator */
        $translator = GeneratorTool::getTranslation();
        $helpMessage = $translator->trans('generator.equal_to.help', ['%value%' => $equalTo->value]);
        $field->setHelp($helpMessage);
    }

    /**
     * @param NotEqualTo $notEqualTo
     * @param Field      $field
     * @param Entity     $entity
     * @param Method     $method
     */
    public static function handleNotEqualTo(NotEqualTo $notEqualTo, Field $field, Entity $entity, Method $method): void
    {
        /** @var Translator $translator */
        $translator = GeneratorTool::getTranslation();
        $helpMessage = $translator->trans('generator.not_equal_to.help', ['%value%' => $notEqualTo->value]);
        $field->setHelp($helpMessage);
    }

    /**
     * @param IdenticalTo $identicalTo
     * @param Field       $field
     * @param Entity      $entity
     * @param Method      $method
     */
    public static function handleIdenticalTo(IdenticalTo $identicalTo, Field $field, Entity $entity, Method $method): void
    {
        /** @var Translator $translator */
        $translator = GeneratorTool::getTranslation();
        $helpMessage = $translator->trans('generator.identical_to.help', ['%value%' => $identicalTo->value]);
        $field->setHelp($helpMessage);
    }

    /**
     * @param NotIdenticalTo $notIdenticalTo
     * @param Field          $field
     * @param Entity         $entity
     * @param Method         $method
     */
    public static function handleNotIdenticalTo(NotIdenticalTo $notIdenticalTo, Field $field, Entity $entity, Method $method): void
    {
        /** @var Translator $translator */
        $translator = GeneratorTool::getTranslation();
        $helpMessage = $translator->trans('generator.not_identical_to.help', ['%value%' => $notIdenticalTo->value]);
        $field->setHelp($helpMessage);
    }

    /**
     * @param LessThan $lessThan
     * @param Field    $field
     * @param Entity   $entity
     * @param Method   $method
     */
    public static function handleLessThan(LessThan $lessThan, Field $field, Entity $entity, Method $method): void
    {
        /** @var Translator $translator */
        $translator = GeneratorTool::getTranslation();
        $helpMessage = $translator->trans('generator.less_than.help', ['%value%' => $lessThan->value]);
        $field->setHelp($helpMessage);
    }

    /**
     * @param LessThanOrEqual $lessThanOrEqual
     * @param Field           $field
     * @param Entity          $entity
     * @param Method          $method
     */
    public static function handleLessThanOrEqual(LessThanOrEqual $lessThanOrEqual, Field $field, Entity $entity, Method $method): void
    {
        /** @var Translator $translator */
        $translator = GeneratorTool::getTranslation();
        $helpMessage = $translator->trans('generator.less_than_or_equal.help', ['%value%' => $lessThanOrEqual->value]);
        $field->setHelp($helpMessage);
    }

    /**
     * @param GreaterThan $greaterThan
     * @param Field       $field
     * @param Entity      $entity
     * @param Method      $method
     */
    public static function handleGreaterThan(GreaterThan $greaterThan, Field $field, Entity $entity, Method $method): void
    {
        /** @var Translator $translator */
        $translator = GeneratorTool::getTranslation();
        $helpMessage = $translator->trans('generator.greater_than.help', ['%value%' => $greaterThan->value]);
        $field->setHelp($helpMessage);
    }

    /**
     * @param GreaterThanOrEqual $greaterThanOrEqual
     * @param Field              $field
     * @param Entity             $entity
     * @param Method             $method
     */
    public static function handleGreaterThanOrEqual(GreaterThanOrEqual $greaterThanOrEqual, Field $field, Entity $entity, Method $method): void
    {
        /** @var Translator $translator */
        $translator = GeneratorTool::getTranslation();
        $helpMessage = $translator->trans('generator.greater_than_or_equal.help', ['%value%' => $greaterThanOrEqual->value]);
        $field->setHelp($helpMessage);
    }

    /**
     * @param DateTime $dateTime
     * @param Field    $field
     * @param Entity   $entity
     * @param Method   $method
     */
    public static function handleDateTime(DateTime $dateTime, Field $field, Entity $entity, Method $method): void
    {
        //dump($dateTime);die();
    }

    /**
     * @param Date   $date
     * @param Field  $field
     * @param Entity $entity
     * @param Method $method
     */
    public static function handleDate(Date $date, Field $field, Entity $entity, Method $method): void
    {
        //dump($date);die();
    }

    /**
     * @param Time   $time
     * @param Field  $field
     * @param Entity $entity
     * @param Method $method
     */
    public static function handleTime(Time $time, Field $field, Entity $entity, Method $method): void
    {
        //dump($time);die();
    }
}
