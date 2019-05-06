<?php

namespace KnpU\CodeBattle\Controller\Api;

use KnpU\CodeBattle\Controller\BaseController;
use KnpU\CodeBattle\Model\Programmer;
use KnpU\CodeBattle\Model\Project;
use KnpU\CodeBattle\Security\Token\ApiToken;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BattleController extends BaseController
{
    protected function addRoutes(ControllerCollection $controllers)
    {
        $controllers->post('/api/battles', [$this, 'newAction']);
        $controllers->get('/api/battles/{id}', [$this, 'showAction'])->bind('api_battles_show');
    }

    public function newAction(Request $request)
    {
        $this->enforceUserSecurity();

        $data = $this->decodeRequestBodyIntoParameters($request);
        /** @var Programmer $programmer */
        $programmer = $this->getProgrammerRepository()->find($data->get('programmerId'));
        /** @var Project $project */
        $project = $this->getProjectRepository()->find($data->get('projectId'));

        $errors = [];
        if (!$programmer) {
            $errors['programmerId'] = 'Invalid or missing programmerId';
        }
        if (!$project) {
            $errors['projectId'] = 'Invalid or missing projectId';
        }
        if ($errors) {
            $this->throwApiProblemException($errors);
        }

        $battle = $this->getBattleManager()->battle($programmer, $project);

        $url = $this->generateUrl('api_battles_show', [
            'id' => $battle->id,
        ]);

        $response = $this->createApiResponse($battle, Response::HTTP_CREATED);
        $response->headers->set('Location', $url);

        return $response;
    }

    public function showAction($id)
    {
        $battle = $this->getBattleRepository()->find($id);
        if (!$battle) {
            $this->throw404('Battle not found!');
        }

        $response = $this->createApiResponse($battle, Response::HTTP_OK);

        return $response;
    }
}