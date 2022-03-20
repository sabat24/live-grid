<?php

namespace App\Component\User\Form\FilterType;

use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UserListFilterType extends AbstractType
{
    // search everywhere https://stackoverflow.com/questions/36442829/search-for-a-keyword-through-all-the-properties-of-an-entity-symfony2

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('email', Filters\TextFilterType::class, [
            'label' => 'email',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'user_list_filter';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
            'validation_groups' => array('filtering'), // avoid NotBlank() constraint-related message
        ));
    }
}
