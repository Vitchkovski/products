<?php

namespace Vitchkovski\ProductsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class SecurityController extends Controller
{
    public function loginAction(Request $request)
    {

        if ($this->isGranted('ROLE_USER') == true) {
            //if user is already authorized showing personal page instead of login form
            return $this->redirectToRoute('VitchkovskiProductsBundle_userPersonalPage');
        }

        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
            'VitchkovskiProductsBundle:Login:login.html.twig',
            array(
                // last username entered by the user
                'last_username' => $lastUsername,
                'error'         => $error,
            )
        );

    }
}
