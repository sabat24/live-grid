<?php

namespace App\Component\User\Form\Type;

use App\Component\User\Entity\User;
use App\Component\User\Form\Transformer\UserVerifiedAtToBooleanTransformer;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractResourceType
{
    public function __construct(array $validationGroups = [])
    {
        parent::__construct(User::class, $validationGroups);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'app.form.user.username',
            ])
            ->add('email', EmailType::class, [
                'label' => 'app.form.user.email',
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'app.form.user.password.label',
                'always_empty' => false,
            ])
            ->add('verifiedAt', CheckboxType::class, [
                'label' => 'app.form.user.verified',
                'required' => false,
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'app.form.user.roles',
                'choices' => ['ROLE_ADMIN' => 'ROLE_ADMIN', 'os' => 'os'],
                'autocomplete' => true,
                'multiple' => true,
                'tom_select_options' => [
                    'closeAfterSelect' => false,
                ],
            ]);

        $builder->get('verifiedAt')->addModelTransformer(new UserVerifiedAtToBooleanTransformer(), true);

        $builder->addEventListener(FormEvents::POST_SET_DATA, static function (FormEvent $event) {
            /** @var User|null $data */
            $data = $event->getData();
            if (null === $data) {
                return;
            }

            if ($data->isVerified()) {
                $event->getForm()->add('verifiedAt', CheckboxType::class, [
                    'label' => 'app.form.user.verified',
                    'required' => false,
                    'disabled' => true,
                    'data' => true,
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => $this->dataClass,
                'validation_groups' => function (FormInterface $form): array {
                    $this->validationGroups[] = 'app_user';
                    $data = $form->getData();
                    if ($data instanceof User && $data->getId() === null) {
                        $this->validationGroups[] = 'app_user_create';
                    }

                    return $this->validationGroups;
                },
            ]);
    }
}
