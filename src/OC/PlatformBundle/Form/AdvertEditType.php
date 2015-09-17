<?php

namespace OC\PlatformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class AdvertEditType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->remove('date');

        $builder->addEventListener(
         FormEvents::PRE_SET_DATA,
         function (FormEvent $event)
         {
          $advert = $event->getData();

          if (null === $advert){
           return;
          }

          if(!$advert->getImage() || null === $advert->getId()){
           $event->getForm()->add('image', new ImageType(), array('required' => true));
          }else{
           $event->getForm()->remove('image');
           $event->getForm()->add('image', new ImageType(), array('required' => false));
          }
         }
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'oc_platformbundle_advert_edit';
    }

    public function getParent()
    {
     return new AdvertType();
    }
}
