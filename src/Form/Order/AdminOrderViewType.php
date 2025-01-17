<?php

namespace App\Order\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Uuid;

class AdminOrderViewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('uuid', TextType::class, [
                'label' => 'Order UUID',
                'constraints' => [
                    new NotBlank([
                        'message' => 'UUID should not be blank.',
                    ]),
                    new Uuid([
                        'message' => 'Please enter a valid UUID.',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'Enter the UUID here...',
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Serach',
                'attr' => ['class' => 'btn btn-primary'], 
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_token_id' => 'admin_user_order_view_form',
        ]);
    }
}
