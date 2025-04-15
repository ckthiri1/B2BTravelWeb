<?php
// src/Form/HebergementType.php

namespace App\Form;

use App\Entity\Hebergement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType; 
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class HebergementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'minlength' => 2,
                    'maxlength' => 50,
                    'pattern' => "^[a-zA-ZÀ-ÿ0-9\s\-']+$",
                    'placeholder' => 'Nom de l\'hébergement'
                ]
                ])
                ->add('adresse', TextType::class, [
                    'attr' => [
                        'class' => 'form-control',
                        'pattern' => "^\\d+\\s+[a-zA-ZÀ-ÿ0-9\\s\\-,'.]{5,}$",
                        'title' => "Format : Numéro suivie du nom de rue (ex: 12 Rue de Paris)"
                    ]
                ])
                ->add('type', ChoiceType::class, [
                    'choices' => [
                        'Hôtel' => Hebergement::TYPE_HOTEL,
                        'Hostel' => Hebergement::TYPE_HOSTEL,
                        'Maison' => Hebergement::TYPE_MAISON,
                    ],
                    'placeholder' => 'Choisir un type', // Ajouter ceci
                    'attr' => ['class' => 'form-select']
                ])
                ->add('description', TextareaType::class, [
                    'attr' => [
                        'class' => 'form-control',
                        'rows' => 4,
                        'minlength' => 10,
                        'maxlength' => 255,
                        'placeholder' => 'Description détaillée...'
                    ]
                ])
                ->add('prix', IntegerType::class, [
                    'attr' => [
                        'class' => 'form-control',
                        'min' => 10,
                        'step' => 1,
                        'placeholder' => 'Prix par nuit'
                    ]
                ]);
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Hebergement::class,
        ]);
    }
}