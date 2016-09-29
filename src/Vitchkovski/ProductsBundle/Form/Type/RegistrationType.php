<?php
/**
 * Created by PhpStorm.
 * User: Art
 * Date: 04.09.2016
 * Time: 23:06
 */

namespace Vitchkovski\ProductsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class RegistrationType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('username', 'text', array('label' => false))
            ->add('email', 'email', array('label' => false))
            ->add('password', 'password', array('label' => false))

            ->add('save', 'submit', ['label'=>'Register'])
        ;
    }

    public function getName()
    {
        return 'registration';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Vitchkovski\ProductsBundle\Entity\User',
            'validation_groups' => array('registration'),
            'csrf_protection'   => false,
        ]);
    }

}