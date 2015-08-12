<?php

namespace App\Room103Bundle\Controller;

use App\Room103Bundle\Entity\Post;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use App\Room103Bundle\Entity\Suggestion;
use App\Room103Bundle\Form\SuggestionType;

/**
 * Suggestion controller.
 *
 * @Route("/suggestion")
 */
class SuggestionController extends Controller
{

    /**
     * Lists all Suggestion entities.
     *
     * @Route("/", name="suggestion")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');

        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('AppRoom103Bundle:Suggestion')->findAll();

        return array(
            'entities' => $entities,
        );
    }

    /**
     * @Route("/publish/{slug}", name="suggestion_publish")
     *
     */
    public function publishAction($slug)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppRoom103Bundle:Suggestion')->findOneBySlug($slug);


        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Suggestion entity.');
        }

        $post = new Post();

        $post = $this->postFromSuggestion($post,$entity);
        $em->remove($entity);

        $em->persist($post);
        $em->flush();


        return $this->redirect($this->generateUrl('news'));



    }
    /**
     * Creates a new Suggestion entity.
     *
     * @Route("/", name="suggestion_create")
     * @Method("POST")
     * @Template("AppRoom103Bundle:Suggestion:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Suggestion();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity->upload();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('suggestion_show', array('slug' => $entity->getSlug())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Suggestion entity.
     *
     * @param Suggestion $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Suggestion $entity)
    {
        $form = $this->createForm(new SuggestionType(), $entity, array(
            'action' => $this->generateUrl('suggestion_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Suggestion entity.
     *
     * @Route("/new", name="suggestion_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Suggestion();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Suggestion entity.
     *
     * @Route("/{slug}", name="suggestion_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($slug)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppRoom103Bundle:Suggestion')->findOneBySlug($slug);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Suggestion entity.');
        }

        $deleteForm = $this->createDeleteForm($slug);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Suggestion entity.
     *
     * @Route("/{slug}/edit", name="suggestion_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($slug)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppRoom103Bundle:Suggestion')->findOneBySlug($slug);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Suggestion entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($slug);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Suggestion entity.
    *
    * @param Suggestion $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Suggestion $entity)
    {
        $form = $this->createForm(new SuggestionType(), $entity, array(
            'action' => $this->generateUrl('suggestion_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Suggestion entity.
     *
     * @Route("/{slug}", name="suggestion_update")
     * @Method("PUT")
     * @Template("AppRoom103Bundle:Suggestion:edit.html.twig")
     */
    public function updateAction(Request $request, $slug)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppRoom103Bundle:Suggestion')->findOneBySlug($slug);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Suggestion entity.');
        }

        $deleteForm = $this->createDeleteForm($slug);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('suggestion_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Suggestion entity.
     *
     * @Route("/{slug}", name="suggestion_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $slug)
    {
        $form = $this->createDeleteForm($slug);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('AppRoom103Bundle:Suggestion')->findOneBySlug($slug);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Suggestion entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('suggestion'));
    }

    /**
     * Creates a form to delete a Suggestion entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($slug)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('suggestion_delete', array('slug' => $slug)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }

    private function postFromSuggestion(Post $post, Suggestion $suggestion)
    {
        $post->setTitle($suggestion->getTitle());
        $post->setContent($suggestion->getContent());
        $post->setFile($suggestion->getFile());
        $post->setPath($suggestion->getPath());

        return $post;
    }
}
