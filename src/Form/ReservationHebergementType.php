<?php

namespace App\Form;

use App\Entity\Hebergement;
use App\Entity\ReservationHebergement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ReservationHebergementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('hebergement', EntityType::class, [
                'class' => Hebergement::class,
                'choice_label' => 'nom',
                'attr' => [
                    'class' => 'hebergement-select form-select',
                    'data-price-url' => '/hebergement/__id__/prix'
                ]
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'date-start']
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'date-end']
            ])
            ->add('prix', HiddenType::class, [
                'attr' => ['class' => 'total-price-input'],
                'empty_data' => 0 // Garantit une valeur par défaut
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'En attente' => 'EnAttente',
                    'Résolue' => 'Resolue'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReservationHebergement::class,
            'constraints' => [
                new Callback([$this, 'validateDates']),
            ],
        ]);
    }

    public function validateDates(ReservationHebergement $reservation, ExecutionContextInterface $context)
    {
        if ($reservation->getDate() && $reservation->getDateFin()) {
            if ($reservation->getDate() > $reservation->getDateFin()) {
                $context->buildViolation('La date de fin doit être postérieure à la date de début')
                    ->atPath('dateFin')
                    ->addViolation();
            }
        }
    }
}