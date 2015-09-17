<?php
// src\OC\PlatformBundle\Controller\AdvertController.php

namespace OC\PlatformBundle\Controller;

use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\Application;
use OC\PlatformBundle\Entity\Image;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OC\PlatformBundle\Form\AdvertType;
use OC\PlatformBundle\Form\AdvertEditType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class AdvertController extends Controller
{
    public function indexAction($page)
    {
     $nbPerPage = 4;

     if ($page < 1) {
       throw $this->createNotFoundException("La page ".$page." n'existe pas.");
     }

     // Pour récupérer la liste de toutes les annonces : on utilise findAll()
     $listAdverts = $this->getDoctrine()
       ->getManager()
       ->getRepository('OCPlatformBundle:Advert')
       ->getAdverts($page, $nbPerPage)
     ;

     $nbPages = ceil(count($listAdverts)/$nbPerPage);

    // Si la page n'existe pas, on retourne une 404
    if ($page > $nbPages) {
      throw $this->createNotFoundException("La page ".$page." n'existe pas.");
    }

    // On donne toutes les informations nécessaires à la vue
    return $this->render('OCPlatformBundle:Advert:index.html.twig', array(
      'listAdverts' => $listAdverts,
      'nbPages'     => $nbPages,
      'page'        => $page,
      'nbPerPage'   => $nbPerPage
    ));
   }

    public function editAction($id, Request $request)
    {
         $advert = $this->getDoctrine()
         ->getManager()
         ->getRepository('OCPlatformBundle:Advert')
         ->find($id)
        ;

         $advert->updateDate();
         $udDated = $advert->getUpdatedAt();

         if (null === $advert) {
           throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
         }else{
          $form = $this->get('form.factory')->create(new AdvertEditType, $advert);
         }

          if ($form->handleRequest($request)->isValid()){
           $em = $this->getDoctrine()->getManager();
           $em->persist($advert);
           $em->flush();

           $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');

           return $this->redirect($this->generateUrl('oc_platform_view', array('id' => $advert->getId())));
          }

          return $this->render('OCPlatformBundle:Advert:edit.html.twig', array(
               'form' => $form->createView(),
               'advert' => $advert,
               'udDated' => $udDated
          ));

         // La méthode findAll retourne toutes les catégories de la base de données
         // $listCategories = $advert->getRepository('OCPlatformBundle:Category')
         // ->findAll();



         // On boucle sur les catégories pour les lier à l'annonce
         // foreach ($listCategories as $category) {
         //   $advert->addCategory($category);
         // }


         // Pour persister le changement dans la relation, il faut persister l'entité propriétaire
         // Ici, Advert est le propriétaire, donc inutile de la persister car on l'a récupérée depuis Doctrine

         // Étape 2 : On déclenche l'enregistrement

    }

    public function viewAction($id, Request $request)
    {
         $em = $this->getDoctrine()->getManager();

         $advert = $em
            ->getRepository('OCPlatformBundle:Advert')
            ->find($id)
         ;

         // $advert est donc une instance de OC\PlatformBundle\Entity\Advert
         // ou null si l'id $id  n'existe pas, d'où ce if :
         if (null === $advert) {
           throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
         }

         $listApplications = $em
             ->getRepository('OCPlatformBundle:Application')
             ->findBy(array('advert' => $advert))
             ;

         // Le render ne change pas, on passait avant un tableau, maintenant un objet
         return $this->render('OCPlatformBundle:Advert:view.html.twig', array(
           'advert' => $advert,
           'listApplications' => $listApplications
         ));

    }


    public function deleteAction($id, Request $request)
    {
         $em = $this->getDoctrine()->getManager();


         // On récupère l'annonce $id
         $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);


         if (null === $advert) {
           throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
         }

         $listApplications = $em
             ->getRepository('OCPlatformBundle:Application')
             ->findBy(array('advert' => $advert))
             ;

         // On crée un formulaire vide, qui ne contiendra que le champ CSRF
        // Cela permet de protéger la suppression d'annonce contre cette faille
        $form = $this->createFormBuilder()->getForm();

        if ($form->handleRequest($request)->isValid()) {
          $em->remove($advert);

          $em->flush();

          $request->getSession()->getFlashBag()->add('info', "L'annonce a bien été supprimée.");

          return $this->redirect($this->generateUrl('oc_platform_home'));
        }

        // Si la requête est en GET, on affiche une page de confirmation avant de supprimer
        return $this->render('OCPlatformBundle:Advert:delete.html.twig', array(
          'advert' => $advert,
          'form'   => $form->createView()
        ));
         // // On boucle sur les catégories de l'annonce pour les supprimer
         // foreach ($advert->getCategories() as $category) {
         //   $advert->removeCategory($category);
         // }


         // Pour persister le changement dans la relation, il faut persister l'entité propriétaire

         // Ici, Advert est le propriétaire, donc inutile de la persister car on l'a récupérée depuis Doctrine


         // On déclenche la modification
         $em->flush();
         return $this->render('OCPlatformBundle:Advert:delete.html.twig', array(
           'advert' => $advert
         ));
    }

    public function addAction(Request $request)
    {
         // Création de l'entité
        $advert = new Advert();
        $form = $this->createForm(new AdvertType(), $advert);

        if ($form->handleRequest($request)->isValid()){

         $em = $this->getDoctrine()->getManager();

         $em->persist($advert);
         $em->flush();

         $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');

         return $this->redirect($this->generateUrl('oc_platform_view', array('id' => $advert->getId())));
        }

        return $this->render('OCPlatformBundle:Advert:add.html.twig', array(
             'form' => $form->createView(),
        ));
        // $advert->setTitle('Recherche développeur HTML/CSS');
        // $advert->setAuthor('flora.rene@gmail.com');
        // $advert->setContent("Nous recherchons un développeur Joomla débutant sur Lyon. Blabla…");
        //
        // // Création de l'entité Image
        // $image = new Image();
        // $image->setUrl('http://sdz-upload.s3.amazonaws.com/prod/upload/job-de-reve.jpg');
        // $image->setAlt('Job de rêve');
        //
        // // On lie l'image à l'annonce
        // $advert->setImage($image);
        //
        // // Création d'une première candidature
        // $application1 = new Application();
        // $application1->setAuthor('Marine');
        // $application1->setContent("J'ai toutes les qualités requises.");
        //
        // // Création d'une deuxième candidature par exemple
        // $application2 = new Application();
        // $application2->setAuthor('Pierre');
        // $application2->setContent("Je suis très motivé.");
        //
        // // On lie les candidatures à l'annonce
        // $application1->setAdvert($advert);
        // $application2->setAdvert($advert);
        //
        // // On peut ne pas définir ni la date ni la publication,
        // // car ces attributs sont définis automatiquement dans le constructeur
        // // On récupère l'EntityManager
        // $em = $this->getDoctrine()->getManager();
        // // Étape 1 : On « persiste » l'entité
        // $em->persist($advert);
        // $em->persist($application1);
        // $em->persist($application2);
        // // Étape 2 : On « flush » tout ce qui a été persisté avant
        // $em->flush();
        // // Reste de la méthode qu'on avait déjà écrit
        // if ($request->isMethod('POST')) {
        //   $request->getSession()
        //   ->getFlashBag()
        //   ->add('notice', 'Annonce bien enregistrée.');
        //   return $this->redirect($this->generateUrl('oc_platform_view', array('id' => $advert->getId())));
        // }

    }
    public function menuAction()
    {
        $listAdverts = $this->getDoctrine()
          ->getManager()
          ->getRepository('OCPlatformBundle:Advert')
          ->findBy(
             array(), // Critere
             array('date' => 'desc'), // Tri
             3,       // Limite
             0        // Offset
         );

        return $this->render('OCPlatformBundle:Advert:menu.html.twig', array(
          'listAdverts' => $listAdverts
        ));
    }

    public function listAction()
    {
     $listAdverts = $this
      ->getDoctrine()
      ->getManager()
      ->getRepository('OCPlatformBundle:Advert')
      ->getAdvertWithApplications()
     ;

     foreach($listAdverts as $advert){
      $advert->getApplications();
     }
    }

   public function listitAction()
   {
    $listAdverts = $this
     ->getDoctrine()
     ->getManager()
     ->getRepository('OCPlatformBundle:Advert')
     ->getAdvertWithCategories(array('Graphisme', 'Intégration'))
    ;

    foreach($listAdverts as $advert){
     $advert->getApplications();
    };

    return $this->render('OCPlatformBundle:Advert:listit.html.twig', array(
      'listAdverts' => $listAdverts
    ));
   }

}
?>
