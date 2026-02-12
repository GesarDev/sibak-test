<?php

namespace App\Application;

use App\Entity\FeedbackMessage;
use App\Entity\User;
use App\Http\Dto\FeedbackRequest;
use App\Http\Json\ValidationErrors;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateFeedbackHandler
{
    public function __construct(
        private EntityManagerInterface $em,
        private ValidatorInterface $validator,
    ) {}

    /**
     * @return array{feedback?:array, errors?:array<string,string>}
     */
    public function handle(FeedbackRequest $req, ?User $matchedUser): array
    {
        $violations = $this->validator->validate($req);
        if (\count($violations) > 0) {
            return ['errors' => ValidationErrors::fromViolations($violations)];
        }

        $fb = (new FeedbackMessage())
            ->setEmail(mb_strtolower(trim($req->email)))
            ->setMessage(trim($req->message))
            ->setUser($matchedUser);

        $violations = $this->validator->validate($fb);
        if (\count($violations) > 0) {
            return ['errors' => ValidationErrors::fromViolations($violations)];
        }

        $this->em->persist($fb);
        $this->em->flush();

        // Определяем автора сообщения
        $author = $fb->getEmail();
        if ($matchedUser) {
            $author = $matchedUser->getName();
        }

        return [
            'feedback' => [
                'id' => $fb->getId(),
                'author' => $author,
                'message' => $fb->getMessage(),
            ],
        ];
    }
}
