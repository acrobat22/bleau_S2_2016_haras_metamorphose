<?php
namespace PlateFormeBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
class DefaultController extends Controller
{
    public function indexAction()
    {
        $limit = 5;

        $em = $this->getDoctrine()->getManager();

        $agenda = $em->getRepository('CalendarBundle:Agenda')->findBy( array('slider' => 1) );
        $articles = $em->getRepository('HarasBundle:Article')->findLatestArticles($limit);

        return $this->render('@PlateForme/homepage_plateforme.html.twig', array(
            'agenda' => $agenda,
            'articles' => $articles
        ));
    }
    public function adminAction()
    {
        $limit = 5;

        $em = $this->getDoctrine()->getManager();

        $agenda = $em->getRepository('CalendarBundle:Agenda')->findBy( array('slider' => 1) );
        $articles = $em->getRepository('HarasBundle:Article')->findLatestArticles($limit);

        $user = $this->get('security.token_storage')->getToken()->getUser();
        return $this->render('@PlateForme/homepage_plateforme.html.twig', array(
            'user' => $user,
            'agenda' => $agenda,
            'articles' => $articles
        ));
    }
    public function moderateurAction()
    {
        $limit = 5;

        $em = $this->getDoctrine()->getManager();

        $agenda = $em->getRepository('CalendarBundle:Agenda')->findBy( array('slider' => 1) );
        $articles = $em->getRepository('HarasBundle:Article')->findLatestArticles($limit);

        $user = $this->get('security.token_storage')->getToken()->getUser();
        return $this->render('@PlateForme/homepage_plateforme.html.twig', array(
            'user' => $user,
            'agenda' => $agenda,
            'articles' => $articles
        ));
    }
    public function philosophieAction()
    {

        $em = $this->getDoctrine()->getManager();

        $philosophies = $em->getRepository('PlateFormeBundle:Philosophie')->findAll();

        return $this->render('@PlateForme/philosophie/index.html.twig', array(
            'philosophies' => $philosophies
        ));
    }
}
