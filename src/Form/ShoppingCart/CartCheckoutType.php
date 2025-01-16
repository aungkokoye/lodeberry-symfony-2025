<?php

namespace App\Form;

use App\Entity\Order;
use App\Entity\User;
use Doctrine\DBAL\Types\Type;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CartCheckoutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('address', TextareaType::class)
            ->add('Submit', SubmitType::class, [
                'attr' => ['class' => 'btn btn-success'], 
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'    => Order::class,
            'csrf_token_id' => 'cart_checkout_form',
        ]);
    }
}
