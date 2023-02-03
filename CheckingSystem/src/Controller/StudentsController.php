<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Student;
use Doctrine\Persistence\ManagerRegistry;
use App\Form\SearchBarFormType;
use App\Form\StudentFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;


#[Route('/student', name: 'app_student_')]
class StudentsController extends AbstractController
{

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    #[Route('/', name: 'all')] // get all students
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {
        if (!$this->getUser()) { // connection check
            return $this->redirectToRoute('app_login');
        }
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
         $form = $this->createForm(SearchBarFormType::class);
         $form->handleRequest($request);
         // Form de la search bar qui Get tout les students si la search bar est vide et au premier chargement de la page.
         if ($form->isSubmitted() && $form->isValid()) {
             $data = $form->get('search_bar')->getData();
             if($data){
                 $students = $doctrine->getRepository(Student::class)->SearchByExampleFieldStudent($data);
             }else{
                 $students = $this->doctrine->getRepository(Student::class)->findAll();
             }
             return $this->render('student/students.html.twig', [
                         'students' => $students,
                         'searchbarForm' => $form->createView(),
                     ]);
         }
        return $this->render('student/students.html.twig', [
            'students' => $students,
            'searchbarForm' => $form->createView(),
        ]);
    }

    #[Route('/getOne/{id}', name: 'show')] // get one student
    public function show($id): Response
    {
        if (!$this->getUser()) { // connection check
            return $this->redirectToRoute('app_login');
        }
        $student = $this->doctrine->getRepository(Student::class)->find($id);
        return $this->render('student/show.html.twig', [
            'student' => $student,
        ]);
    }

    #[Route('/presence' , name: 'presence')] // all student presences
    public function presence(Request $request, ManagerRegistry $doctrine): Response
    {
        if (!$this->getUser()) { // connection check
            return $this->redirectToRoute('app_login');
        }
        $students = $this->doctrine->getRepository(Student::class)->findAll();
        // function to send mail to the student who is present
        foreach($students as $student){
            if ($student->getPresence() == 1)
            {
                $student->setPresence(2);
                $this->doctrine->getManager()->flush();
                $this->addFlash('success', 'The student '.$student->getFirstName().' '.$student->getLastName().' is present');
                return $this->redirectToRoute('app_calendar_sendToOneStudent', ['id' => $student->getId()]);
            }
        } // end of the function to send mail to the student who is present
        // set all students to absent at 00:00 every day, work only we are on this page (presence.html.twig)
        $now = new \DateTime();
        $hour = $now->format('H:i');
        if($hour == '00:00'){
            $students = $this->doctrine->getRepository(Student::class)->findAll();
            foreach($students as $student){
                $student->setPresence(false);
                $this->doctrine->getManager()->flush();
            }}
         // search bar start here
         $form = $this->createForm(SearchBarFormType::class);
         $form->handleRequest($request);
         // Form de la search bar qui Get tout les students si la search bar est vide et au premier chargement de la page.
         if ($form->isSubmitted() && $form->isValid()) {
             $data = $form->get('search_bar')->getData();
             if($data){
                 $students = $doctrine->getRepository(Student::class)->SearchByExampleFieldStudent($data);
             }else{
                 $students = $this->doctrine->getRepository(Student::class)->findAll();
             }
             return $this->render('student/presence.html.twig', [
                         'students' => $students,
                         'searchbarForm' => $form->createView(),
                     ]);
         }
        return $this->render('student/presence.html.twig', [
            'students' => $students,
            'searchbarForm' => $form->createView(),
        ]);
    }

