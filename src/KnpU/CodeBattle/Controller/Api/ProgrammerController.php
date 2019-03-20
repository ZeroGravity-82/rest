<?php

namespace KnpU\CodeBattle\Controller\Api;

use KnpU\CodeBattle\Api\ApiProblem;
use KnpU\CodeBattle\Api\ApiProblemException;
use KnpU\CodeBattle\Controller\BaseController;
use KnpU\CodeBattle\Model\Programmer;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ProgrammerController extends BaseController
{
    protected function addRoutes(ControllerCollection $controllers)
    {
        $controllers->post('/api/programmers', [$this, 'newAction']);
        $controllers->get('/api/programmers/{nickname}', [$this, 'showAction'])->bind('api_programmers_show');
        $controllers->get('/api/programmers', [$this, 'listAction']);
        $controllers->put('/api/programmers/{nickname}', [$this, 'updateAction']);
        $controllers->match('/api/programmers/{nickname}', [$this, 'updateAction'])->method("PATCH");
        $controllers->delete('/api/programmers/{nickname}', [$this, 'deleteAction']);
    }

    public function newAction(Request $request)
    {
        $programmer = new Programmer();
        $this->handleRequest($request, $programmer);
        $errors = $this->validate($programmer);
        if (!empty($errors)) {
            return $this->throwApiProblemException($errors);
        }
        $this->save($programmer);

        $url = $this->generateUrl('api_programmers_show', [
            'nickname' => $programmer->nickname,
        ]);
        $data = $this->serializeProgrammer($programmer);
        $response = new JsonResponse($data, Response::HTTP_CREATED);
        $response->headers->set('Location', $url);

        return $response;
    }

    public function updateAction(Request $request, $nickname)
    {
        $programmer = $this->getProgrammerRepository()->findOneByNickName($nickname);
        if (!$programmer && $request->isMethod('PUT')) {
            $programmer = new Programmer();
        }
        if (!$programmer && $request->isMethod('PATCH')) {
            $this->throw404('Programmer not found.');
        }

        $this->handleRequest($request, $programmer);
        $errors = $this->validate($programmer);
        if (!empty($errors)) {
            return $this->throwApiProblemException($errors);
        }
        $this->save($programmer);

        $data = $this->serializeProgrammer($programmer);
        $response = new JsonResponse($data, Response::HTTP_OK);

        return $response;
    }

    public function deleteAction($nickname)
    {
        $programmer = $this->getProgrammerRepository()->findOneByNickName($nickname);
        $this->delete($programmer);

        $response = new Response(null, Response::HTTP_NO_CONTENT);

        return $response;
    }

    public function showAction($nickname)
    {
        $programmer = $this->getProgrammerRepository()->findOneByNickName($nickname);
        if (!$programmer) {
            $this->throw404('Programmer not found!');
        }

        $data = $this->serializeProgrammer($programmer);
        $response = new JsonResponse($data, Response::HTTP_OK);

        return $response;
    }

    public function listAction()
    {
        $programmers = $this->getProgrammerRepository()
            ->findAll();

        $data = [
            'programmers' => [],
        ];
        foreach ($programmers as $programmer) {
            $data['programmers'][] = $this->serializeProgrammer($programmer);
        }

        $response = new JsonResponse($data, Response::HTTP_OK);

        return $response;
    }

    private function serializeProgrammer(Programmer $programmer)
    {
        $data = [
            'nickname'     => $programmer->nickname,
            'avatarNumber' => $programmer->avatarNumber,
            'tagLine'      => $programmer->tagLine,
            'userId'       => $programmer->userId,
            'powerLevel'   => $programmer->powerLevel,
        ];

        return $data;
    }

    private function handleRequest(Request $request, Programmer $programmer)
    {
        $data = json_decode($request->getContent(), true);
        if ($data === null) {
            $apiProblem = new ApiProblem(Response::HTTP_BAD_REQUEST, ApiProblem::TYPE_INVALID_BODY_FORMAT);

            throw new ApiProblemException($apiProblem);
        }

        $isNew = !$programmer->id;
        $apiProperties = ['avatarNumber', 'tagLine'];
        if ($isNew) {
            $apiProperties[] = 'nickname';
        }
        foreach ($apiProperties as $property) {
            if ($request->isMethod('PATCH') && !isset($data[$property])) {
                continue;
            }
            $programmer->$property = $data[$property] ?? null;
        }

        $programmer->userId = $this->findUserByUsername('weaverryan')->id;
    }

    private function throwApiProblemException(array $errors)
    {
        $apiProblem = new ApiProblem(Response::HTTP_UNPROCESSABLE_ENTITY, ApiProblem::TYPE_VALIDATION_ERROR);
        $apiProblem->set('errors', $errors);

        throw new ApiProblemException($apiProblem);
    }
}
