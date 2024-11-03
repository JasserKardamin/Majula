<?php

namespace App\Form;

use App\Entity\Data;
use Symfony\Component\Form\Extension\Core\Type\TextType;  
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType; 
use Symfony\Component\Form\Extension\Core\Type\ChoiceType; 
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DataType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class  , [
                'attr' => [
                    'class' => 'input',
                    'placeholder' => 'Enter the Title'
                ]
            ])

            ->add('content' , FileType::class, [  
                'attr' => [
                    'class' => 'file', 
                ],
                'label' => 'Upload File',  
                'mapped' => false,  
                'required' => false,  
            ])

            ->add('description' ,TextareaType::class, [  
                'attr' => [
                    'class' => 'input_area', 
                    'placeholder' => 'Enter the Description',
                    'rows' => 5, 
                    'cols' => 57
                ]
            ])

            ->add('class', ChoiceType::class , [
                'attr' => [
                    'class' => 'select',
                    'placeholder' => 'Enter the Title'
                ],
                'label' => 'Class', 'choices' => [
                     '1A' => '1A',
                     '2A' => '2A',
                     '3A' => '3A',
                     '4A' => '4A',
                     '5A' => '5A'
                     ], 
               ])

            ->add('type', ChoiceType::class , [
                'attr' => [
                    'class' => 'select',
                    'placeholder' => 'Enter the Title'
                ],
                'label' => 'Type', 'choices' => [
                     'Serie' => 'serie',
                     'Test' => 'Test',
                     'Exam' => 'Exam',
                     'Course' => 'Couse'
                     ], 
               ]) ;
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Data::class,
        ]);
    }
}
