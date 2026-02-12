<?php

namespace App\Http\Json;

use Symfony\Component\Validator\ConstraintViolationListInterface;

final class ValidationErrors
{
    /**
     * @param array<string,string> $fieldMap
     * @return array<string,string> поле => сообщение
     */
    public static function fromViolations(ConstraintViolationListInterface $violations, array $fieldMap = []): array
    {
        $out = [];
        foreach ($violations as $v) {
            $field = (string)$v->getPropertyPath();
            if ($field !== '' && isset($fieldMap[$field])) {
                $field = $fieldMap[$field];
            }
            $out[$field !== '' ? $field : '_global'] = (string)$v->getMessage();
        }
        return $out;
    }
}
