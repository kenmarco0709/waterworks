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
use App\Form\DataTransformer\DatetimeTransformer;
use App\Entity\ClientMeterEntity;



class ClientMeterReadingForm extends AbstractType
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
            ->add('previousReading', TextType::class, array(
                'label' => 'Previous Reading',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => [ 'class' => 'form-control', 'readonly' => true],
                'data' => $options['previousReading'],
                'required' => true
            ))
            ->add('presentReading', TextType::class, array(
                'label' => 'Present Reading',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => [ 'class' => 'form-control'],
                'required' => true
            ))
            ->add('readingDate', TextType::class, [
                'label' => 'Reading Date',
                'attr' => [ 'class' => 'form-control datepicker'],
                'required' => true,
            ])
            ->add('dueDate', TextType::class, [
                'label' => 'Due Date',
                'attr' => [ 'class' => 'form-control datepicker'],
                'required' => true,
            ])
            ->add('clientMeter', HiddenType::class, array('data' => $options['clientMeterId']));

            $builder->get('clientMeter')->addModelTransformer(new DataTransformer($this->manager, ClientMeterEntity::class, true, $options['clientMeterId']));
            $builder->get('readingDate')->addModelTransformer(new DatetimeTransformer());
            $builder->get('dueDate')->addModelTransformer(new DatetimeTransformer());



    }

    public function getName()
    {
        return 'clientMeterReading';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' 	  => 'App\Entity\ClientMeterReadingEntity',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention'       => 'clientEntity_intention',
            'action'          => 'n',
            'clientMeterId'    => null,
            'previousReading' => '' 
        ));
    }
}