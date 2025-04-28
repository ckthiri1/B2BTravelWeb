<?php

namespace App\Form;

use App\Entity\Evennement;
use App\Entity\Organisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class EvennementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Event Type field
            ->add('eventType', ChoiceType::class, [
                'label' => 'Type d\'évènement',
                'choices' => [
                    'Conférence' => 'CONFERENCE',
                    'Webinar' => 'WEBINAR',
                    'Trade Show' => 'TRADE_SHOW',
                    'Atelier' => 'WORKSHOP',
                    'Par défaut' => 'DEFAULT',
                ],
                'placeholder' => '-- Choisir un type --',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le type d\'évènement est requis.'
                    ])
                ],
                'attr' => [
                    'required' => false, // This removes HTML5 required validation
                ],
            ])

            // Event Name field (nomE)
            ->add('nomE', null, [
                'label' => 'Nom de l’Évènement',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le nom de l\'évènement est requis.'
                    ]),
                    new Assert\Length([
                        'min' => 3,
                        'minMessage' => 'Le nom de l\'évènement doit contenir au moins {{ limit }} caractères.',
                    ]),
                ],
                'attr' => [
                    'required' => false, // Removes HTML5 required validation
                ],
            ])

            // Event Location field (local)
            ->add('local', null, [
                'label' => 'Lieu',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le lieu de l\'évènement est requis.'
                    ]),
                    new Assert\Length([
                        'min' => 3,
                        'minMessage' => 'Le lieu de l\'évènement doit contenir au moins {{ limit }} caractères.',
                    ])
                ],
                'attr' => [
                    'required' => false, // Removes HTML5 required validation
                ],
            ])

            // Event Date field (dateE)
            ->add('dateE', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date de l’Évènement',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'La date de l\'évènement est requise.'
                    ]),
                    new Assert\GreaterThanOrEqual([
                        'value' => 'today',
                        'message' => 'La date de l\'évènement doit être aujourd\'hui ou plus tard.'
                    ]),
                ],
                'attr' => [
                    'required' => false, // Removes HTML5 required validation
                ],
            ])

            // Event Description field (desE)
            ->add('desE', null, [
                'label' => 'Description',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'La description de l\'évènement est requise.'
                    ]),
                    new Assert\Length([
                        'min' => 10,
                        'minMessage' => 'La description doit contenir au moins {{ limit }} caractères.',
                    ])
                ],
                'attr' => [
                    'required' => false, // Removes HTML5 required validation
                ],
            ])

            // Event Organizer field (idOr)
            ->add('idOr', EntityType::class, [
                'class' => Organisateur::class,
                'choice_label' => 'nomOr',
                'label' => 'Organisateur',
                'placeholder' => '-- Sélectionner un organisateur --',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'L\'organisateur est requis.'
                    ]),
                ],
                'attr' => [
                    'required' => false, // Removes HTML5 required validation
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evennement::class,
        ]);
    }
}
