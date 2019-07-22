<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/inscription", name="security_registration")
     */
    //On a besoin de l'object manager pour enregistrer l'user en bdd et de la requette http pour l'analyser
    public function registration(Request $request, ObjectManager $manager, 
    UserPasswordEncoderInterface $encoder)
    {
        $user = new User();
        //On relie les champs du formulaire aux champs de l'user
        $form = $this->createForm(RegistrationType::class, $user);

        //Le formulaire analyse la request
        $form->handleRequest($request);

        // 2/ Puis, si le formulaire est soumis et que tous ses champs sont valides alors :
        if($form->isSubmitted() && $form->isValid()){

            // Avant de persister, je vais encoder le mot de passe
            $hash = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hash);
            //alors il fera persister les données de l'user
            $manager->persist($user);
            //ensuite envoie tout à la bdd
            $manager->flush();
            //Une fois que tout est enregistré redirige moi vers telle vue
            return $this->redirectToRoute('security_login');
        }

        // 1/ D'abord la fonction m'affiche le formulaire dans la vue
        return $this->render('security/registration.html.twig', [ 
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/connexion", name="security_login")
     * Il ira voir dans security.yaml qu'il existe un path qui s'appelle security_login
     */
    public function login(){
        return $this->render('security/login.html.twig');
    }

    /**
     * @Route("/deconnexion", name="security_logout")
     * Ici c'est encore security.yaml qui va gérer en fonction du nom de la route qu'on a donné
     */
    public function logout(){

    }

}
