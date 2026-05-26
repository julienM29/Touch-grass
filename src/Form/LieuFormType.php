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
            ->add('villeNom', TextType::class, [
                'mapped' => false,
                'label' => 'Ville',
                'required' => false,
                'data' => $options['ville_nom'],
            ])
            ->add('villeCodePostal', TextType::class, [
                'mapped' => false,
                'label' => 'Code postal',
                'required' => false,
                'data' => $options['ville_code_postal'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lieu::class,
            'ville_nom'         => null,
            'ville_code_postal' => null,
        ]);
    }
}
