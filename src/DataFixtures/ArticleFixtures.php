<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Category;

class ArticleFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        //Creation d'une variable qui sera une instance de faker  avec de fausses données en français
        $faker = \Faker\Factory::create('fr_FR');

        //Créer 3 catégories fakées
        for($i = 1 ; $i <= 3 ; $i++){
            $category = new Category();
            $category->setTitle($faker->sentence())
                        ->setDescription($faker->paragraph());
            $manager->persist($category);           
        

        // $product = new Product();
        // $manager->persist($product);
         // Créer entre 4 et 6 articles
        for ($j = 1; $j <= mt_rand(4, 6); $j++){
            // Création de l'article et configuration des attributs
            $article = new Article();
            
            $content = '<p>' . join($faker->paragraphs(5), '</p><p>') . '</p>';

            $article->setTitle($faker->sentence())
                    ->setContent($content)
                    ->setImage($faker->imageUrl())
                    //Création d'une date entre : il y a 6 mois et maintenant
                    ->setCreatedAt($faker->dateTimeBetween('-6months'))
                    ->setCategory($category);
            
            //Ls données persisteront dans le temps
            $manager->persist($article);

            for($k=1 ; $k<= mt_rand(4, 10) ; $k++){
                $comment = new Comment();

                $content = '<p>' . join($faker->paragraphs(2), '</p><p>') . '</p>';

                $now = new \DateTime();
                //Interval représente la différence entre maintenant et la date de création de l'article
                $interval = $now->diff($article->getCreatedAt());

                //Il m'affiche l'intervale en jours
                $days = $interval->days;

                //Il affichera par exemple -100 days
                $minimum = '-' . $days . ' days';

                $comment->setAuthor($faker->name)
                        ->setContent($content)
                        //faked va m'afficher une date entre le jour de création de l'article et maintenant
                        ->setCreatedAt($faker->dateTimeBetween($minimum))
                        ->setArticle($article);

                $manager->persist($comment);
           
                }
            }
        }
        //Envoi des données dans la bdd
        $manager->flush();
    }
}
