<?php

namespace App\Room103Bundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Room103Bundle\Entity\Post;
use App\Room103Bundle\Form\Type\PostType;
use App\Room103Bundle\Entity\Comment;
use App\Room103Bundle\Form\Type\CommentType;
/**
 * Post controller.
 *
 * @Route("/news")
 */
class PostController extends Controller
{

    /**
     * Lists all Post entities.
     *
     * @Route("/", name="news")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('AppRoom103Bundle:Post')->findBy(['published' => 1]);

        return array(
            'entities' => $entities,
        );
    }

    /**
     * @Route("/suggested", name="news_suggested")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function suggestedAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('AppRoom103Bundle:Post')->findBy(['published' => null]);

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Creates a new Post entity.
     *
     * @Route("/", name="news_create")
     * @Method("POST")
     * @Template("AppRoom103Bundle:Post:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Post();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('news'));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }



    /**
     * Creates a form to create a Post entity.
     *
     * @param Post $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Post $entity)
    {
        $form = $this->createForm(new PostType(), $entity, array(
            'action' => $this->generateUrl('news_create'),
            'method' => 'POST',
        ));

        return $form;
    }

    /**
     * Displays a form to create a new Post entity.
     *
     * @Route("/new", name="news_new")
     * @Method("GET")
     * @Template()
     *
     */
    public function newAction()
    {
        $entity = new Post();
        $form = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Publish new post
     * @Route("/{slug}/publish", name="news_publish")
     * @Method("GET")
     * @Template()
     */
    public function publishAction($slug)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppRoom103Bundle:Post')->findOneBySlug($slug);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Post entity.');
        }
        $entity->setPublished(1);
        $em->flush();
        return $this->redirect($this->generateUrl('news'));
    }



    /**
     * Finds and displays a Post entity.
     *
     * @Route("/{slug}", name="news_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($slug)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppRoom103Bundle:Post')->findOneBySlug($slug);
        $comments = $entity->getComments();
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Post entity.');
        }
        if ($entity->getPublished() == 0){
            $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        }

        $comment = new Comment();

        $deleteForm = $this->createDeleteForm($slug);
        $commentForm = $this->createCommentForm($comment, $slug);


        return array(
            'comments' => $comments,
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
            'comment_form' => $commentForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Post entity.
     *
     * @Route("/{slug}/edit", name="news_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction($slug)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppRoom103Bundle:Post')->findOneBySlug($slug);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Post entity.');
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
    * Creates a form to edit a Post entity.
    *
    * @param Post $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Post $entity)
    {
        $form = $this->createForm(new PostType(), $entity, array(
            'action' => $this->generateUrl('news_update', array('slug' => $entity->getSlug())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Comment entity.
     *
     * @Route("/comment/{slug}", name="comment_create")
     * @Method("POST")
     * @Template()
     *
     */
    public function newCommentAction(Request $request, $slug)
    {

        $comment = new Comment();

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppRoom103Bundle:Post')->findOneBySlug($slug);

        $form = $this->createCommentForm($comment, $slug);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $comment->setPost($entity);
            $em->persist($comment);

            $em->flush();
            return $this->redirect($this->generateUrl('news'));
        }

        return array(
            'comment' => $comment,
            'form' => $form->createView(),
        );
    }
    /**
     * Creates a form to edit a Comment entity.
     *
     * @param Comment $comment The comment
     *
     * @return \Symfony\Component\Form\Form The form
     */

    private function createCommentForm(Comment $comment, $slug)
    {
        $form = $this->createForm(new CommentType(), $comment, array(
            'action' => $this->generateUrl('comment_create', array('slug' => $slug)),
            'method' => 'POST',
        ) );


        $form->add('submit', 'submit', array('label' => 'Submit'));


        return $form;


    }
    /**
     * Edits an existing Post entity.
     *
     * @Route("/{slug}", name="news_update")
     * @Method("PUT")
     * @Template("AppRoom103Bundle:Post:edit.html.twig")
     */
    public function updateAction(Request $request, $slug)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppRoom103Bundle:Post')->findOneBySlug($slug);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Post entity.');
        }

        $deleteForm = $this->createDeleteForm($slug);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('news'));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Post entity.
     *
     * @Route("/{slug}", name="news_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, $slug)
    {
        $form = $this->createDeleteForm($slug);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('AppRoom103Bundle:Post')->findOneBySlug($slug);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Post entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('news'));
    }

    /**
     * Creates a form to delete a Post entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($slug)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('news_delete', array('slug' => $slug)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
