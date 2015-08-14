<?php
    namespace App\Room103Bundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\HttpFoundation\Request;
    use App\Room103Bundle\Form\Type\RegistrationType;
    use App\Room103Bundle\Form\Model\Registration;

    class AccountController extends Controller
    {
        public function registerAction()
        {
            $registration = new Registration();
            $form = $this->createForm(new RegistrationType(), $registration, array(
                'action' => $this->generateUrl('account_create'),
            ));

            return $this->render(
                'AppRoom103Bundle:Account:register.html.twig',
                array('form' => $form->createView())
            );
        }
        public function createAction(Request $request)
        {
            $em = $this->getDoctrine()->getManager();

            $form = $this->createForm(new RegistrationType(), new Registration());

            $form->handleRequest($request);

            if ($form->isValid()) {
                $registration = $form->getData();

                $em->persist($registration->getUser());
                $em->flush();

                return $this->redirectToRoute('news');
            }

            return $this->render(
                'AppRoom103Bundle:Account:register.html.twig',
                array('form' => $form->createView())
            );
        }
    }