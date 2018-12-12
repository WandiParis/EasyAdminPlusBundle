<?php
/**
 *  This file is part of the Lego project.
 *
 *   (c) Joris Saenger <joris.saenger@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */
namespace Lle\EasyAdminPlusBundle\Form\Type;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lle\EasyAdminPlusBundle\Form\DataTransformer\EntityToIdTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\Form\Extension\Core\Type\HiddenType as ParentType;

class EntityHiddenType extends AbstractType
{
    private $em;
    
    public function __construct($em){
        $this->em = $em;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'class'     => null,
        ));
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach(array('class') as $k){
            if($options[$k] == null){
                throw new InvalidConfigurationException('Option "'.$k.'" must be set.');
            }
        }
        $transformer = new EntityToIdTransformer($this->em, $options['class']);
        $builder->addModelTransformer($transformer);
    }
    public function getParent()
    {
        return ParentType::class;
    }
    public function getName()
    {
        return 'lego_entity_hidden';
    }
}