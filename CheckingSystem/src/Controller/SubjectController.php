<?php

namespace App\Controller;

use App\Entity\Users;
use App\Entity\Subject;
use App\Entity\Schedule;
use App\Form\SubjectType;
use App\Repository\SubjectRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('/subject')]
class SubjectController extends AbstractController
{
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    #[Route('/', name: 'app_subject_index', methods: ['GET'])] // get my subjects or all subjects if admin
    public function index(SubjectRepository $subjectRepository): Response
    {
        if (!$this->getUser()) { // connection check
            return $this->redirectToRoute('app_login');
        }
        
            $mysubjects = $subjectRepository->findBy(['teacherName' => $this->getUser()->getFirstName() . " " . $this->getUser()->getLastName()]);
        
       
        return $this->render('subject/index.html.twig', [
            'my_subjects' => $mysubjects,
            'subjects' => $subjectRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_subject_new', methods: ['GET', 'POST'])] // create new subject
    public function new(Request $request, SubjectRepository $subjectRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        $subject = new Subject();
        $user = $this->getUser();
        $form = $this->createForm(SubjectType::class, $subject);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $teacherName = $user->getFirstName()." ".$user->getLastName();
            if($subject->getTeacherName() == null){ // if teacher name is not set, set it to current user name (fonctionnality for admin)
                $subject->setTeacherName($teacherName);
            }
            $subjectRepository->add($subject, true);
            $scheduleId = $subject->getScheduleId()->getId();
            return $this->redirectToRoute('app_calendar_show', [
                'id' => $scheduleId
            ]);
        }

        return $this->renderForm('subject/new.html.twig', [
            'subject' => $subject,
            'form' => $form
        ]);
    }

    #[Route('/{id}', name: 'app_subject_show', methods: ['GET'])] // show one subject onclick on calendar event 
    public function show(Subject $subject): Response
    {
        if (!$this->getUser()) { // connection check
            return $this->redirectToRoute('app_login');
        }
        $scheduleId = $subject->getScheduleId()->getId();
        $user = $this->getUser();
        $teacherName = $subject->getTeacherName();
        if ( $teacherName == $user->getFirstName()." ".$user->getLastName() ||  $user->isAdmin() == true)
        {
            $x = 1;
        }
        else
        {
            $x = 0;
        }
        return $this->render('subject/show.html.twig', [
            'subject' => $subject,
            'x' => $x,
            'scheduleId' => $scheduleId
        ]);
    }

    #[Route('/{id}/edit', name: 'app_subject_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Subject $subject, SubjectRepository $subjectRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        $scheduleId = $subject->getScheduleId()->getId();
        $form = $this->createForm(SubjectType::class, $subject);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $subjectRepository->add($subject, true);

            return $this->redirectToRoute('app_calendar_show', [
                'id' => $scheduleId
            ]);
        }

        return $this->renderForm('subject/edit.html.twig', [
            'subject' => $subject,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_subject_delete', methods: ['POST'])]
    public function delete(Request $request, Subject $subject, SubjectRepository $subjectRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        if ($this->isCsrfTokenValid('delete'.$subject->getId(), $request->request->get('_token'))) {
            $subjectRepository->remove($subject, true);
        }
        $scheduleId = $subject->getScheduleId()->getId();
        return $this->redirectToRoute('app_calendar_show', [
            'id' => $scheduleId
        ]);
    }

    #[Route('/{id}/move', name: 'app_subject_move', methods: ['PUT'])]
    public function move(Request $request, Subject $subject, SubjectRepository $subjectRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        $dataSubject = json_decode($request->getContent());
        if(
            isset($dataSubject->teacherName) && !empty($dataSubject->teacherName) &&
            isset($dataSubject->subjectName) && !empty($dataSubject->subjectName) &&
            isset($dataSubject->startAt) && !empty($dataSubject->startAt) &&
            isset($dataSubject->endAt) && !empty($dataSubject->endAt) &&
            isset($dataSubject->backgroundColor) && !empty($dataSubject->backgroundColor) &&
            isset($dataSubject->borderColor) && !empty($dataSubject->borderColor) &&
            isset($dataSubject->textColor) && !empty($dataSubject->textColor) &&
            isset($dataSubject->allDay) && !empty($dataSubject->allDay) &&
            isset($dataSubject->url) && !empty($dataSubject->url)
        ){
            $code = 200;
            if (!$dataSubject){
                $dataSubject = new Subject();
                $code = 201;
            }
            $teacherName = $user->getFirstName()." ".$user->getLastName();
            $dataSubject->setTeacherName($teacherName);
            $dataSubject->setSubjectName($dataSubject->subjectName);
            $dataSubject->setStartAt(new DateTime($dataSubject->startAt));
            if($dataSubject->allDay){
                $dataSubject->setEndAt(new DateTime($dataSubject->startAt));
            }else{
                $dataSubject->setEndAt(new DateTime($dataSubject->endAt));
            }
            $dataSubject->setBackgroundColor($dataSubject->backgroundColor);
            $dataSubject->setBorderColor($dataSubject->borderColor);
            $dataSubject->setTextColor($dataSubject->textColor);
            $dataSubject->setAllDay($dataSubject->allDay);
            $dataSubject->setUrl($dataSubject->url);

            $subjectRepository->add($dataSubject, true);
            $em = $this->doctrine->getManager();
            $em->persist($dataSubject);
            $em->flush();
            return new Response('Saved new subject with id '.$dataSubject->getId(), $code);
        }else {
            return new Response('Data incomplete', Response::HTTP_BAD_REQUEST);
        }

        return $this->json(['success' => true]);
    }
}
