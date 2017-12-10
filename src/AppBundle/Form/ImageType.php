<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImageType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('src')
            ->add('alt')
            ->add('filename')
            ->add('path')
            ->add('width')
            ->add('height')
            ->add('type')
            ->add('author')
            ->add('copyright')
            ->add('isExifLocation')
            ->add('dateTaken')
            ->add('dateAcquired')
            ->add('latitude')
            ->add('longitude')
            ->add('altitude')
            ->add('address')
            //->add('metadata')
            ->add('source');
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Image'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_image';
    }


}
