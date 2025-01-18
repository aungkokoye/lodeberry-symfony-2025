<?php

namespace App\Order\Form;

use App\Entity\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Uuid;

class AdminProductSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choices = array_flip(Order::ORDER_STATUS_ARRAY);
        $builder
           
            ->add('search', TextType::class, [
                'label'         => false,
                'attr' => [
                    'placeholder' => 'Search on product name ...',
                ],
            ])
            ->add('active', ChoiceType::class, [
                'label'         => false,
                'placeholder'   => 'Select Active',
                'choices'       => [
                    'Yes'   => true,
                    'No'    => false
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_token_id' => 'admin_user_order_update_form',
        ]);
    }
}
