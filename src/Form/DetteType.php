<?php

// src/Form/DetteType.php

namespace App\Form;

use App\Entity\Dette;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType; // Assurez-vous d'importer le bon type
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DetteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('montant', NumberType::class, [
                'label' => 'Montant',
                'attr' => [
                    'class' => 'border border-gray-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500'
                ]
            ])
            ->add('date', DateTimeType::class, [ // Assurez-vous d'ajouter le champ date
                'label' => 'Date',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'border border-gray-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500'
                ]
            ])
            ->add('montantVerser', NumberType::class, [
                'label' => 'Montant Versé',
                'required' => false, // Il est possible que ce champ soit optionnel
                'attr' => [
                    'class' => 'border border-gray-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Dette::class,
        ]);
    }
}
