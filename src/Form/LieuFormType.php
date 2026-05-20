<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LieuFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du lieu',
            ])
            ->add('rue', TextType::class, [
                'label' => 'Rue',
            ])
            ->add('latitude', NumberType::class, [
                'label' => 'Latitude',
                'scale' => 6, // 6 décimales
                'html5' => true, // input type=number
            ])
            ->add('longitude', NumberType::class, [
                'label' => 'Longitude',
                'scale' => 6,
                'html5' => true,
            ])
            ->add('ville', EntityType::class, [
                'class'        => Ville::class,
                'choice_label' => 'nom',
                'label'        => 'Ville existante',
                'placeholder'  => '-- Choisir une ville existante --',
                'required'     => false,
                'attr'         => [
                    'class' => 'tom-select',
                ],
            ])
            ->add('nouvelleVille', VilleFormType::class, [
                'mapped' => false, // pour dire qu'elle n'est pas directement lié à l'entité Lieu
                'required' => false,
                'label' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lieu::class,
        ]);
    }
}
