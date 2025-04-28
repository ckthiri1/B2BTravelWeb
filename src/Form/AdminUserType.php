<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

class AdminUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'attr' => [
                    'class' => 'form-input',
                    'placeholder' => 'Enter your last name',
                    'data-validate' => 'true'
                ],
                'label_attr' => ['class' => 'form-label'],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter a last name']),
                    new Length(['min' => 2, 'max' => 50])
                ]
            ])
            ->add('prenom', TextType::class, [
                'attr' => [
                    'class' => 'form-input',
                    'placeholder' => 'Enter your first name',
                    'data-validate' => 'true'
                ],
                'label_attr' => ['class' => 'form-label'],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter a first name']),
                    new Length(['min' => 2, 'max' => 50])
                ]
            ])
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => 'form-input',
                    'placeholder' => 'Enter your email',
                    'data-validate' => 'true'
                ],
                'label_attr' => ['class' => 'form-label'],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter an email']),
                    new Email(['message' => 'Please enter a valid email address']),
                    new Length(['max' => 180])
                ]
            ])
            ->add('role', ChoiceType::class, [
                'choices' => [
                    'User' => 'user',
                    'Admin' => 'admin'
                ],
                'multiple' => false,
                'expanded' => false,
                'attr' => ['class' => 'form-select'],
                'label_attr' => ['class' => 'form-label'],
                'placeholder' => 'Select a role',
                'required' => true,
            ])
            ->add('nbrVoyage', IntegerType::class, [
                'attr' => ['class' => 'form-input'],
                'label' => 'Number of Trips',
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'first_options'  => [
                    'label' => 'Password',
                    'attr' => [
                        'class' => 'form-input',
                        'placeholder' => 'Enter password',
                        'data-validate' => 'true'
                    ],
                    'label_attr' => ['class' => 'form-label'],
                    'constraints' => [
                        new NotBlank(['message' => 'Please enter a password']),
                        new Length([
                            'min' => 6,
                            'minMessage' => 'Your password should be at least {{ limit }} characters',
                            'max' => 4096,
                        ]),
                    ]
                ],
                'second_options' => [
                    'label' => 'Repeat Password',
                    'attr' => [
                        'class' => 'form-input',
                        'placeholder' => 'Repeat password',
                        'data-validate' => 'true'
                    ],
                    'label_attr' => ['class' => 'form-label']
                ],
                'mapped' => false,
                'required' => false
            ])
            ->add('profileImage', FileType::class, [
                'label' => 'Profile Image',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'custom-file-input',
                    'accept' => 'image/*',
                    'data-validate' => 'true'
                ],
                'constraints' => [
                    new Image([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
                        'mimeTypesMessage' => 'Please upload a valid image (JPEG/PNG/GIF)',
                        'maxSizeMessage' => 'The file is too large (max {{ limit }} {{ suffix }})'
                    ])
                ]
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_new' => false,
            'constraints' => [
                new UniqueEntity([
                    'fields' => 'email',
                    'message' => 'This email is already registered.',
                ]),
            ],
        ]);
        $resolver->setAllowedTypes('is_new', 'bool');
    }
}