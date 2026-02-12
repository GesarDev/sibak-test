<?php

namespace App\Http\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class RegisterRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Имя обязательно')]
        #[Assert\Length(
            min: 2,
            max: 100,
            minMessage: 'Имя минимум {{ limit }} символа',
            maxMessage: 'Имя максимум {{ limit }} символов'
        )]
        #[Assert\Type('string')]
        public readonly string $name,

        #[Assert\NotBlank(message: 'Email обязателен')]
        #[Assert\Email(
            message: 'Некорректный email адрес',
            mode: 'strict'
        )]
        #[Assert\Length(max: 255)]
        public readonly string $email,

        #[Assert\NotBlank(message: 'Телефон обязателен')]
        #[Assert\Regex(
            pattern: '/^\+?[0-9\-\s()]{7,30}$/',
            message: 'Некорректный формат телефона'
        )]
        public readonly string $phone,

        #[Assert\NotBlank(message: 'Пароль обязателен')]
        #[Assert\Length(
            min: 6,
            max: 200,
            minMessage: 'Пароль минимум {{ limit }} символов',
            maxMessage: 'Пароль максимум {{ limit }} символов'
        )]
        #[Assert\NotCompromisedPassword(message: 'Этот пароль слишком простой')]
        public readonly string $password,

        #[Assert\NotBlank(message: 'Повтор пароля обязателен')]
        #[Assert\IdenticalTo(
            propertyPath: 'password',
            message: 'Пароли не совпадают'
        )]
        public readonly string $passwordRepeat,
    ) {}
}
