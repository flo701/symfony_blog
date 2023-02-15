<?php

namespace App\DataFixtures;

// https://blog.gary-houbre.fr/developpement/symfony/symfony-comment-mettre-en-place-des-fixtures#:~:text=Fixtures%20(jeu%20de%20donn%C3%A9es)%20est,production%20avec%20des%20fausses%20donn%C3%A9es.
// Fixtures (jeu de données) est un ensemble de données qui permet d’avoir un environnement de développement proche d’un environnement de production avec des fausses données.

use App\Entity\Tag;
use App\Entity\Bulletin;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class BulletinFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        //La méthode load() ne fait strictement rien par elle-même si ce n'est suivre la purge de la base de données effectuée au moment de la commande "php bin/console doctrine:fixtures:load". La véritable méthode derrière la persistance des données est la méthode persist() d'ObjectManager, mise en pratique grâce à la méthode flush(). Il faut donc se concentrer sur ObjectManager et non sur notre classe de Fixtures, laquelle fournit simplement un environnement pour la persistance facile de données.

        // On crée un tableau conservant les bulletins créés :
        $bulletins = [];
        $categories = ['general', 'divers', 'urgent'];
        $tagNames = ['PHP', 'Symfony', 'Doctrine', 'Twig', 'MVC', 'Info', 'Composer', 'Symfony (CLI)', 'JavaScript', 'C++'];

        for ($i = 0; $i < 50; $i++) {
            $bulletin = new Bulletin("Bulletin Fixtures");
            $bulletin->setCategory($categories[rand(0, count($categories) - 1)]);
            array_push($bulletins, $bulletin);
            $manager->persist($bulletin);
        }

        // On crée nos tags, liés au hasard aux bulletins :
        foreach ($tagNames as $tagName) {
            $tag = new Tag; // On crée un nouveau Tag
            $tag->setName($tagName);
            foreach ($bulletins as $bulletin) {
                if (rand(0, 100) > 80) {
                    $tag->addBulletin($bulletin); // 20% de chances que notre tag soit lié à un des bulletins, pour chacun d'entre eux
                }
            }
            $manager->persist($tag);
        }

        $manager->flush();
    }
}
