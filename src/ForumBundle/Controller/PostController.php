<?php

namespace ForumBundle\Controller;

use ForumBundle\Entity\Post;
use ForumBundle\Entity\CategoriePlateforme;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\User;

/**
 * Post controller.
 *
 */
class PostController extends Controller
{
    /**
     * Lists all post entities.
     *
     */
    public function indexAction()
    {
        // Connexion à la BdD
        $em = $this->getDoctrine()->getManager();
        // Ramene le Fil de discussion parent et actif
        $postParents = $em->getRepository('ForumBundle:Post')->findBy(array('actif' => 1, 'parent' => null), array('dateCreate' => 'DESC'));
        // Compte le nombre de post enfant par fil de discussion
        $nbPostEnfants = array();
        foreach ($postParents as $postParent) {
            $nbPostEnfants[$postParent->getId()] = count($em->getRepository('ForumBundle:Post')->findBy(array('parent' => $postParent->getId(), 'actif' => 1)));
        }

        // Ramene les catégories à partir du HarasBundle
        $categories = $em->getRepository('HarasBundle:Category')->findAll(array('id' => 'ASC'));

        $categoriesName = $em->getRepository('ForumBundle:CategoriePlateforme')->getNameCateg();
        $categoriesPlateforme = $em->getRepository('ForumBundle:CategoriePlateforme')->findBy(array('parent' => null));

        foreach ($categoriesPlateforme as $categoriePlateforme)
        {
            foreach ($categories as $key => $category){
                if ($category->getId() == $categoriePlateforme->getId()){
                    // Récupération du nom des catégories du harasBundle
                    foreach ($categoriesName as $categName){
                        if (substr($categName['name'], 0, 8) == $category->getName()){
                            $categoriePlateforme->setNom($categName['text_fr']);
                            $em->persist($categoriePlateforme);
                            unset($categories[$key]);
                        }
                    }

                }
            }
        }

        // Ramene sous catégorie
        // SELECT * FROM `categorie_plateforme` WHERE `parent_id` is NOT null and actif = 1
        $repository = $em->getRepository('ForumBundle:CategoriePlateforme');
        $sousCategories = $repository->getSousCategorie();

        // -----------------------------------------------------------------------------------------------------
        // Test si la base de données est suffisament remplie
        // si pas de sous-catégorie redirection vers création
        // nouvelle sous-catégorie
        // -----------------------------------------------------------------------------------------------------
        // Cas 1 : Sous catégorie completement vide => redirection sur page index catégorie
        if (empty($sousCategories)) {
            $this->addFlash('notice', 'Il faut remplir au minimum 1 sous-catégorie par catégorie');
            return $this->redirectToRoute('categorieplateforme_index');
        }

        // Cas 2 : Pas de sous catégorie dans toutes les categories
        $nbSousCat = 0;
        foreach ($categories as $categorie) {
            foreach ($sousCategories as $sousCategorie) {
                if ($sousCategorie->getParent()->getId() == $categorie->getId()) {
                    $nbSousCat++;
                }
            }
            if ($nbSousCat < 1) {
                $this->addFlash('notice', 'Ajouter une sous-catégorie à ' . $categorie->getNom());
                return $this->redirectToRoute('categorieplateforme_newSousCat',
                    array('cat' => $sousCategorie->getParent()->getId()));
            }
        }

        // -----------------------------------------------------------------------------------------------------
        // Selection des derniers fils de discussion enregistré (1 par catégorie) Ici seulement 4 catégories
        // -----------------------------------------------------------------------------------------------------
        $lastPostByCats = array();
        $categs = array();
        foreach ($postParents as $postParent) {
            if (!in_array($postParent->getCategorie()->getParent(), $categs)) {
                $categs[] = $postParent->getCategorie()->getParent();
                $lastPostByCats[] = $postParent;
            }
        }

        $em->flush();

        return $this->render('@Forum/post/index.html.twig', array(
            'postParents' => $postParents,
            'nbPostEnfants' => $nbPostEnfants,
            'categories' => $categoriesPlateforme,
            'sousCategories' => $sousCategories,
            'lastPostCats' => $lastPostByCats,
        ));
    }


