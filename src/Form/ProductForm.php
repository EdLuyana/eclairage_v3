<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\Season;
use App\Entity\Size;
use App\Entity\Supplier;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du produit',
            ])
            ->add('color', TextType::class, [
                'label' => 'Couleur',
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Prix',
                'currency' => 'EUR',
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Catégorie',
            ])
            ->add('supplier', EntityType::class, [
                'class' => Supplier::class,
                'choice_label' => 'name',
                'label' => 'Fournisseur',
            ])
            ->add('season', EntityType::class, [
                'class' => Season::class,
                'choice_label' => 'name',
                'label' => 'Collection',
            ])
            ->add('sizes', EntityType::class, [
                'class' => Size::class,
                'choice_label' => 'value',
                'label' => 'Tailles disponibles',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'attr' => ['class' => 'size-checkboxes'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
