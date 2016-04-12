<?php

namespace Clab\StripeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PlanFormType extends AbstractType
{
    public function __construct($class)
    {
        $this->class = $class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array(
            'label' => 'Name',
            'attr' => array(
                'title' => 'Enter the name',
            ),
        ));

        $addNewFields = function ($form) use ($builder) {
            $factory = $builder->getFormFactory();
            $form
                ->add($factory->createNamed('id', 'text', null, array(
                    'label' => 'Id',
                    'attr' => array(
                        'title' => 'Enter the id',
                    ),
                )))
                ->add($factory->createNamed('amount', 'text', null, array(
                    'label' => 'Amount',
                    'attr' => array(
                        'title' => 'Enter the amount',
                    ),
                )))
                ->add($factory->createNamed('interval', 'choice', null, array(
                    'label' => 'Interval',
                    'choices' => array('month' => 'Monthly', 'year' => 'Yearly'),
                    'attr' => array(
                        'title' => 'Select the interval',
                    ),
                )))
                ->add($factory->createNamed('currency', 'choice', null, array(
                    'label' => 'Currency',
                    'choices' => array('CAD' => 'CAD', 'USD' => 'USD'),
                    'attr' => array(
                        'title' => 'Select the currency',
                    ),
                )))
            ;
        };
    }

    public function getName()
    {
        return 'clab_stripe_plan';
    }
}
