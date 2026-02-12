<?php

namespace App\Http\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class FeedbackRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Email обязателен')]
        #[Assert\Email(message: 'Некорректный email')]
        #[Assert\Length(max: 180)]
        public readonly string $email,

        #[Assert\NotBlank(message: 'Сообщение обязательно')]
        #[Assert\Length(min: 5, max: 5000, minMessage: 'Сообщение минимум 5 символов')]
        public readonly string $message,
    ) {}
}