<?php

namespace KnpU\CodeBattle\Api;

use Symfony\Component\HttpFoundation\JsonResponse;

class ApiProblemResponseFactory
{
    public function createResponse(ApiProblem $problem)
    {
        $data = $problem->toArray();
        if ($data['type'] !== 'about:blank') {
            $data['type'] = 'http://localhost:8082/docs/errors#'.$data['type'];
        }
        $response = new JsonResponse(
            $data,
            $problem->getStatusCode()
        );
        $response->headers->set('Content-Type', 'application/problem+json');

        return $response;
    }
}
