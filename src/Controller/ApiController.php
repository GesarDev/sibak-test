<?php

namespace App\Controller;

use App\Application\CreateFeedbackHandler;
use App\Application\RegisterUserHandler;
use App\Http\Dto\FeedbackRequest;
use App\Http\Dto\RegisterRequest;
use App\Http\Json\JsonBody;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class ApiController extends AbstractController
{
    private const CSRF_ID = 'api';

    /**
     * Регистрирует пользователя и возвращает JSON с данными пользователя
     * или ошибками валидации.
     */
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        CsrfTokenManagerInterface $csrf,
        RegisterUserHandler $handler,
    ): JsonResponse {
        // Проверяем CSRF токен
        $csrfError = $this->guardCsrf($request, $csrf);
        if ($csrfError) {
            return $csrfError;
        }

        $data = JsonBody::parse($request);
        $dto = new RegisterRequest(
            name: JsonBody::str($data, 'name'),
            email: JsonBody::str($data, 'email'),
            phone: JsonBody::str($data, 'phone'),
            password: JsonBody::str($data, 'password'),
            passwordRepeat: JsonBody::str($data, 'password_repeat'),
        );

        $result = $handler->handle($dto);
        if (isset($result['errors'])) {
            return $this->json(['errors' => $result['errors']], 422);
        }

        return $this->json($result, 200);
    }

    /**
     * Создаёт сообщение обратной связи и определяет автора отображения
     * по переданному email.
     */
    #[Route('/api/feedback', name: 'api_feedback', methods: ['POST'])]
    public function feedback(
        Request $request,
        CsrfTokenManagerInterface $csrf,
        UserRepository $users,
        CreateFeedbackHandler $handler,
    ): JsonResponse {
        $csrfError = $this->guardCsrf($request, $csrf);
        if ($csrfError) {
            return $csrfError;
        }

        $data = JsonBody::parse($request);
        $dto = new FeedbackRequest(
            email: JsonBody::str($data, 'email'),
            message: JsonBody::str($data, 'message'),
        );

        $matchedUser = $users->findOneBy([
            'email' => mb_strtolower(trim($dto->email)),
        ]);

        $result = $handler->handle($dto, $matchedUser);
        if (isset($result['errors'])) {
            return $this->json(['errors' => $result['errors']], 422);
        }

        return $this->json($result, 200);
    }

    /**
     * Проверяет CSRF токен и возвращает JSON ошибку если невалидный.
     * TODO: возможно стоит вынести в отдельный middleware
     */
    private function guardCsrf(Request $request, CsrfTokenManagerInterface $csrf): ?JsonResponse
    {
        // Берем токен из заголовка
        $token = (string)$request->headers->get('X-CSRF-TOKEN', '');
        if (!$csrf->isTokenValid(new CsrfToken(self::CSRF_ID, $token))) {
            return $this->json(["message" => 'CSRF token invalid'], 419);
        }
        return null;
    }
}
