<?php

namespace App\Controller;

use App\Repository\FeedbackMessageRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class HomeController extends AbstractController
{
    /**
     * Рендерит обе формы и последние записи пользователей и сообщений.
     */
    #[Route('/', name: 'home', methods: ['GET'])]
    public function __invoke(
        UserRepository $users,
        FeedbackMessageRepository $feedback,
        CsrfTokenManagerInterface $csrf,
    ): Response {
        $lastFeedback = $feedback->findBy([], ['id' => 'DESC'], 10);
        $feedbackEmails = [];
        foreach ($lastFeedback as $item) {
            $email = mb_strtolower(trim((string) $item->getEmail()));
            if ($email !== '') {
                $feedbackEmails[$email] = true;
            }
        }

        return $this->render('home/index.html.twig', [
            'csrf' => $csrf->getToken('api')->getValue(),
            'lastUsers' => $users->findBy([], ['id' => 'DESC'], 10),
            'lastFeedback' => $lastFeedback,
            'userNamesByEmail' => $users->findNamesByEmails(array_keys($feedbackEmails)),
        ]);
    }
}
