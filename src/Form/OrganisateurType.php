<?php

namespace App\Form;

use App\Entity\Organisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrganisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomOr', TextType::class, [
                'label' => 'Nom de l\'organisateur',
            ])
            ->add('contact', TextType::class, [
                'label' => 'Contact',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Organisateur::class,
        ]);
    }
}
