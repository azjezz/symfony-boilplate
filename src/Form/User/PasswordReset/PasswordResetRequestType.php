<?php

/*
 * This file is part of Symfony Boilerplate.
 *
 * (c) Saif Eddin Gmati
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Form\User\PasswordReset;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

final class PasswordResetRequestType extends AbstractType
{
    public const EMAIL_FIELD = 'email';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(self::EMAIL_FIELD, EmailType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter your email.',
                    ]),
                    new Email([
                        'message' => 'Please enter a valid email address.',
                    ]),
                ],
            ])
        ;
    }
}
