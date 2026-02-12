<?php

namespace App\Http\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class RegisterRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Имя обязательно')]
        #[Assert\Length(min: 2, max: 100)]
        public readonly string $name,

        #[Assert\NotBlank(message: 'Email обязателен')]
        #[Assert\Email(message: 'Некорректный email')]
        #[Assert\Length(max: 180)]
        public readonly string $email,

        #[Assert\NotBlank(message: 'Телефон обязателен')]
        #[Assert\Regex(pattern: '/^\+?[0-9\-\s()]{7,20}$/', message: 'Некорректный телефон')]
        public readonly string $phone,

        #[Assert\NotBlank(message: 'Пароль обязателен')]
        #[Assert\Length(min: 6, max: 200, minMessage: 'Пароль минимум 6 символов')]
        public readonly string $password,

        #[Assert\NotBlank(message: 'Повтор пароля обязателен')]
        public readonly string $passwordRepeat,
    ) {}
}
