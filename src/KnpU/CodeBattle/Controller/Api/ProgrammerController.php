<?php

namespace KnpU\CodeBattle\Controller\Api;

use KnpU\CodeBattle\Controller\BaseController;
use KnpU\CodeBattle\Model\Programmer;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProgrammerController extends BaseController
{
    protected function addRoutes(ControllerCollection $controllers)
    {
        $controllers->post('/api/programmers', [$this, 'newAction']);
        $controllers->get('/api/programmers/{nickname}', [$this,
            'showAction'])->bind('api_programmers_show');
        $controllers->get('/api/programmers', [$this, 'listAction']);
    }

    public function newAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $programmer = new Programmer();
        $programmer->nickname     = $data['nickname'];
        $programmer->avatarNumber = $data['avatarNumber'];
        $programmer->tagLine      = $data['tagLine'];
        $programmer->userId       = $this->findUserByUsername('weaverryan')->id;
        $this->save($programmer);

        $url = $this->generateUrl('api_programmers_show', [
            'nickname' => $programmer->nickname,
        ]);
        $data = $this->serializeProgrammer($programmer);
        $response = new JsonResponse($data, Response::HTTP_CREATED);
        $response->headers->set('Location', $url);

        return $response;
    }

    public function showAction($nickname)
    {
        $programmer = $this->getProgrammerRepository()
            ->findOneByNickName($nickname);
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
}
