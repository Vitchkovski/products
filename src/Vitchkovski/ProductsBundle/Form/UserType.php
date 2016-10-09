<?php

namespace Vitchkovski\ProductsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', EmailType::class, array('label' => false));
        $builder->add('password', PasswordType::class, array('label' => false));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
       /* $resolver->setDefaults(array(
            'csrf_protection'   => false,
        ));*/
    }

    public function getName()
    {
        return 'user';
    }
}
