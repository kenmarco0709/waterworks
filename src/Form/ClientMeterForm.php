<?php

namespace App\Form;

use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;


use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use App\Form\DataTransformer\DataTransformer;
use App\Entity\ClientEntity;
use App\Entity\PurokEntity;



class ClientMeterForm extends AbstractType
{
   
    private $manager;

    public function __construct(ObjectManager $manager) {
        $this->manager = $manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('action', HiddenType::class, array(
                'data' => $options['action'],
                'mapped' => false,
                'attr' => array(
                    'class' => 'form-action'
                )
            ))
            ->add('id', HiddenType::class)
            ->add('connectionType', ChoiceType::class, [
                'attr' => [ 'class' => 'form-control', 'required' => 'required'],

                'choices'  => [
                    'Residential' => 'Residential',
                    'Commercial' => 'Commercial'
                ]
            ])
            ->add('status', ChoiceType::class, [
                'attr' => [ 'class' => 'form-control', 'required' => 'required'],

                'choices'  => [
                    'Active' => 'Active',
                    'Disconnected' => 'Disconnected'
                ]
            ])
            ->add('houseNo', TextType::class, array(
                'label' => 'House #',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => [ 'class' => 'form-control'],
                'required' => false
            ))
            ->add('meterModel', TextType::class, array(
                'label' => 'Meter Model',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => [ 'class' => 'form-control'],
                'required' => false
            ))
            ->add('meterSerialNo', TextType::class, array(
                'label' => 'Meter Serial #',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => [ 'class' => 'form-control', 'required' => 'required'],
                'required' => true
            ))
            ->add('presentReading', TextType::class, array(
                'label' => 'Present Reading',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => [ 'class' => 'form-control', 'required' => 'required'],
                'required' => true
            ))
            ->add('oldBalance', TextType::class, array(
                'label' => 'Remaining Balance Before System',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => [ 'class' => 'form-control'],
                'required' => false
            ))
            ->add('client', HiddenType::class, array('data' => $options['clientId']))
            ->add('purok', HiddenType::class)
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event, $options) {

                $form = $event->getForm();
                $data = $event->getData();
                $purok = $data->getPurok();

                $form
                    ->add('purok_desc', TextType::class, array(
                        'label' => 'Purok',
                        'label_attr' => array(
                            'class' => 'middle required  col-form-label col-3'
                        ),
                        'required' => true,
                        'attr' => array(                            
                            'class' => 'form-control',
                        ),
                        'mapped' => false,
                        'data' => $purok ? $purok->getDescription() : ''
                    ));
                 
            });

            $builder->get('client')->addModelTransformer(new DataTransformer($this->manager, ClientEntity::class, true, $options['clientId']));
            $builder->get('purok')->addModelTransformer(new DataTransformer($this->manager, PurokEntity::class, false));


    }

    public function getName()
    {
        return 'clientMeter';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' 	  => 'App\Entity\ClientMeterEntity',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention'       => 'clientEntity_intention',
            'action'          => 'n',
            'clientId'    => null 
        ));
    }
}