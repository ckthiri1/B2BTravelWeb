<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Vol;
use App\Entity\Voyage;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VolType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('volID')
            ->add('dateDepart', null, [
                'widget' => 'single_text',
            ])
            ->add('dateArrival', null, [
                'widget' => 'single_text',
            ])
            ->add('airLine')
            ->add('flightNumber')
            ->add('dureeVol')
            ->add('prixVol')
            ->add('typeVol')
            ->add('status')
            ->add('idVoyage', EntityType::class, [
                'class' => Voyage::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vol::class,
        ]);
    }
}
