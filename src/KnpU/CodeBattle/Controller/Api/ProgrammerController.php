<?php

namespace KnpU\CodeBattle\Controller\Api;

use Hateoas\Representation\CollectionRepresentation;
use KnpU\CodeBattle\Api\ApiProblem;
use KnpU\CodeBattle\Api\ApiProblemException;
use KnpU\CodeBattle\Controller\BaseController;
use KnpU\CodeBattle\Model\Programmer;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

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

        if ($errors = $this->validate($programmer)) {
            return $this->throwApiProblemException($errors);
        }

        $this->save($programmer);

        $url = $this->generateUrl('api_programmers_show', [
            'nickname' => $programmer->nickname,
        ]);
        $response = $this->createApiResponse($programmer, Response::HTTP_CREATED);
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

        $this->enforceProgrammerOwnershipSecurity($programmer);

        $this->handleRequest($request, $programmer);
        $errors = $this->validate($programmer);
        if (!empty($errors)) {
            return $this->throwApiProblemException($errors);
        }
        $this->save($programmer);

        $response = $this->createApiResponse($programmer, Response::HTTP_OK);

        return $response;
    }

    public function deleteAction($nickname)
    {
        $programmer = $this->getProgrammerRepository()->findOneByNickName($nickname);

        $this->enforceProgrammerOwnershipSecurity($programmer);

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

        $response = $this->createApiResponse($programmer, Response::HTTP_OK);

        return $response;
    }

    public function listAction()
    {
        $programmers = $this->getProgrammerRepository()->findAll();
        $collection = new CollectionRepresentation([
                'programmers' => $programmers,
            ]
        );

        $response = $this->createApiResponse($collection, Response::HTTP_OK);

        return $response;
    }

    private function handleRequest(Request $request, Programmer $programmer)
    {
        $this->enforceUserSecurity();

        $data = $this->decodeRequestBodyIntoParameters($request);

        $isNew = !$programmer->id;
        $apiProperties = ['avatarNumber', 'tagLine'];
        if ($isNew) {
            $apiProperties[] = 'nickname';
        }
        foreach ($apiProperties as $property) {
            if ($request->isMethod('PATCH') && !$data->has($property)) {
                continue;
            }
            $programmer->$property = $data->get($property);
        }

        $programmer->userId = $this->getLoggedInUser()->id;
    }
}
