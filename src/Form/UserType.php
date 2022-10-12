<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $constraints = [new Length([
            'min' => 6,
            'minMessage' => 'Your password should be at least {{ limit }} characters',
            // max length allowed by Symfony for security reasons
            'max' => 4096,
        ])];

        if (!$options['edit']) {
            $constraints[] = new NotBlank(['message' => 'Please enter a password']);
        } else {
            $builder
                ->add('name', TextType::class, [
                    'label' => 'Name',
                    'required' => false,
                    'help' => 'Optional',
                ])
                ->add('callsign', TextType::class, [
                    'label' => 'Callsign',
                    'required' => false,
                    'help' => 'Optional',
                ]);

            $builder->get('callsign')
                ->addModelTransformer(new CallbackTransformer(
                // The value shown in the form
                    static function (?string $callsign): ?string {
                        // Just return the callsign as is
                        return $callsign;
                    },
                    // The value saved to the database
                    static function (?string $callsign): ?string {
                        // Convert the callsign to upper case
                        return $callsign ? mb_strtoupper($callsign) : null;
                    }
                ));
        }

        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
            ]);

        if ($options['edit']) {
            $builder
                ->add('homepageUrl', UrlType::class, [
                    'label' => 'Homepage URL',
                    'required' => false,
                    'help' => 'Optional',
                ]);
        }

        $builder
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Password',
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'required' => !$options['edit'],
                'constraints' => $constraints,
                'help' => $options['edit'] ? 'If you want to change it' : null,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'edit' => false,
        ]);
    }
}
