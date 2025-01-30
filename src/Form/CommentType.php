<?php

namespace App\Form;

use App\Entity\Comment;
use App\Entity\Tweets;
use App\Entity\Users;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content')
            ->add('tweet', EntityType::class, [
                'class' => Tweets::class,
                'choice_label' => 'id',
                'required' => true,
            ])
            ->add('parentComment', EntityType::class, [
                'class' => Comment::class,
                'choice_label' => 'id',
                'required' => false, // Permet d’avoir un commentaire sans parent
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
