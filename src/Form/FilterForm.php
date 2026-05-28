<?php

namespace App\Form;

use App\Entity\Site;
use App\Repository\SiteRepository;
use App\Dto\FilterDto;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\DateTime;


class FilterForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'label' => 'Choisissez un site :',
                'choice_label' => 'nom',
                'placeholder' => 'Tous les sites',
                'required' => false,
                'query_builder' => function (SiteRepository $siteRepository) {
                    return $siteRepository->createQueryBuilder('s')
                        ->orderBy('s.nom', 'ASC');
                },
                'attr' => [
                    'data-filter-submit' => true,
                ],
            ])
            ->add('word', SearchType::class, [
                'label' => 'Recherche par mot-clef :',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Rechercher une sortie',
                    'onkeydown' => 'return event.key !== "Enter";',
                ],
            ])
            ->add('dateMin', DateType::class, [
                'label' => 'Entre :',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('dateMax', DateType::class, [
                'label' => 'Et :',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('organisateur', CheckboxType::class, [
                'label' => 'Sorties dont je suis organisateur',
                'required' => false,
            ])
            ->add('registered', CheckboxType::class, [
                'label' => 'Sorties où je suis inscrit',
                'required' => false,
            ])
            ->add('notRegistered', CheckboxType::class, [
                'label' => 'Sorties où je ne suis pas inscrit',
                'required' => false,
            ])
            ->add('finished', CheckboxType::class, [
                'label' => 'Sorties passées',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FilterDto::class,
        ]);
    }

}
