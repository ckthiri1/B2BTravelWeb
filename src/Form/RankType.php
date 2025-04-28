<?php

namespace App\Form;

use App\Entity\Rank;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class RankType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('NomRank', TextType::class, [
                'label' => 'NOM RANK',
                'attr' => [
                    'class' => 'input-field',
                    'placeholder' => 'Entrez le nom du rank'
                ]
            ])
            ->add('points', NumberType::class, [
                'label' => 'POINTS',
                'attr' => [
                    'class' => 'input-field',
                    'placeholder' => 'Entrez le nombre de points'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rank::class,
        ]);
    }
}