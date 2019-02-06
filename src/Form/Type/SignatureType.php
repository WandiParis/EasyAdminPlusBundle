<?php
namespace Lle\EasyAdminPlusBundle\Form\Type;



use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SignatureType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'compound' => false,
        ));
    }

    public function getParent()
    {
        return TextType::class;
    }
}