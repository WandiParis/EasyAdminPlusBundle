<?php

namespace Lle\EasyAdminPlusBundle\Form\Type;

use EasyCorp\Bundle\EasyAdminBundle\DataCollector\EasyAdminDataCollector;
use EasyCorp\Bundle\EasyAdminBundle\DependencyInjection\Compiler\EasyAdminConfigPass;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Router;
use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigManager;
use Doctrine\ORM\EntityManagerInterface;
use Lle\EasyAdminPlusBundle\Form\DataTransformer\EntityToIdTransformer;
use Symfony\Component\Form\FormBuilderInterface;

class UrlAutocompleteType extends AbstractType
{

    private $router;
    private $configManager;
    private $em;

    public function __construct(Router $router, ConfigManager $configManager, EntityManagerInterface $em){
        $this->router = $router;
        $this->configManager = $configManager;
        $this->em = $em;
    }

    public function getParent()
    {
        return TextType::class;
    }

    public function getName()
    {
        return 'lle.easyadminplus.url_autocomplete';
    }



    public function configureOptions(OptionsResolver $resolver){
        $resolver->setDefaults([
            "url" => "#",
            "path" => null,
            "class" => null,
            "value_filter" => null,
            "placeholder" => 'form.url_auto_complet.placeholder' 
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options){
        $view->vars['url'] = $options['url'];
        $view->vars['value_label'] = $view->vars['value'];
        if ($options['placeholder']) {
            $view->vars['placeholder'] = $options['placeholder'];
        }
        if($options['path']){
            $path = $options['path'];
            $path['params']['action'] = $path['params']['action'] ?? 'autocomplete';
            $path['route'] = $path['route'] ?? 'easyadmin';
            $view->vars['url'] = $this->router->generate($path['route'], $path['params']);
        }elseif($options['class']){
            $config = $this->configManager->getEntityConfigByClass($options['class']);
            $view->vars['url'] = $this->router->generate('easyadmin', ['action'=>'autocomplete', 'entity'=>$config['name']]);
            if($options['value_filter'] === null and $view->vars['value']) {
                $view->vars['value_label'] = $this->em->getRepository($options['class'])->find($view->vars['value']) ?? $view->vars['value'];
            }
        }

        $view->vars['value_filter'] = $options['value_filter'];
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if( $options['class']) {
            $transformer = new EntityToIdTransformer($this->em);
            $transformer->setClass($options['class']);
            $builder->addModelTransformer($transformer);
        }
    }
}
