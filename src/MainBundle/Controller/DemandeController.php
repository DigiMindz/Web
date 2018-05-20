<?php

namespace MainBundle\Controller;

use MainBundle\Entity\DemandeAjout;
use MainBundle\Form\DemandeAjoutType;
use MainBundle\Form\ModifierDemandeType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use MainBundle\Entity\User;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class DemandeController extends Controller
{
    /**
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function AjoutDemandeAction(Request $request)
    {
        $demande=new DemandeAjout();
        $form=$this->createForm(DemandeAjoutType::class,$demande);
        $form->handleRequest($request);
        if ($form->isValid())
        {
            /** @var UploadedFile $image */
            $image = $demande->getImagePrincipale();
            $imageName = $this->generateUniqueFileName().'.'.$image->guessExtension();
            $image->move($this->getParameter('brochures_directory'), $imageName);
            $demande->setImagePrincipale($imageName);
            $demande->setIdUser($this->getUser());

            $em=$this->getDoctrine()->getManager();
            $em->persist($demande);
            $em->flush();
            return $this->redirectToRoute('main_bundle_afficher_demande');
        }

        return $this->render('MainBundle:DemandeAjout:DemandeAjout.html.twig',
            array('demande'=>$form->createView()));
    }

    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }

    public function AfficherDemandeAction()
    {
        $user = $this->getUser();
        $em=$this->getDoctrine()->getManager();
        $demande=$em->getRepository("MainBundle:DemandeAjout")->findBy(array(
            'id_user'=>$user
        ));
        return $this->render('MainBundle:DemandeAjout:ListeDemandes.html.twig',
            array('demandes'=>$demande));
    }

    public function AnnulerDemandeAction($id)
    {
        $em=$this->getDoctrine()->getManager();
        $demande=$em->getRepository("MainBundle:DemandeAjout")->find($id);
        $em->remove($demande);
        $em->flush();
        return $this->redirectToRoute('main_bundle_afficher_demande');
    }

    public function ModifierDemandeAction(Request $request,$id)
    {
        $demande=new DemandeAjout();
        $em=$this->getDoctrine()->getManager();
        $demande=$em->getRepository("MainBundle:DemandeAjout")->find($id);

        $demande->setImagePrincipale
        (new File($this->getParameter('brochures_directory').'/'.$demande->getImagePrincipale()));

        $Form= $this->createForm(ModifierDemandeType::class, $demande);
        $Form->handleRequest($request);

        if ($Form->isValid() && $Form->isSubmitted())
        {
            /** @var UploadedFile $image */
            $image = $demande->getImagePrincipale();
            $imageName = $this->generateUniqueFileName().'.'.$image->guessExtension();
            $image->move($this->getParameter('brochures_directory'), $imageName);
            $demande->setImagePrincipale($imageName);

            $em=$this->getDoctrine()->getManager();
            $em->persist($demande);
            $em->flush();
            return $this->redirectToRoute('main_bundle_afficher_demande');
        }
        return $this->render("MainBundle:DemandeAjout:Modifier.html.twig",
            array('modif'=>$Form->createView()));

    }
    public function AfficherAdminAction()
    {

        $em=$this->getDoctrine()->getManager();
        $demande=$em->getRepository("MainBundle:DemandeAjout")->findAll();
        return $this->render('MainBundle:Admin:Afficher_demandes.html.twig',
            array('demandes'=>$demande));
    }
    public function RefuserAdminAction($id)
    {

        $em=$this->getDoctrine()->getManager();
        $demande=$em->getRepository("MainBundle:DemandeAjout")->find($id);
        $em->remove($demande);
        $em->flush();
        return $this->redirectToRoute('all_demandes');
    }


    public function AjoutWebServiceAction(Request $request)
    {
        $Demande = new DemandeAjout();
        $em = $this->getDoctrine()->getManager();
        $User = $em->getRepository("MainBundle:User")->find($request->get('user'));
        $Demande->setIdUser($User);
        $Demande->setNom($request->get('nom'));
        $Demande->setType($request->get('type'));
        $Demande->setAdresse($request->get('adresse'));
        $Demande->setDescription($request->get('description'));
        $Demande->setHoraireOuverture($request->get('horaireouverture'));
        $Demande->setHoraireFermeture($request->get('horairefermeture'));
        $Demande->setNumTel($request->get('numero'));
        $Demande->setImagePrincipale($request->get('image'));
        $Demande->setURL($request->get('url'));
        $Demande->setBudgetmoyen($request->get('budgetmoyen'));
        if ($Demande->getType() == "Restaurants/Cafés")
        {
            $Demande->setTypeResto($request->get('type1'));
        }
        if ($Demande->getType() == "Boutiques")
        {
            $Demande->setTypeShops($request->get('type1'));
        }
        if ($Demande->getType() == "Autres Etablissements")
        {
            $Demande->setTypeLoisirs($request->get('type1'));
        }
        if ($Demande->getType() == "Hotels")
        {
            $Demande->setNbrStars($request->get('type1'));
        }
        $em->persist($Demande);
        $em->flush();
        $e = new ObjectNormalizer();
        $e->setCircularReferenceHandler(function ($object)
        {
            return $object;
        });
        $serializer = new Serializer([$e]);
        $formatted = $serializer->normalize($Demande);
        return new JsonResponse($formatted);
    }
}