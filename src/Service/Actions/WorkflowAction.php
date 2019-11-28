<?php

namespace Lle\EasyAdminPlusBundle\Service\Actions;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Exception\LogicException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class WorkflowAction
{
    private $workflows;
    private $em;

    public function __construct( Registry $workflows, EntityManagerInterface $em) {
        $this->workflows = $workflows;
        $this->em = $em;
    }

    public function __invoke(Request $request)
    {
        $id = $request->query->get('id');
        $entity = $request->query->get('class');
        $transition = $request->query->get('transition');
        $object = $this->em->getRepository($entity)->find($id);
        if(!$object){
            throw new NotFoundHttpException('not found');
        }
        $workflow = $this->workflows->get($object);

        try {
            $workflow->apply($object, $transition);
            $this->em->flush();
        } catch (LogicException $exception) {
            $request->getSession()->getFlashBag()->add('error', $exception->getMessage());
        }
        if ($request->server->get('HTTP_REFERER')) {
            return new RedirectResponse($request->server->get('HTTP_REFERER'));
        } else {
            return new RedirectResponse('/');
        }
    }
}
