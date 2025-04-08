<?php

namespace App\Form;

use App\Entity\Reponse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Type;

class ReponseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('DescriptionRep', TextareaType::class, [
                'label' => 'Réponse',
                'required' => false, // Désactive la validation HTML5
                'constraints' => [
                    new NotBlank([
                        'message' => 'La réponse ne peut pas être vide.',
                    ]),
                    new Length([
                        'min' => 10,
                        'max' => 2000,
                        'minMessage' => 'La réponse doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'La réponse ne peut pas dépasser {{ limit }} caractères'
                    ]),
                    new Type([
                        'type' => 'string',
                        'message' => 'La réponse doit être une chaîne de caractères valide'
                    ])
                ],
                'attr' => [
                    'rows' => 8,
                    'class' => 'form-control',
                    'placeholder' => 'Entrez votre réponse ici...'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reponse::class,
            'validation_groups' => ['Default'] // Activation des groupes de validation
        ]);
    }
}