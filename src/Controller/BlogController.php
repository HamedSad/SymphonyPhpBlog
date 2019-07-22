<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Repository\ArticleRepository;
use App\Entity\Article;
use App\Entity\Comment;
use App\Form\ArticleType;
use App\Form\CommentType;

class BlogController extends AbstractController
{

    /**
     * @Route("/blog", name="blog")
     */
    public function index(ArticleRepository $repo)
    {
        //Avoir acces au repository de l'entité Article
        
        $articles = $repo->findAll();

        return $this->render('blog/index.html.twig', [
            'controller_name' => 'BlogController',
            'articles' => $articles
        ]);
    }

    /**
     * @Route("/", name="home")
     */
    public function home(){
        return $this->render('blog/home.html.twig' ,[
            // Tableau avec les variables que twig va devoir utiliser
            // La variable title va correspondre à Bienvenue les amis
            'title' => "Bienvenue les amis",
            'age' => 31
        ]);
    }


    /**
     * @Route("/blog/new", name="blog_create")
     * @Route("/blog/{id}/edit", name="blog_edit")
     */
    public function form(Article $article = null, Request $request, ObjectManager $objectManager){

        //Si l'article est null alors il va créer un nouvel article (pas besoin d'Id)
        if(!$article){
            //1 On a un article qui est vide
            $article = new Article();
        }
        
        //Fonction createFormBuilder() pour créer un formulaire et passe lui une entité. Ici 
        //il va créer un formulaire associé à article
        
        //2 on va lier cet article à notre formulaire via un nouveau formulaire graçe à createFormBuilder
        // $form = $this->createFormBuilder($article)
        //             ->add('title')
        //             ->add('content')
        //             ->add('image')
        //             //Résultat final
        //             ->getForm();

        //va directement chercher le formulaire ArticleType 
        $form = $this->createForm(ArticleType::class, $article);

        //Il analyse la requete http en parametre (il vérifie si il y a un title, content, image)
        //Ensuite il ira lier le title avec celui de $article
        //3 au moment où on demande au formulaire d'analyser la requete, il est capable de voir les champs de la requete
        $form->handleRequest($request);

        //4 quand tout est ok, on enregistre notre article mais avant on vérifie si on a soumis l'article
        //et  si les données sont valides
        if ($form->isSubmitted() && $form->isValid()){

            //Si l'article a un identifiant (sous-entendu existe deja)
            if(!$article->getId()){
                //donner une date
                $article->setCreatedAt(new \DateTime());
            }
            //on fait persister l'article
            $objectManager->persist($article);
            //on envoie les données à la bdd
            $objectManager->flush();
               
            //une fois que tout est enregistré redirige moi vers l'article que je viens de créer
            return $this->redirectToRoute('blog_show', ['id' =>$article->getId()]);
        }

        return $this->render('blog/create.html.twig', [
            //twig aura uniquement le resultat de la fonction createView
            'formArticle' => $form->createView(),
            //Si l'article a un identifiant (sous-entendu existe deja) 
            //Dans le fichier twig on ajoutera une condition
            'editMode' => $article->getId() !==null
        ]);
    }

    /**
     * @Route("/blog/{id}", name="blog_show")
     * Injection de dépendance ObjectManager pour manipuler les données
     * Injection de dépendance Request pour récupérer les données du formulaire des commentaires
     */

    public function show(Article $article, Request $request, ObjectManager $manager){

        $comment = new Comment();

        $form =$this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            // On lui donne une date (celle de l'instant T)
            $comment->setCreatedAt(new \DateTime())
            //Et lui dire qu'il appartient à l'article de la variable
                    ->setArticle($article);

            //on fait persister le commentaire
            $manager->persist($comment);
            //on envoie les données à la bdd
            $manager->flush();

            return $this->redirectToRoute('blog_show', [
                // Redirection vers la l'article sur lequel il y a eu le commentaire
                'id' => $article->getId()
            ]);

        }

        return $this->render('blog/show.html.twig', [
            // Twig va afficher l'article et le formulaire me permettant de faire un commentaire
            'article'=> $article,
            'commentForm' => $form->createView()
        ]);
    }

    
}
