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
    /**
     * no-day is usefull to disable some days of the week (e.g : 0 = sunday, 1 = monday, 2 = tuesday, etc)
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'widget'        => 'single_text',
            'format'        => 'dd/MM/yyyy',
            'js_date_format'   => 'dd/mm/yy',
            'min_day'       => null,
            'max_day'       => null,
            'no_day'        => array(),
            'edit_year'     => true,
            'edit_month'    => true,
            'is_birthday'   => false,
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
        $view->vars['min'] = $this->treatMinMax($options['min_day']);
        $view->vars['max'] = $this->treatMinMax($options['max_day']);
        $view->vars['noday'] = json_encode($options['no_day']);
        $view->vars['edit_year'] = $options['edit_year'];
        $view->vars['edit_month'] = $options['edit_month'];
        $view->vars['is_birthday'] = $options['is_birthday'];
    }

    private function treatMinMax($strDate){
        if(substr($strDate,0,1) === '+' or substr($strDate,0,1) === '-'){
            $now = new \DateTime();
            $interval = new \DateInterval('P'. substr($strDate, 1, strlen($strDate)));
            if(substr($strDate,0,1) === '+'){
                $now->add($interval);
            }else{
                $now->sub($interval);
            }
            return '"'.$now->format('d/m/Y').'"';
        }else{
            return (is_string($strDate))? '"'.$strDate.'"':null;
        }
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