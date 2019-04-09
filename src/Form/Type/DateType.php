<?php


namespace Lle\EasyAdminPlusBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Extension\Core\Type\DateType as ParentType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lle\EasyAdminPlusBundle\Form\DataTransformer\DateTransformer;


class DateType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'widget'        => 'single_text',
            'format'        => 'dd/MM/yyyy',
            'js_date_format'   => 'dd/mm/yy',
            'min_day'       => null,
            'max_day'       => null,
            'no-day'        => array(),
            'edit_year'     => true,
            'edit_month'    => true,
            'show_diff'     => false,
            'js'            => true,
            'placeholder'   => null,
            'is_birthday'   => null,
        ));
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new DateTransformer());
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['id_date_picker'] = $view->vars['id'];
        $view->vars['date_format'] = $options['js_date_format'];
        $view->vars['placeholder'] = (is_array($options['placeholder']) && isset($options['placeholder']['year']))? $options['placeholder']['year']:null;
        $view->vars['min'] = (is_string($options['min_day']))? '"'.$options['min_day'].'"':null;
        $view->vars['max'] = (is_string($options['max_day']))? '"'.$options['max_day'].'"':null;
        $view->vars['noday'] = json_encode($options['no-day']);
        $view->vars['edit_year'] = $options['edit_year'];
        $view->vars['edit_month'] = $options['edit_month'];
        $view->vars['show_diff'] = $options['show_diff'];
        $view->vars['js'] = $options['js'];
        $view->vars['is_birthday'] = $options['is_birthday'];
    }

    public function getParent()
    {
        return ParentType::class;
    }

    public function getName()
    {
        return 'lle_date';
    }

    public function getBlockPrefix()
    {
        return 'lle_date';
    }
}