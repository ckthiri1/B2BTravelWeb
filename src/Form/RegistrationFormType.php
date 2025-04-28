<?php
namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'attr' => [
                    'class' => 'form-input',
                    'placeholder' => 'Enter your last name',
                    'autocomplete' => 'off'
                ],
                'empty_data' => '',
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
                    'autocomplete' => 'off'
                ],
                'empty_data' => '',
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
                    'autocomplete' => 'off'
                ],
                'empty_data' => '',
                'label_attr' => ['class' => 'form-label'],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter an email']),
                    new Length(['max' => 180])
                ]
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'first_options'  => [
                    'label' => 'Password',
                    'attr' => [
                        'class' => 'form-input',
                        'placeholder' => 'Enter password',
                        'autocomplete' => 'new-password'
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
                        'autocomplete' => 'new-password'
                    ],
                    'label_attr' => ['class' => 'form-label']
                ],
                'mapped' => false
            ])
            ->add('profileImage', FileType::class, [
                'label' => 'Profile Image',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'custom-file-input',
                    'accept' => 'image/*'
                ],
                'constraints' => [
                    new Image([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
                        'mimeTypesMessage' => 'Please upload a valid image (JPEG/PNG)',
                        'maxSizeMessage' => 'The file is too large (max {{ limit }} {{ suffix }})'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
{
    $resolver->setDefaults([
        'data_class' => User::class,
    ]);
}
}