<?php

namespace App\Form;

use App\Entity\Citation;
use App\Repository\AuthorRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;

class CitationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content', CKEditorType::class, [
                'config' => [
                    'toolbar' => 'basic',
                    'entities' => false,
                    'entities_additional' => ''
                ],
            ])
            ->add('note')
            ->add('created_date', DateType::class, [
                'years' => range(date('Y')-32, date('Y')),
                'input_format' => 'd-m-Y'
                ])
            ->add('author', null, [
                'query_builder' => function(AuthorRepository $aut) {
                    return $aut->getOrderedAuthorsQuery();
                }
            ])
            ->add('new_author', null, [
                'mapped' => false,
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Citation::class,
        ]);
    }
}
