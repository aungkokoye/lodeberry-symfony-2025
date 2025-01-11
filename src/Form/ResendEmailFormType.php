<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ResendEmailFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', TextType::class, [
                'label' => 'Your Login Email',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Email cannot be empty.',
                    ]),
                    new Assert\Email([
                        'message' => 'Please enter a valid email address.',
                    ]),
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Resend',
                'attr' => ['class' => 'btn btn-primary'], // Optional: Add Bootstrap or custom CSS classes
            ]);;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_token_id' => 'resend_email_form',
        ]);
    }
}