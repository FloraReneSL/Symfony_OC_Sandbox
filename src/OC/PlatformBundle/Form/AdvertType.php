<?php

namespace OC\PlatformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;


class AdvertType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date', 'date')
            ->add('title', 'text')
            ->add('author', 'text')
            ->add('content', 'textarea')
            ->add('published', 'checkbox', array('required' => false))
            ->add('image', new ImageType())
            ->add('categories', 'entity', array(
             'class'         => 'OCPlatformBundle:Category',
             'property'    => 'name',
             'multiple' => true,
             'expanded' => true
            ))
            ->add('Valider', 'submit')
        ;

        $builder->addEventListener(
         FormEvents::PRE_SET_DATA,
         function (FormEvent $event)
         {
          $advert = $event->getData();

          if (null === $advert){
           return;
          }

          if(!$advert->getPublished() || null === $advert->getId()){
           $event->getForm()->add('published', 'checkbox', array('required' => false));
          }else{
           $event->getForm()->remove('published');
          }
         }
        );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'OC\PlatformBundle\Entity\Advert'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'oc_platform_view';
    }
}