    /**
     * Creates a new post entity.
     *
     */
    public function newAction(Request $request, CategoriePlateforme $cat, Post $id)
    {
        $post = new Post();
        $form = $this->createForm('ForumBundle\Form\PostType', $post);
        $form->handleRequest($request);

        // Récupération titre du post parent
        // Connexion à la BdD
        $em = $this->getDoctrine()->getManager();
        // Ramene le Fil de discussion parent
        $postParents = $em->getRepository('ForumBundle:Post')->findOneBy(array('id' => $id));

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            // Enregistrement en BdD
            // Ajout de l'id Parent au post
            $post->setParent($id);

            // Ajout de la catégorie du parent au post
            $post->setCategorie($cat);

            // Ajout actif
            $post->setActif(true);

            // Ajout date de la saisie
            $post->setDateCreate(new \DateTime());

            // Ajout id user
            $post->setUser($this->getUser());

            // Ajout +1 sur nbPost sur User
            $count = $post->getUser()->getNbPost();
            $post->getUser()->setNbPost(++$count);

            $em->persist($post);
            $em->flush();

            $this->addFlash(
                'notice',
                'Votre message a été enregistré'
            );

            return $this->redirectToRoute('post_showAllPost', array('id' => $post->getParent()->getId()) );
        }

        return $this->render('@Forum/post/new.html.twig', array(
            'postParentTitre' => $postParents->getTitre(),
            'post' => $post,
            'form' => $form->createView(),
            'cat' => $request->get('cat'),
            'id' => $request->get('id'),
        ));
    }

        /**
     * Creates a new post entity.
     *
     */
    public function newParentAction(Request $request, CategoriePlateforme $cat)
    {
        $post = new Post();
        $form = $this->createForm('ForumBundle\Form\PostType', $post);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            // Enregistrement en BdD
            // Ajout de la catégorie au post
            $post->setCategorie($cat);
            // Ajout actif
            $post->setActif(true);

            // Ajout date de la saisie
            $post->setDateCreate(new \DateTime());

            // Ajout user
            $post->setUser($this->getUser());

            // Ajout +1 sur nbPost sur User
            $count = $post->getUser()->getNbPost();
            $post->getUser()->setNbPost(++$count);

            $em->persist($post);
            $em->flush();
            return $this->redirectToRoute('post_showAllPost', array('id' => $post->getId()) );

        }

        return $this->render('@Forum/post/newParent.html.twig', array(
            'post' => $post,
            'form' => $form->createView(),
            'cat' => $request->get('cat'),
        ));
    }

    /**
     * Finds and displays a post entity.
     *
     */
    public function showAction(Post $post)
    {
        $deleteForm = $this->createDeleteForm($post);

        return $this->render('@Forum/post/show.html.twig', array(
            'post' => $post,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Show last post pour homepage plateforme.
     *
     */
    public function showLastPostHomepageAction()
    {
        // Connexion à la BdD
        $em = $this->getDoctrine()->getManager();
        // Ramene le Fil de discussion parent et actif
        $posts = $em->getRepository('ForumBundle:Post')->findBy(array('actif' => 1), array('dateCreate' => 'DESC'));

        // -----------------------------------------------------------------------------------------------------
        // Selection des derniers fils de discussion enregistré (1 par catégorie) Ici seulement 4 catégories
        // -----------------------------------------------------------------------------------------------------
        $lastPostByCats = array();
        $categs = array();
        foreach ($posts as $post) {
            if (!in_array($post->getCategorie()->getParent(), $categs)) {
                $categs[] = $post->getCategorie()->getParent();
                $lastPostByCats[] = $post;
            }
        }

        return $this->render('@Forum/post/showLastPost.html.twig', array(
            'lastPostCats' => $lastPostByCats,
        ));
    }

    /**
     * Finds and displays a post entity.
     *
     */
    public function showAllPostAction(Request $request)
    {
        // Connexion à la BdD
        $em = $this->getDoctrine()->getManager();
        // Ramene le Fil de discussion parent et actif

        // Récupération du numéro du post fil de discussion
        $idPostFirst = $request->get('id');
        // Récupération des post en BdD
        // Post parent
        $postParents = $em->getRepository('ForumBundle:Post')->findBy(array( 'id' => $idPostFirst, 'actif'=> 1), array('dateCreate' => 'DESC'));
        // Post enfant + mise en place pagination
        $findPostEnfants = $em->getRepository('ForumBundle:Post')->findBy(array( 'parent' => $idPostFirst, 'actif'=> 1));
        $paginator  = $this->get('knp_paginator');
        $postEnfants = $paginator->paginate($findPostEnfants, $request->query->getInt('page', 1), 5);

        return $this->render('@Forum/post/showAllPost.html.twig', array(
            'postParents' => $postParents,
            'postEnfants' => $postEnfants,
        ));
    }


    /**
     * Finds and displays a post entity by user current.
     *
     */
    public function showMesMessagesAction(Request $request, User $user)
    {
        // Connexion à la BdD
        $em = $this->getDoctrine()->getManager();
        // Ramene le Fil de discussion parent et actif
//var_dump($request);
        if (isset($user)) {
        // Récupération des post en BdD
        $posts = $em->getRepository('ForumBundle:Post')->findBy(array('actif'=> 1, 'user' =>$user->getId()), array('dateCreate' => 'DESC'));
        }
        else {
            $posts = $em->getRepository('ForumBundle:Post')->findBy(array('actif'=> 1, 'user' =>$request->getUser()), array('dateCreate' => 'DESC'));
        }

        $tabIdParents = array();
        foreach ($posts as $post) {
            if ($post->getParent() == null) {
                $tabIdParents[] = $post->getId();
            }
        }

        return $this->render('@Forum/post/showMesMessages.html.twig', array(
            'posts' => $posts,
            'tabIdParents' => $tabIdParents,
        ));
    }

    /**
     * List user.
     *
     */
    public function showListUserAction()
    {
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('UserBundle:User')->findAll();

        return $this->render('@Forum/post/selectUser.html.twig', array(
            'users' => $users,
        ));
    }


    /**
     * Displays a form to edit an existing post entity.
     *
     */
    public function editAction(Request $request, Post $post)
    {
        $deleteForm = $this->createDeleteForm($post);
        $editForm = $this->createForm('ForumBundle\Form\PostType', $post);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            // Redirection sur le fil de discussion initial
            // pour post parent
            if($post->getParent() == null) {
                return $this->redirectToRoute('post_showAllPost', array('id' => $post->getId()));
            }
            // pour post enfant
            else {
            return $this->redirectToRoute('post_showAllPost', array('id' => $post->getParent()->getId()));
            }
        }

        // Direction pour post parent
        if($post->getParent() == null) {
            return $this->render('@Forum/post/edit.html.twig', array(
                'id' => $post->getId(),
                'post' => $post,
                'edit_form' => $editForm->createView(),
                'delete_form' => $deleteForm->createView()
            ));
        // Direction pour post enfant
        } else {
            return $this->render('@Forum/post/edit.html.twig', array(
                'post' => $post,
                'edit_form' => $editForm->createView(),
                'delete_form' => $deleteForm->createView()
            ));
        }
    }

    /**
     * Desactives a post entity.
     *
     */
    public function desactiveAction(Post $post) {
        // Si post Parent, désactive également les post enfant, actif = false
        if ($post->getParent() == null) {
            $em = $this->getDoctrine()->getManager();
            // Récupération des post enfant dans BdD
            $postEnfants = $em->getRepository('ForumBundle:Post')->findBy(array( 'parent' => $post->getId(), 'actif'=> 1));

            // Enregistrement en BdD
            // Enfant : actif = false
            foreach ($postEnfants as $postEnfant) {
                $postEnfant->setActif(false);
                $em->persist($postEnfant);
                $em->flush($postEnfant);
            }
            // parent : actif = false
            $post->setActif(false);
            $em->persist($post);
            $em->flush($post);

            $this->addFlash(
                'notice',
                'Discussion désactivée'
            );

            return $this->redirectToRoute('post_index');

        // Si post Enfant, desactive que le post selectionné
        } else {
            $em = $this->getDoctrine()->getManager();
            $post->setActif(false);
            $em->persist($post);
            $em->flush($post);

            $this->addFlash(
                'notice',
                'Commentaire réactivée'
            );

            return $this->redirectToRoute('post_showAllPost', array('id' => $post->getParent()->getId()) );
        }
    }

    /**
     * show desactives post.
     *
     */
    public function showInactivePostAction(Request $request) {
        // Connexion à la BdD
        $em = $this->getDoctrine()->getManager();

        // Récupération des post en BdD
        $posts = $em->getRepository('ForumBundle:Post')->findBy(array('actif'=> 0));

        // Mémorisation id post parent pour affichage des post isolés
        $tabIdParents = array();
        foreach ($posts as $post) {
            if ($post->getParent() == null) {
                $tabIdParents[] = $post->getId();
            }
        }

        return $this->render('@Forum/post/reactivePost.html.twig', array(
            'posts' => $posts,
            'tabIdParents' => $tabIdParents,
        ));
    }

    /**
     * Reactives a post entity.
     *
     */
    public function reactiveAction(Post $post) {
        // Si post Parent, réactive également les post enfant, actif = true
        if ($post->getParent() == null and $post->getActif() == false) {
            $em = $this->getDoctrine()->getManager();
            // Récupération des post enfant dans BdD
            $postEnfants = $em->getRepository('ForumBundle:Post')->findBy(array( 'parent' => $post->getId(), 'actif'=> 0));

            // Enregistrement en BdD
            // Enfant : actif = true
            foreach ($postEnfants as $postEnfant) {
                $postEnfant->setActif(true);
                $em->persist($postEnfant);
                $em->flush($postEnfant);
            }
            // Parent : actif = true
            $post->setActif(true);
            $em->persist($post);
            $em->flush($post);

            return $this->redirectToRoute('post_showAllPostDesactive');

        // Si post Enfant, réactive que le post selectionné
        }
        else {
            return $this->redirectToRoute('post_showAllPostDesactive');
        }
    }

    /**
     * Message vers Admin
     * si contenu message incorrect
     *
     */
    public function alerteMessageAction(Request $request, Post $post) {

        $postConcerneId = $post->getId();
        $postConcerneTitre = $post->getTitre();
        $postConcerneContenu = $post->getContenu();
        $postAuteurNom = $post->getUser()->getNom();
        $postAuteurEmail = $post->getUser()->getEmail();
        $postAuteurId = $post->getUser()->getId();
        $emailExpediteur = $this->getUser()->getEmail();
        $nomExpediteur = $this->getUser()->getNom();

        $message = \Swift_Message::newInstance()
                        ->setSubject('Message inapproprié')
                        ->setFrom(array($this->getParameter('mailer_user') => 'Le Haras de la métamorphose'))
                        ->setBcc($emailExpediteur)
                        ->setCc(array($this->getParameter('mailer_user') => 'Le Haras de la métamorphose'))
                        ->setBody(
                            $this->renderView(
                                '@Forum/post/alerteMail.html.twig',
                                array(
                                    'contenu' => $postConcerneContenu,
                                    'titre' => $postConcerneTitre,
                                    'id' => $postConcerneId,
                                    'auteurNom' => $postAuteurNom,
                                    'auteurEmail' => $postAuteurEmail,
                                    'auteurId' => $postAuteurId,
                                    'emailExpediteur' => $emailExpediteur,
                                    'nomExpediteur' => $nomExpediteur
                                )
                            ),
                            'text/html'
                        );

        $this->get('mailer')->send($message);

        if ($post->getParent() == null) {
            $this->addFlash('notice', "Votre alerte a été signalé à l'administrateur du site");
            return $this->redirectToRoute('post_showAllPost', array('id' => $post->getId()));
        } else {
            $this->addFlash('notice', "Votre alerte a été signalé à l'administrateur du site");
            return $this->redirectToRoute('post_showAllPost', array('id' => $post->getParent()->getId()));
        }

    }

    /**
     * Deletes a post entity.
     *
     */
    public function deleteAction(Request $request, Post $post)
    {
        $form = $this->createDeleteForm($post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($post);
            $em->flush($post);
        }

        return $this->redirectToRoute('post_index');
    }

    /**
     * Creates a form to delete a post entity.
     *
     * @param Post $post The post entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Post $post)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('post_delete', array('id' => $post->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
