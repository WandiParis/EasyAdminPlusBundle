<?php

namespace Lle\EasyAdminPlusBundle\Service\Actions;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Exception\LogicException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class AddSublistAction
{
    private $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    public function __invoke(Request $request)
    {
        $parent_id = $request->query->get('parent_id');
        $parent_entity = $request->query->get('parent_entity');
        $entity = $request->query->get('entity');
        $parent = $this->em->getRepository($parent_entity)->find($parent_id);
        $form = $request->request->get('form');
        if(isset($form['item_id']) && isset($form['item_id']['autocomplete'])) {
            $child_id = $request->request->get('form')['item_id']['autocomplete'];
            $child = $this->em->getRepository($entity)->find($child_id);

            $namespace = explode('\\', $entity);
            $addMethod = 'add' . array_pop($namespace);
            $parent->$addMethod($child);

            $this->em->persist($parent);
            $this->em->flush();
        }
        if ($request->server->get('HTTP_REFERER')) {
            return new RedirectResponse($request->server->get('HTTP_REFERER'));
        } else {
            return new RedirectResponse('/');
        }
    }
}
