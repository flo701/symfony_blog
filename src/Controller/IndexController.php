<?php

// Si index.php (dans le dossier "public") est le point de départ de la requête client vers le lancement de notre application, les classes .php de notre Controller sont où nous, en tant que développeurs, commençons le développement de notre application.

namespace App\Controller;

use App\Entity\Tag;
use App\Entity\Bulletin;
use App\Form\BulletinType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index')] // Annotation de la méthode
    public function index(ManagerRegistry $doctrine): Response // Déclaration de la méthode
    {
        // Cette page d'accueil affiche les différents bulletins enregistrés dans notre application web.

        // Nous récupérons l'Entity Manager ainsi que le Repository de l'Entity qui nous intéresse, ici, Bulletin :
        $entityManager = $doctrine->getManager();
        $bulletinRepository = $entityManager->getRepository(Bulletin::class);

        // On récupère la liste des Catégories :
        $categories = $bulletinRepository->findEachCategory();
        // dd($categories); dd() (dump) est une fonction Symfony/Twig permettant une analyse approfondie des variables placées en paramètre

        // Le Repository nous permet d'effectuer des recherches dans notre base de données et de récupérer toutes les entrées :
        $bulletins = $bulletinRepository->findAll();
        $bulletins = array_reverse($bulletins); // On inverse l'ordre des bulletins de manière à avoir les plus récents en premier.

        // Retour de la méthode :
        return $this->render('index/index.html.twig', [
            'categories' => $categories, // la clé 'categories' se retrouve dans header.html.twig
            'bulletins' => $bulletins, // la clé 'bulletins' se retrouve dans index.html.twig
        ]);
    }

    // ----------------------------------------------------------------------------------------------------------

    #[Route('/category/{categoryName}', name: 'index_category')]
    public function indexCategory(ManagerRegistry $doctrine, string $categoryName): Response
    {
        // Cette méthode affiche la liste des Bulletins appartenant à la catégorie indiquée via le paramètre de route.

        // Pour récupérer les bulletins pertinents, on fait appel à l'Entity Manager :
        $entityManager = $doctrine->getManager();
        $bulletinRepository = $entityManager->getRepository(Bulletin::class);

        // On récupère la liste des catégories pour la navbar :
        $categories = $bulletinRepository->findEachCategory();

        // On récupère la liste des bulletins dont la catégorie correspond au paramètre de route :
        $bulletins = $bulletinRepository->findBy(['category' => $categoryName,], ['id' => 'DESC']);
        // findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)

        // Retour de la méthode :
        return $this->render('index/index.html.twig', [
            'categories' => $categories,
            'bulletins' => $bulletins,
        ]);
    }

    // ----------------------------------------------------------------------------------------------------------

    #[Route('/tag/tag{tagId}', name: 'index_tag')]
    public function indexTag(ManagerRegistry $doctrine, int $tagId): Response
    {
        // Cette méthode présente tous les Bulletins liés à un Tag dont l'Id est présenté dans l'URL.

        // On récupère l'Entity Manager ainsi que le Repository de Tag :
        $entityManager = $doctrine->getManager();
        $tagRepository = $entityManager->getRepository(Tag::class);

        // On retrouve le Tag dont l'Id a été renseigné. Si le tag n'a pas été trouvé, nous retournons à l'index :
        $tag = $tagRepository->find($tagId);
        if (!$tag) {
            return $this->redirectToRoute('app_index');
        }

        // Puisque nous avons notre Tag, nous récupérons la liste de tous les Bulletins qui lui sont liés :
        $bulletins = $tag->getBulletins();

        // On transmet notre liste de Bulletins à index.html.twig :
        return $this->render('index/index.html.twig', ['bulletins' => $bulletins,]);
    }

    // ----------------------------------------------------------------------------------------------------------

    #[Route('/cheatsheet', name: 'index_cheatsheet')]
    public function cheatsheet(): Response
    {
        return $this->render('index/cheatsheet.html.twig', []);
    }

    // ----------------------------------------------------------------------------------------------------------

    #[Route('/bulletin/display/{bulletinId}', name: 'bulletin_display')]
    public function displayBulletin(ManagerRegistry $doctrine, int $bulletinId): Response
    {
        // Cette méthode nous affiche un seul bulletin en particulier, identifié via son ID :

        // Tout d'abord, nous avons besoin de l'Entity Manager ainsi que du Repository de Bulletin :
        $entityManager = $doctrine->getManager();
        $bulletinRepository = $entityManager->getRepository(Bulletin::class);

        // Nous recherchons le Bulletin, identifié par sa valeur ID :
        $bulletin = $bulletinRepository->find($bulletinId);

        // Si le bulletin n'est pas trouvé (et que $bulletin vaut null), nous retournons à l'index :
        if (!$bulletin) {
            return $this->redirectToRoute('app_index');
        }

        // Si le bulletin est trouvé, nous le transmettons à notre page index.html.twig :
        return $this->render('index/index.html.twig', [
            'bulletins' => [$bulletin], // On place l'unique bulletin dans un tableau
        ]);
    }

    // ----------------------------------------------------------------------------------------------------------

    #[Route('/bulletin/create', name: 'bulletin_create')]
    public function createBulletin(Request $request, ManagerRegistry $doctrine): Response
    {
        // Cette méthode nous renvoie vers un formulaire de création de Bulletin avant de gérer les données que nous aurons transmises par l'intermédiaire de celui-ci.

        // On récupère l'Entity Manager :
        $entityManager = $doctrine->getManager();

        // On crée une instance d'Entity Bulletin que nous lions à notre formulaire (ne pas oublier d'importer BulletinType) :
        $bulletin = new Bulletin();
        $bulletin->clearFields(); // On retire les valeurs générées

        $bulletinForm = $this->createForm(BulletinType::class, $bulletin);
        // $this->createForm() prend en paramètre le nom de la classe Type associée, ainsi qu'une instance de l'Entity liée.

        // Nous appliquons les valeurs enregistrées par notre objet Request sur le formulaire du bulletin :
        $bulletinForm->handleRequest($request);

        // Si notre bulletin est rempli et valide, nous le transférons vers notre base de données :
        if ($bulletinForm->isSubmitted() && $bulletinForm->isValid()) {
            // Nous indiquons à Doctrine que nous souhaitons que cette instance soit conservée (persiste dans son existence) via la méthode persist() :
            $entityManager->persist($bulletin); // Etant donné que $bulletin est lié au formulaire, $bulletin possède les informations du formulaire.
            // Nous demandons à Doctrine d'appliquer toutes les opérations que nous avons requises, via la méthode flush() :
            $entityManager->flush();

            //Nous retournons à l'index :
            return $this->redirectToRoute('app_index');
        }

        // Si le formulaire n'est pas rempli, nous le présentons :
        return $this->render('index/dataform.html.twig', ['formName' => 'Formulaire de création de Bulletin', 'dataForm' => $bulletinForm->createView()]);
        // On génére la vue de notre formulaire via la méthode $bulletinForm->createView() et dans le fichier Twig qui affichera le formulaire, on indique {{ form(dataForm) }}.
    }

    // ----------------------------------------------------------------------------------------------------------

    #[Route('bulletin/update/{bulletinId}', name: 'bulletin_update')]
    public function updateBulletin(Request $request, ManagerRegistry $doctrine, int $bulletinId): Response
    {
        // Cette méthode nous permet de modifier un bulletin dont l'iD est renseigné dans la route (via le paramètre de route)

        // On récupère l'Entity Manager et le Repository de l'Entité :
        $entityManager = $doctrine->getManager();
        $bulletinRepository = $entityManager->getRepository(Bulletin::class);

        // On récupère l'instance de la classe Bulletin qui nous intéresse grâce à la méthode find() du Repository, sinon nous retournons à l'index :
        $bulletin = $bulletinRepository->find($bulletinId);
        if (!$bulletin) {
            return $this->redirectToRoute('app_index');
        }

        $bulletinForm = $this->createForm(BulletinType::class, $bulletin); // createForm() est une fonction de Symfony
        // Nous appliquons les valeurs enregistrées par notre objet Request sur le formulaire du bulletin :
        $bulletinForm->handleRequest($request);

        // Si notre bulletin est rempli et valide, nous le transférons vers notre base de données :
        if ($bulletinForm->isSubmitted() && $bulletinForm->isValid()) {
            // Nous indiquons à Doctrine que nous souhaitons que cette instance soit conservée (persiste dans son existence) via la méthode persist() :
            $entityManager->persist($bulletin); // Etant donné que $bulletin est lié au formulaire, $bulletin possède les informations du formulaire.
            // Nous demandons à Doctrine d'appliquer toutes les opérations que nous avons requises, via la méthode flush() :
            $entityManager->flush();

            //Nous retournons à l'index :
            return $this->redirectToRoute('app_index');
        }

        // Si le formulaire n'est pas rempli, nous le présentons :
        return $this->render('index/dataform.html.twig', [
            'formName' => 'Formulaire de modification de Bulletin', 'dataForm' => $bulletinForm->createView()
        ]); // createView() prépare l'affichage
    }

    // ----------------------------------------------------------------------------------------------------------

    #[Route('/bulletin/delete/{bulletinId}', name: 'bulletin_delete')]
    public function deleteBulletin(ManagerRegistry $doctrine, int $bulletinId): Response
    {
        // Cette méthode nous permet de supprimer une instance d'Entité Bulletin si existante selon l'id qui nous a été renseigné.

        // On récupère l'Entity Manager et le Repository de l'Entité que nous voulons supprimer (Bulletin) :
        $entityManager = $doctrine->getManager();
        $bulletinRepository = $entityManager->getRepository(Bulletin::class);

        // On récupère l'instance de la classe Bulletin qui nous intéresse grâce à la méthode find() du Repository :
        $bulletin = $bulletinRepository->find($bulletinId);

        // Si un bulletin avec un ID correspondant existe, le Bulletin avec les informations pertinentes sera automatiquement instancié et retourné. Sinon, $bulletin vaudra null. Afin d'éviter une erreur suite à la requête de persistance de "null", nous allons mettre fin à la fonction si le Bulletin n'est pas trouvé :
        if (!$bulletin) {
            return $this->redirectToRoute('app_index');
        }

        // Puisque le bulletin existe, nous procédons à sa suppression :
        $entityManager->remove($bulletin);
        $entityManager->flush();

        // On retourne à l'index : 
        return $this->redirectToRoute('app_index');
    }

    // ----------------------------------------------------------------------------------------------------------

    #[Route('/generate', name: 'bulletin_generate')]
    public function generateBulletin(ManagerRegistry $doctrine): Response
    {
        // Cette méthode crée un nouveau Bulletin aléatoirement, avec un titre original, et persiste ce Bulletin vers la base de données. 
        // (Voir la méthode __construct de l'Entity Bulletin.php avec ses valeurs par défaut).

        // Nous récupérons l'Entity Manager :
        $entityManager = $doctrine->getManager();

        // On crée un nouveau bulletin :
        $bulletin = new Bulletin();

        // On persiste le bulletin :
        $entityManager->persist($bulletin);
        $entityManager->flush();

        // Comme le nom le sous-entend, redirectToRoute est une méthode de Controller renvoyant une Response qui nous redirige vers une autre méthode de Controller indiquée par le nom de sa route (ici, 'app_index'))
        return $this->redirectToRoute('app_index');
    }

    // ----------------------------------------------------------------------------------------------------------

    #[Route('/fake', name: 'bulletin_fake')] // Annotation de la méthode
    public function fakeBulletin(): Response // Déclaration de la méthode (avec son statut, son nom et ses paramètres ou arguments) 
    {
        // Cette page d'accueil affiche les différents bulletins enregistrés dans notre application web :
        $bulletins = []; // tableau vide de bulletins
        for ($i = 0; $i < rand(5, 15); $i++) {
            // Création d'un bulletin à ajouter :
            $bulletin = [
                "title" => "Bulletin #" . rand(1000, 9999),
                "category" => "Général",
                "content" => "Lorem ipsum etc.",
                "date" => (new \DateTime("now")),
            ];
            array_push($bulletins, $bulletin); // array_push nous permet d'ajouter notre $bulletin au tableau $bulletins
        }

        // Retour de la méthode :
        return $this->render('index/index.html.twig', [
            'bulletins' => $bulletins,
        ]);
    }

    // ----------------------------------------------------------------------------------------------------------

    #[Route('/square/{squareValue}', name: 'index_square')]
    public function displaySquare(string $squareValue = ''): Response
    {
        // $selectedColor prend la valeur de $squareValue.
        // strtolower change une chaîne de caractères en minuscules, afin d'éviter tout problème de casse.
        // Du fait des nombreuses conditions, nous allons utiliser une structure switch au lieu de if :
        switch (strtolower($squareValue)) {
            case "rouge":
                $selectedColor = "red";
                break; // Ne pas oublier de sortir de la structure switch
            case "vert":
                $selectedColor = "green";
                break;
            case "bleu":
                $selectedColor = "blue";
                break;
            case "jaune":
                $selectedColor = "yellow";
                break;
            case "":
                $selectedColor = "gray";
                break;
                // Si aucune valeur proposée ne correspond à $squareValue, $selectedColor prendra la valeur "black" :
            default:
                $selectedColor = "black";
        }

        return new Response('<div style="height:500px; width:500px; background-color:' . $selectedColor . ';"></div>');
    }
}
