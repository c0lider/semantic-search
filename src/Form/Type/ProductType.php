<?php

declare(strict_types=1);

namespace App\Form\Type;

use Pimcore\Model\DataObject\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // TODO: add reviews and tags

        $builder
            ->add('title', TextType::class)
            ->add('brand', TextType::class)
            ->add('description', TextAreaType::class)
            ->add('rating', NumberType::class, ['constraints' => new LessThanOrEqual(5)])
            ->add('price', NumberType::class)
            ->add('discountPercentage', NumberType::class)
            ->add('stock', IntegerType::class)
            ->add('warrantyInfo', TextAreaType::class)
            ->add('published', CheckboxType::class, ['required' => false])
            ->add('save', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
