<?php

namespace KnpU\CodeBattle\Controller\Api;

use KnpU\CodeBattle\Controller\BaseController;
use KnpU\CodeBattle\Security\Token\ApiToken;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TokenController extends BaseController
{
    protected function addRoutes(ControllerCollection $controllers)
    {
        $controllers->post('/api/tokens', [$this, 'newAction']);
        $controllers->get('/api/tokens/{id}', [$this, 'showAction'])->bind('api_tokens_show');
    }

    public function newAction(Request $request)
    {
        $this->enforceUserSecurity();

        $data = $this->decodeRequestBodyIntoParameters($request);
        $token = new ApiToken($this->getLoggedInUser()->id);
        $token->notes = $data->get('notes');

        if ($errors = $this->validate($token)) {
            return $this->throwApiProblemException($errors);
        }

        $this->getApiTokenRepository()->save($token);

        $url = $this->generateUrl('api_tokens_show', [
            'id' => $token->id,
        ]);

        $response = $this->createApiResponse($token, Response::HTTP_CREATED);
        $response->headers->set('Location', $url);

        return $response;
    }

    public function showAction($id)
    {
        $token = $this->getApiTokenRepository()->find($id);
        if (!$token) {
            $this->throw404('Token not found!');
        }

        $response = $this->createApiResponse($token, Response::HTTP_OK);

        return $response;
    }
}