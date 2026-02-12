<?php

namespace App\Http\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class FeedbackRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Email обязателен')]
        #[Assert\Email(
            message: 'Некорректный email адрес',
            mode: 'strict'
        )]
        #[Assert\Length(max: 255)]
        public readonly string $email,

        #[Assert\NotBlank(message: 'Сообщение обязательно')]
        #[Assert\Length(
            min: 5,
            max: 5000,
            minMessage: 'Сообщение минимум {{ limit }} символов',
            maxMessage: 'Сообщение максимум {{ limit }} символов'
        )]
        #[Assert\Type('string')]
        public readonly string $message,
    ) {}
}