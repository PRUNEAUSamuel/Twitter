<?php

namespace App\Form;

use App\Entity\Tweets;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class TweetsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class ,[
                'attr' => [
                    'class' => 'form-control border-secondary bg-black text-white',
                    'placeholder' => '',
                    'style' => 'height: auto;',                  
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tweets::class,
        ]);
    }
}
