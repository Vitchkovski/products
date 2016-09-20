<?php

namespace Vitchkovski\ProductsBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;


class ProductType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('product_img_name', FileType::class, array('label' => 'Product Image:', 'required' => false))
            ->add('product_name', TextType::class, array('label' => 'Product Name:'))
            ->add('categories', CollectionType::class, array(
                'entry_type' => CategoryType::class,
                'allow_add'    => true,
                'allow_delete' => true,
                ))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Vitchkovski\ProductsBundle\Entity\Product'
        ));
    }
}
