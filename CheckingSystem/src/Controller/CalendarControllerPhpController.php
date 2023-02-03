<?php

namespace App\Controller;

use App\Entity\Users;
use App\Entity\Subject;
use App\Entity\Schedule;
use App\Entity\Student;
use App\Form\ScheduleFormType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Twig\Environment;
use Knp\Snappy\Pdf;

#[Route('/calendar', name: 'app_calendar_')]
class CalendarControllerPhpController extends AbstractController
{
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    #[Route('/', name: 'index')] // get all calendar
    public function index(): Response
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

        $schedules = $this->doctrine->getRepository(Schedule::class)->findAll();
        return $this->render('calendar/index.html.twig', [
            'schedules' => $schedules
        ]);
    }

    #[Route('/getOne/{id}', name: 'show')] // get one calendar
    public function show($id): Response
    {
        if (!$this->getUser()) { // connection check
            return $this->redirectToRoute('app_login');
        }

        $schedule = $this->doctrine->getRepository(Schedule::class)->find($id);
        $subjects = $this->doctrine->getRepository(Subject::class)->findBy(['schedule_id' => $schedule]);
        $all_subjects = [];
        foreach ($subjects as $subject)
        {
            $all_subjects[] = [
                'display' => null,
                'url' => $this->generateUrl('app_subject_show', ['id' => $subject->getId()]),
                'title' => $subject->getSubjectName() . ' - Teacher : ' . $subject->getTeacherName(),
                'start' => $subject->getStartAt()->format('Y-m-d H:i'),
                'end' => $subject->getEndAt()->format('Y-m-d H:i:s'), 
                'backgroundColor' => $subject->getBackgroundColor(),
                'borderColor' => $subject->getBorderColor(),
                'textColor' => $subject->getTextColor(),
                'allDay' => $subject->IsAllDay(),
            ];    
        }
        $data = json_encode($all_subjects);
        // get student from schedule 
        $students = $this->doctrine->getRepository(Student::class)->findBy(['scheduleId' => $schedule]);
        return $this->render('calendar/show.html.twig', compact('schedule', 'data', 'students'));
    }
    #[Route('/new', name: 'new')] // create new calendar
    public function new(Request $request,EntityManagerInterface $entityManager) : Response
    {
        if (!$this->getUser()) { // connection check
            return $this->redirectToRoute('app_login');
        }
        $schedule = new Schedule();
        $form = $this->createForm(ScheduleFormType::class, $schedule);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->doctrine->getManager();
            $em->persist($schedule);
            $em->flush();
            return $this->redirectToRoute('app_calendar_index');
        }
        
            return $this->render('calendar/new.html.twig', [
                'schedules' => $schedule,
                'ScheduleForm' => $form->createView(),
            ]);
    }
    #[Route('/edit/{id}', name: 'edit')] // edit calendar
    public function edit(Request $request, Schedule $schedule, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser()) { // connection check
            return $this->redirectToRoute('app_login');
        }
        $form = $this->createForm(ScheduleFormType::class, $schedule);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_calendar_index');
        }
        return $this->render('calendar/edit.html.twig', [
            'schedules' => $schedule,
            'ScheduleForm' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'delete')] // delete calendar
    public function delete(Schedule $schedule, EntityManagerInterface $entityManager): Response
    {
        // we cant display user after deleted schedule oupsi
        if (!$this->getUser()) { // connection check
            return $this->redirectToRoute('app_login');
        }
        // if we try to delete default schedule we cant (in this example default is 0 but we can change it)
        if ($schedule->getId() == 0) {
            return $this->redirectToRoute('app_calendar_index');
        }

        $students = $this->doctrine->getRepository(Student::class)->findBy(['scheduleId' => $schedule]);
        $schedule0 = $this->doctrine->getRepository(Schedule::class)->find(0); // schedule 0 is the default schedule 'not defined' I created it in the database
        foreach ($students as $student) {
            $student->setScheduleId($schedule0); // we change the schedule of the student to the default schedule (weird but it works)
            $entityManager->persist($student);
        }
        $subjects = $this->doctrine->getRepository(Subject::class)->findBy(['schedule_id' => $schedule]);
        foreach ($subjects as $subject)
        {
            $entityManager->remove($subject);
        }
        $entityManager->remove($schedule);
        $entityManager->flush();
        return $this->redirectToRoute('app_calendar_index');
    }

    // route for {{ path('sendCalendar', {'pdf': pdf}) }} in show.html.twig
    #[Route('/sendCalendar/{id}', name: 'sendCalendar')] // send calendar to all the students
    public function sendCalendar($id, MailerInterface $mailer, \Knp\Snappy\Pdf $knpSnappyPdf, Environment $twig) : Response
    {
        if (!$this->getUser()) { // connection check
            return $this->redirectToRoute('app_login');
        }
        $schedule = $this->doctrine->getRepository(Schedule::class)->find($id);
        $students = $this->doctrine->getRepository(Student::class)->findBy(['scheduleId' => $schedule]);
        $subjects = $this->doctrine->getRepository(Subject::class)->findBy(['schedule_id' => $schedule]);
        $all_subjects = [];
        foreach ($subjects as $subject)
        {
            $all_subjects[] = [
                'display' => null,
                'url' => $this->generateUrl('app_subject_show', ['id' => $subject->getId()]),
                'title' => $subject->getSubjectName() . ' - Teacher : ' . $subject->getTeacherName(),
                'start' => $subject->getStartAt()->format('Y-m-d H:i'),
                'end' => $subject->getEndAt()->format('Y-m-d H:i:s'), 
                'backgroundColor' => $subject->getBackgroundColor(),
                'borderColor' => $subject->getBorderColor(),
                'textColor' => $subject->getTextColor(),
                'allDay' => $subject->IsAllDay(),
            ];    
        }
        $data = json_encode($all_subjects);
        $pdf = $twig->render('email/showCalendarPdf.html.twig', compact('schedule', 'data'));
        $myPdf = $knpSnappyPdf->getOutputFromHtml($pdf);
        foreach ($students as $student)
        {
            $message = (new TemplatedEmail())
                ->from('checkingsystem2023@gmail.com')
                ->to($student->getEmail())
                ->subject('Your schedule')
                ->htmlTemplate('email/templatePDF.html.twig')
                ->context([
                    'student' => $student,
                    'schedule' => $schedule,
                    'data' => $data,
                ])
                ->attach($myPdf, 'calendar.pdf');
            $mailer->send($message);
        }
        return $this->redirectToRoute('app_calendar_show', ['id' => $id]);
        // le calendrier s'affiche bien dans le navigateur mais pas dans le pdf (regarder le commentaire en dessous)
        // return $this->render('email/showCalendarPdf.html.twig', compact('schedule', 'data')); 
        // les données sont bien envoyées dans le pdf mais le calendrier ne s'affiche pas entièrement
    }

    #[Route('/sendToOneStudent/{id}', name: 'sendToOneStudent')] // send calendar to one student
    public function sendToOneStudent($id, MailerInterface $mailer, \Knp\Snappy\Pdf $knpSnappyPdf, Environment $twig) : Response
    {
        if (!$this->getUser()) { // connection check
            return $this->redirectToRoute('app_login');
        }
        // if precense pass to 1  we send the calendar to the student
        $student = $this->doctrine->getRepository(Student::class)->find($id);
            $schedule = $student->getScheduleId();
            $subjects = $this->doctrine->getRepository(Subject::class)->findBy(['schedule_id' => $schedule]);
            $all_subjects = [];
            foreach ($subjects as $subject)
            {
                $all_subjects[] = [
                    'display' => null,
                    'url' => $this->generateUrl('app_subject_show', ['id' => $subject->getId()]),
                    'title' => $subject->getSubjectName() . ' - Teacher : ' . $subject->getTeacherName(),
                    'start' => $subject->getStartAt()->format('Y-m-d H:i'),
                    'end' => $subject->getEndAt()->format('Y-m-d H:i:s'), 
                    'backgroundColor' => $subject->getBackgroundColor(),
                    'borderColor' => $subject->getBorderColor(),
                    'textColor' => $subject->getTextColor(),
                    'allDay' => $subject->IsAllDay(),
                ];    
            }
            $data = json_encode($all_subjects);
            $pdf = $twig->render('email/showCalendarPdf.html.twig', compact('schedule', 'data'));
            $myPdf = $knpSnappyPdf->getOutputFromHtml($pdf);
            $message = (new TemplatedEmail())
                ->from('checkingsystem@gmail.com')
                ->to($student->getEmail())
                ->subject('Your schedule')
                ->htmlTemplate('email/templatePDF.html.twig')
                ->context([
                    'student' => $student,
                    'schedule' => $schedule,
                    'data' => $data,
                ])
                ->attach($myPdf, 'calendar.pdf');
            $mailer->send($message);
        return $this->redirectToRoute('app_student_presence', ['id' => $id]);
                }
}
