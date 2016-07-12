<?php

namespace Clab\StripeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class CardFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('card', 'text', array(
                    'required' => true,
                    'attr' => array('data-stripe' => 'number'),
                    'label' => 'Numéro de carte',
                )
            )
            ->add('name', 'text', array(
                    'required' => false,
                    'attr' => array('data-stripe' => 'name'),
                    'label' => 'Nom',
                )
            )
            ->add('cvc', 'integer', array(
                    'required' => true,
                    'attr' => array('data-stripe' => 'cvc'),
                    'label' => 'CVC',
                )
            )
            ->add(
                'month',
                'choice',
                array(
                    'required' => true,
                    'attr' => array('data-stripe' => 'exp-month'),
                    'choices' => array(
                        1 => '01',
                        2 => '02',
                        3 => '03',
                        4 => '04',
                        5 => '05',
                        6 => '06',
                        7 => '07',
                        8 => '08',
                        9 => '09',
                        10 => '10',
                        11 => '11',
                        12 => '12'),
                    'label' => 'Mois',
                )
            )
            ->add(
                'year',
                'choice',
                array(
                    'required' => true,
                    'attr' => array('data-stripe' => 'exp-year'),
                    'choices' => array_combine(range(date('Y'), date('Y') + 10), range(date('Y'), date('Y') + 10)),
                    'label' => 'Année',
                )
            )
            ->add('token', 'hidden');
    }

    public function getName()
    {
        return 'clab_stripe_cardformtype';
    }
}
