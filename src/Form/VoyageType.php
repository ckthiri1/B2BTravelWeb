<?php
// src/Form/VoyageType.php

namespace App\Form;

use App\Entity\Voyage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class VoyageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('depart', TextType::class, [
                'label' => 'Lieu de départ',
                'attr' => [
                    'class' => 'autocomplete-input',
                    'placeholder' => 'Choisissez un pays de départ',
                    'autocomplete' => 'off',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le lieu de départ est obligatoire.',
                    ]),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Le lieu de départ ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('Destination', TextType::class, [
                'label' => 'Lieu d\'arrivée',
                'attr' => [
                    'class' => 'autocomplete-input',
                    'placeholder' => 'Choisissez un pays d\'arrivée',
                    'autocomplete' => 'off',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le lieu d\'arrivée est obligatoire.',
                    ]),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Le lieu d\'arrivée ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('Description', TextareaType::class, [
                'label' => 'Description de voyage',
                'attr' => [
                    'class' => 'description-textarea',
                    'placeholder' => 'Entrez la description du voyage ici...',
                    'rows' => 5
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'La description du voyage est obligatoire.',
                    ]),
                    new Length([
                        'max' => 2000,
                        'maxMessage' => 'La description ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Voyage::class,
            'csrf_protection' => true,
            'validation_groups' => ['Default'], 
        ]);
    }
}