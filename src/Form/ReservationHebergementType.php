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
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Security\Core\Security;

class ReservationHebergementType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isAdmin = $this->security->isGranted('ROLE_ADMIN');
        
        $builder
            ->add('hebergement', EntityType::class, [
                'class' => Hebergement::class,
                'choices' => $options['hebergements'],
                'choice_label' => function (Hebergement $hebergement) {
                    return sprintf(
                        '%s (%s DT/nuit - %s)',
                        $hebergement->getNom(),
                        $hebergement->getPrix(),
                        $hebergement->getAdresse()
                    );
                },
                'choice_attr' => function (Hebergement $hebergement) {
                    return ['data-price' => $hebergement->getPrix()];
                },
                'placeholder' => 'Sélectionnez un hébergement',
                'required' => true,
                'attr' => [
                    'class' => 'form-select hebergement-select',
                    'required' => 'required'
                ],
                'label' => 'Hébergement'
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'class' => 'form-control date-start',
                    'min' => (new \DateTime())->format('Y-m-d')
                ],
                'label' => 'Check-in Date'
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'class' => 'form-control date-end',
                    'min' => (new \DateTime())->format('Y-m-d')
                ],
                'label' => 'Check-out Date'
            ])
            ->add('prix', NumberType::class, [
                'attr' => [
                    'class' => 'form-control total-price-input',
                    'readonly' => true
                ],
                'label' => 'Total Price (DT)',
                'scale' => 2,
                'html5' => true
            ]);

        // Only show status field to admins, otherwise set as hidden with default value
        if ($isAdmin) {
            $builder->add('status', ChoiceType::class, [
                'choices' => [
                    'En Attente' => 'EnAttente',
                    'Résolue' => 'Resolue',
                    'Annulée' => 'Annulee'
                ],
                'label' => 'Reservation Status',
                'attr' => ['class' => 'form-select']
            ]);
        } else {
            $builder->add('status', HiddenType::class, [
                'data' => 'EnAttente',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReservationHebergement::class,
            'hebergements' => [],
            'constraints' => [
                new Callback([$this, 'validateDates']),
            ],
        ]);
    }

    public function validateDates(ReservationHebergement $reservationHebergement, ExecutionContextInterface $context)
    {
        if ($reservationHebergement->getDate() && $reservationHebergement->getDateFin()) {
            // Check if check-out date is before check-in date
            if ($reservationHebergement->getDateFin() < $reservationHebergement->getDate()) {
                $context->buildViolation('Check-out date must be after check-in date')
                    ->atPath('dateFin')
                    ->addViolation();
            }

            // Check if dates are in the past
            $today = new \DateTime('today');
            if ($reservationHebergement->getDate() < $today) {
                $context->buildViolation('Check-in date cannot be in the past')
                    ->atPath('date')
                    ->addViolation();
            }

            // Minimum stay validation (at least 1 night)
            $diff = $reservationHebergement->getDate()->diff($reservationHebergement->getDateFin());
            if ($diff->days < 1) {
                $context->buildViolation('Minimum stay is 1 night')
                    ->atPath('dateFin')
                    ->addViolation();
            }
        }
    }
}