<?php

namespace App\Form;

use App\Entity\Users;
use PhpParser\Node\Stmt\Label;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\Image;

class UsersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class,[
                'required' => false,
                'label' => 'Nom d\'utilisateur',
                ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => false,
            ])
            ->add('oldPassword', PasswordType::class,[
                'label' => 'Ancien mot de passe',
                'required' => false,
                'mapped' => false,
                'attr' => ['autocomplete' => 'current-password'],
            ])
            ->add('password', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'label' => 'Nouveau mot de passe',
                'required' => false,
                'mapped' => false,
                'constraints' => [ 
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('Confirm_password', PasswordType::class, [
                'label' => 'Confirmer le mot de passe',
                'mapped' => false,
                'required'=> false,
                'attr' => ['autocomplete' => 'new-password'],
                
            //     'validation_groups' => function($form) {
            //     $password = $form->get('password')->getData();
            //     return $password ? ['Default'] : [];
            // },
            ])
            ->add('profilePicture', FileType::class, [
                'label' => 'Photo de profil',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Image([
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Merci de télécharger une image valide (JPEG ou PNG)',
                    ])
                ]
            ]);
            
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}
