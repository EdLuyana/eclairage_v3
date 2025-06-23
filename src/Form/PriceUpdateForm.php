<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Season;
use App\Entity\Supplier;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PriceUpdateForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('filterType', ChoiceType::class, [
                'label' => 'Filtrer par',
                'choices' => [
                    'Catégorie' => 'category',
                    'Fournisseur' => 'supplier',
                    'Collection' => 'season',
                    'Référence exacte' => 'reference',
                ],
                'placeholder' => 'Choisir un critère',
            ])
            ->add('filterValue', TextType::class, [
                'label' => 'Valeur du filtre',
                'required' => true,
                'attr' => ['placeholder' => 'Saisir ou sélectionner...'],
            ])
            ->add('newPrice', MoneyType::class, [
                'label' => 'Nouveau prix à appliquer (€)',
                'currency' => 'EUR',
                'scale' => 2,
            ]);

        // Dynamique : si possible on remplace le champ `filterValue` selon le choix fait
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if (!isset($data['filterType'])) return;

            switch ($data['filterType']) {
                case 'category':
                    $form->add('filterValue', EntityType::class, [
                        'class' => Category::class,
                        'choice_label' => 'name',
                        'label' => 'Catégorie',
                    ]);
                    break;

                case 'supplier':
                    $form->add('filterValue', EntityType::class, [
                        'class' => Supplier::class,
                        'choice_label' => 'name',
                        'label' => 'Fournisseur',
                    ]);
                    break;

                case 'season':
                    $form->add('filterValue', EntityType::class, [
                        'class' => Season::class,
                        'choice_label' => 'name',
                        'label' => 'Collection',
                    ]);
                    break;

                case 'reference':
                default:
                    $form->add('filterValue', TextType::class, [
                        'label' => 'Référence exacte',
                    ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Pas lié à une entité
        ]);
    }
}
