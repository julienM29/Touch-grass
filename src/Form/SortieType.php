<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Site;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateIntervalType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom',
                TextType::class, [
                    'label' => 'Nom de la sortie',
                    'attr' => [
                        'placeholder' => 'Renseignez ici le nom de votre sortie',
                    ],
                ])
            ->add('description', TextAreaType::class, [
                'label' => 'Description de la sortie',
                'attr' => [
                    'placeholder' => 'Renseignez ici la description de la sortie',
                ]
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'label'  => 'Date et heure de la sortie',
                'widget' => 'single_text'
            ])
            ->add('duree', DateIntervalType::class, [
                'label'        => 'Durée de la sortie',
                'with_years'   => false,
                'with_months'  => false,
                'with_days'    => true,
                'with_hours'   => true,
                'with_minutes' => true,
                'with_seconds' => false,
                'widget'       => 'integer',
                'labels'       => [
                    'days'    => 'Jours',
                    'hours'   => 'Heures',
                    'minutes' => 'Minutes',
                ],
            ])
            ->add('dateLimiteInscription')
            ->add('nbInscriptionsMax')
            ->add('dateOuvertureInscription')
            ->add('image', FileType::class, [
                'label'    => 'Illustration de la sortie',
                'mapped'   => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize'          => '2M',
                        'mimeTypes'        => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide',
                    ])
                ],
            ])
            ->add('siteOrganisateur', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'id',
            ])
            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choice_label' => 'id',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
