<?php

namespace App\Form;

use App\Entity\Subject;
use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;

class SubjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('teacherName', TextType::class, [
                'label' => 'Teacher Name',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'let it empty if you are the teacher',
                ],
                'required' => false
            ])
            ->add('start_at', DateTimeType::class, [
                'date_widget' => 'single_text'
            ])
            ->add('end_at', DateTimeType::class, [
                'date_widget' => 'single_text'
            ])
            ->add('subjectName', TextType::class , [
                'label' => 'Subject Name',
                'attr' => [
                    'placeholder' => 'Subject Name',
                    'class' => 'form-control',
                ],
            ])
            ->add('background_color', ColorType::class , [
                'label' => 'Background Color',
                'attr' => [
                    'placeholder' => 'Background Color',
                    'class' => 'form-control w-25',
                ],
            ])
            ->add('all_day')
            ->add('border_color', ColorType::class , [
                'label' => 'Border Color',
                'attr' => [
                    'placeholder' => 'Border Color',
                    'class' => 'form-control w-25',
                ],
            ])
            ->add('text_color', ColorType::class , [
                'label' => 'Text Color',
                'attr' => [
                    'placeholder' => 'Text Color',
                    'class' => 'form-control w-25',
                ],
            ])
            ->add('scheduleId', EntityType::class, [
                'class' => 'App\Entity\Schedule',
                'choice_label' => 'classeSchedule',
                'label' => 'Classe',
                'attr' => [
                    'placeholder' => 'Schedule',
                    'class' => 'form-control',
                ],
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Subject::class,
        ]);
    }
}
