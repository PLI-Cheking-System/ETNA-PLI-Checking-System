<?php

namespace App\Controller;

use App\Entity\Users;
use App\Entity\Subject;
use App\Entity\Schedule;
use App\Form\SubjectType;
use App\Entity\Student;
use App\Repository\SubjectRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile_index')] // profile page
    public function index(): Response
    { 
        if (!$this->getUser()) { // connection check
            return $this->redirectToRoute('app_login');
        }
        return $this->render('profile/profile.html.twig', [
            'controller_name' => 'ProfileController',
        ]);
    }

    // route home page 
    #[Route('/home', name: 'app_home')]
    public function home(SubjectRepository $subjectRepository,ManagerRegistry $doctrine): Response
    {
        if (!$this->getUser()) { // connection check
            return $this->redirectToRoute('app_login');
        }
        $this->doctrine = $doctrine;
    
          // function to send mail to the student who is present
          $students = $this->doctrine->getRepository(Student::class)->findAll();
          foreach($students as $student){
              if ($student->getPresence() == 1)
              {
                  $student->setPresence(2);
                  $this->doctrine->getManager()->flush();
                  $this->addFlash('success', 'The student '.$student->getFirstName().' '.$student->getLastName().' is present');
                  return $this->redirectToRoute('app_calendar_sendToOneStudent', ['id' => $student->getId()]);
              }
          } // end of the function to send mail to the student who is present
        
            $mysubjects = $subjectRepository->findBy(['teacherName' => $this->getUser()->getFirstName() . " " . $this->getUser()->getLastName()]);

        return $this->render('main/home.html.twig', [
            'my_subjects' => $mysubjects,
            'subjects' => $subjectRepository->findAll(),
        ]);
    }

}
