<?php

namespace App\Product\Form;

use App\Entity\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class AdminProductSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choices = array_flip(Order::ORDER_STATUS_ARRAY);
        $builder
            ->setMethod('GET')
            ->add('search', TextType::class, [
                'label'         => false,
                'attr' => [
                    'placeholder' => 'Search on product name ...',
                ],
            ])
            ->add('active', ChoiceType::class, [
                'label'         => false,
                'placeholder'   => 'Activation (Yes & No)',
                'choices'       => [
                    'Activation (Yes)'   => true,
                    'Activation (No)'    => false
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_token_id' => 'admin_product_search_form',
        ]);
    }
}
