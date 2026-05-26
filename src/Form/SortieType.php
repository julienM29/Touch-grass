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
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Positive;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom',
                TextType::class, [
                    'label' => 'Nom de la sortie',
                    'required' => true,
                    'attr' => [
                        'placeholder' => 'Renseignez ici le nom de votre event_registration',
                        'maxlength' => 255,
                        'minlength' => 5,
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Le nom de la event_registration est obligatoire.',
                        ]),
                        new Length([
                            'max' => 255,
                            'maxMessage' => 'Le nom de la event_registration ne peut pas dépasser {{ limit }} caractères.',
                        ]),
                    ],
                ])
            ->add('description', TextAreaType::class, [
                'label' => 'Description de la sortie',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Renseignez ici la description de la sortie',
                ],
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'label' => 'Date et heure de la sortie',
                'widget' => 'single_text',
                'required' => true,
                'constraints' => [
                    new NotNull([
                        'message' => 'La date et l’heure de début sont obligatoires.',
                    ]),
                ],
            ])
            ->add('duree', DateIntervalType::class, [
                'label' => 'Durée de la sortie',
                'required' => true,
                'with_years' => false,
                'with_months' => false,
                'with_days' => true,
                'with_hours' => true,
                'with_minutes' => true,
                'with_seconds' => false,
                'widget' => 'integer',
                'labels' => [
                    'days' => 'Jours',
                    'hours' => 'Heures',
                    'minutes' => 'Minutes',
                ],
                'constraints' => [
                    new NotNull([
                        'message' => 'La durée de la sortie est obligatoire.',
                    ]),
                ],
            ])
            ->add('dateLimiteInscription', DateTimeType::class, [
                'label' => 'Date limite d’inscription',
                'widget' => 'single_text',
                'required' => true,
                'constraints' => [
                    new NotNull([
                        'message' => 'La date limite d’inscription est obligatoire.',
                    ]),
                ],
            ])
            ->add('nbInscriptionsMax', IntegerType::class, [
                'label' => 'Nombre maximum d’inscriptions',
                'required' => true,
                'constraints' => [
                    new NotNull([
                        'message' => 'Le nombre maximum d’inscriptions est obligatoire.',
                    ]),
                    new Positive([
                        'message' => 'Le nombre maximum d’inscriptions doit être supérieur à 0.',
                    ]),
                ],
            ])
            ->add('dateOuvertureInscription', DateTimeType::class, [
                'label' => 'Date d’ouverture des inscriptions',
                'widget' => 'single_text',
                'required' => true,
                'constraints' => [
                    new NotNull([
                        'message' => 'La date d’ouverture des inscriptions est obligatoire.',
                    ]),
                ],
            ])
            ->add('image', FileType::class, [
                'label' => 'Illustration de la sortie',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide.',
                    ]),
                ],
            ])
            ->add('siteOrganisateur', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'nom',
                'label' => 'Site organisateur',
                'required' => true,
                'placeholder' => 'Choisissez un site',
                'constraints' => [
                    new NotNull([
                        'message' => 'Le site organisateur est obligatoire.',
                    ]),
                ],
            ])
            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choice_label' => 'nom',
                'label' => 'Lieu',
                'required' => true,
                'placeholder' => 'Choisissez un lieu',
                'constraints' => [
                    new NotNull([
                        'message' => 'Le lieu est obligatoire.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