    #[Route('/set_present/{id}', name: 'set_present')] // set one student to present
    public function setPresent($id, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser()) { // connection check
            return $this->redirectToRoute('app_login');
        }
        $student = $this->doctrine->getRepository(Student::class)->find($id);
        $student->setPresence(1);
        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($student);
        $entityManager->flush();
        return $this->redirectToRoute('app_student_presence');
    }

    #[Route('/set_absent/{id}', name: 'set_absent')] // set one student to absent
    public function setAbsent($id): Response
    {
        if (!$this->getUser()) { // connection check
            return $this->redirectToRoute('app_login');
        }
        $student = $this->doctrine->getRepository(Student::class)->find($id);
        $student->setPresence(0);
        $this->doctrine->getManager()->flush();
        return $this->redirectToRoute('app_student_presence');
    }

    #[Route('/set_all_absent', name: 'set_all_absent')] // set all students to absent
    public function setAllAbsent(): Response
    {
        if (!$this->getUser()) { // connection check
            return $this->redirectToRoute('app_login');
        }
        $students = $this->doctrine->getRepository(Student::class)->findAll();
        foreach($students as $student){
            $student->setPresence(false);
            $this->doctrine->getManager()->flush();
        }
        return $this->redirectToRoute('app_student_presence');
    }

    #[Route('/edit/{id}', name: 'edit')] // edit one student
    public function edit($id, Request $request,EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        if (!$this->getUser()) { // connection check
            return $this->redirectToRoute('app_login');
        }
        $student = $this->doctrine->getRepository(Student::class)->find($id);
        $oldImage = $student->getImage();
        $pathOldImage = $this->getParameter('image_directory').'/'. $oldImage;
        $form = $this->createForm(StudentFormType::class, $student);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
        // if image change delete old image
        if($form->get('image')->getData() != null){
            if(file_exists($pathOldImage)){
                unlink($pathOldImage);
            }
        }
            //if email exist in database and not the same as the current student
            $email = $form->get('email')->getData();
            $studentEmail = $this->doctrine->getRepository(Student::class)->findOneBy(['email' => $email]);
            if($studentEmail && $studentEmail->getId() != $student->getId()){
                $this->addFlash('danger', 'Email already exist');
                return $this->redirectToRoute('app_student_edit', ['id' => $student->getId()]);
            } // end email check
            $id = $student->getId();
            $firstname = $student->getFirstName();
            $lastname = $student->getLastName();
            $path = $form->get('image')->getData();
            if ($path) {
                $originalFilename = pathinfo($path->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$path->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $path->move(
                        $this->getParameter('image_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $student->setImage($newFilename);
            }


            $em = $this->doctrine->getManager();
            $em->persist($student);
            $em->flush();
            return $this->redirectToRoute('app_student_all');
        }
        
        return $this->render('student/edit.html.twig', [
            'student' => $student,
            'StudentForm' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'delete')] // delete one student
    public function delete($id): Response
    {
        if (!$this->getUser()) { // connection check
            return $this->redirectToRoute('app_login');
        }
        $student = $this->doctrine->getRepository(Student::class)->find($id);
        // delete image and QrCode on delete student
        $image = $student->getImage();
        $path = $this->getParameter('image_directory').'/'.$image;
        $qrCode = $student->getQrCode();
        $pathQrCode = "../public/assets/qrCode/".$qrCode;
        if (file_exists($path)) {
            unlink($path);
        }
        if (file_exists($pathQrCode)) {
            unlink($pathQrCode);
        }
        $em = $this->doctrine->getManager();
        $em->remove($student);
        $em->flush();
        return $this->redirectToRoute('app_student_all');
    }

    #[Route('/new', name: 'new')] // add new student
    public function new(Request $request,EntityManagerInterface $entityManager,SluggerInterface $slugger) : Response
    {
        if (!$this->getUser()) { // connection check
            return $this->redirectToRoute('app_login');
        }
        $student = new Student();
        $form = $this->createForm(StudentFormType::class, $student);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //if email exist in database
            $email = $student->getEmail();
            $emailExist = $this->doctrine->getRepository(Student::class)->findOneBy(['email' => $email]);
            if ($emailExist) {
                $this->addFlash('danger', 'Email exist');
                return $this->render('student/new.html.twig', [
                    'StudentForm' => $form->createView(),
                ]);
            }
            $id = $student->getId();
            $firstname = $student->getFirstName();
            $lastname = $student->getLastName();
            $path = $form->get('image')->getData();
            if ($path) {
                $originalFilename = pathinfo($path->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$path->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $path->move(
                        $this->getParameter('image_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $student->setImage($newFilename);
            }
            $student->setMatricule(rand(0,1000));
            $matricule = $student->getMatricule();
            $matriculeExist = $this->doctrine->getRepository(Student::class)->findOneBy(['matricule' => $matricule]);
            if ($matriculeExist) {
                $student->setMatricule(rand(0,1000));
            }
            $matricule = $student->getMatricule();
            shell_exec("python3 ./QrCode.py $matricule $firstname $lastname"); // generate QR code
            $student->setQrCode("$matricule"."_".$firstname."_".$lastname."QR.png"); // set QR code name
            $em = $this->doctrine->getManager();
            $em->persist($student);
            $em->flush();
            return $this->redirectToRoute('app_student_all');
        }
        
            return $this->render('student/new.html.twig', [
                'students' => $student,
                'StudentForm' => $form->createView(),
            ]);
    }

    #[Route('/qrCode/{id}', name: 'QrCode')] // edit QR code for student page 
    public function QrCode_edit( $id,Request $request, EntityManagerInterface $entityManager): Response
    {       
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
             $student = $this->doctrine->getRepository(Student::class)->find($id);
            $firstname = $student->getFirstName();
            $lastname = $student->getLastName();
            $matricule = $student->getMatricule();
            unlink("../public/assets/qrCode/"."$matricule"."_".$firstname."_".$lastname."QR.png");
            $student->setMatricule(rand(0,1000));
            $matricule = $student->getMatricule();      
            $matriculeExist = $this->doctrine->getRepository(Student::class)->findOneBy(['matricule' => $matricule]);
            if ($matriculeExist) {
                $student->setMatricule(rand(0,1000));
            }
            $matricule = $student->getMatricule();
            shell_exec("python3 ./QrCode.py $matricule $firstname $lastname");
            $student->setQrCode("$matricule"."_".$firstname."_".$lastname."QR.png");
            $em = $this->doctrine->getManager();
            $entityManager->persist($student);
            $entityManager->flush();
            return $this->redirectToRoute('app_student_all');
    }
}