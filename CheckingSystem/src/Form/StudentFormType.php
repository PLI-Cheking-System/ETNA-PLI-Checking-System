<?php

namespace App\Form;

use App\Entity\Student;
use App\Entity\Schedule;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class StudentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class , [
                'label' => 'First Name',
                'attr' => [
                    'placeholder' => 'First Name',
                    'class' => 'form-control',
                ],
            ])
            ->add('lastName', TextType::class , [
                'label' => 'Last Name',
                'attr' => [
                    'placeholder' => 'Last Name',
                    'class' => 'form-control',
                ],
            ])
            ->add('image', FileType::class , [
                'data_class' => null,
                'label' => 'Image',
                'attr' => [
                    'placeholder' => 'Image',
                    'class' => 'form-control',
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'Email',
                    'class' => 'form-control',
                ],
            ])
            ->add('phone', TextType::class , [
                'label' => 'Phone',
                'attr' => [
                    'placeholder' => 'Phone',
                    'class' => 'form-control',
                ],
            ])
            // ->add('qrCode', TextType::class , [
            //     'label' => 'Qr Code',
            //     'attr' => [
            //         'placeholder' => 'Qr Code',
            //         'class' => 'form-control',
            //     ],
            // ])
            // ->add('matricule', TextType::class , [
            //     'label' => 'Matricule',
            //     'attr' => [
            //         'placeholder' => 'Matricule',
            //         'class' => 'form-control',
            //     ],
            // ])
            ->add('scheduleId', EntityType::class, [
                'class' => 'App\Entity\Schedule',
                'choice_label' => 'classeSchedule',
                'label' => 'Classe',
                'attr' => [
                    'placeholder' => 'Schedule',
                    'class' => 'form-control',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Student::class,
        ]);
    }
}
