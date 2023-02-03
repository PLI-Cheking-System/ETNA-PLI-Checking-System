<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Student;
use App\Entity\Users;
use App\Form\SearchBarFormType;
use App\Form\RegistrationFormType;
use App\Form\ResetPasswordFormType;
use App\Form\EmailResetPasswordFormType;
use App\Security\UsersAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UsersController extends AbstractController
{
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    #[Route('/users', name: 'app_users')] // show all users
    public function users(Request $request,ManagerRegistry $doctrine): Response
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
        $users = $this->doctrine->getRepository(Users::class)->findAll();
        // search bar start here
        $form = $this->createForm(SearchBarFormType::class);
        $form->handleRequest($request);
        // Form de la search bar qui Get tout les users si la search bar est vide et au premier chargement de la page.
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->get('search_bar')->getData();
            if($data){
                $users = $doctrine->getRepository(Users::class)->SearchByExampleField($data);
            }else{
                $users = $this->doctrine->getRepository(Users::class)->findAll();
            }
            return $this->render('administration/users.html.twig', [
                        'users' => $users,
                        'searchbarForm' => $form->createView(),
                    ]);
        }
        return $this->render('administration/users.html.twig', [
            'users' => $users,
            'searchbarForm' => $form->createView(),
        ]);
    }

    #[Route('/users/{id}', name: 'app_users_id')] // show one user
    public function users_id(Request $request,ManagerRegistry $doctrine, $id): Response
    {
        if (!$this->getUser()) { // connection check
            return $this->redirectToRoute('app_login');
        }
        $user = $this->doctrine->getRepository(Users::class)->find($id);
        return $this->render('administration/user_id.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/users/edit/{id}', name: 'app_users_edit')] // edit user
    public function edit($id, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {

        if (!$this->getUser()) { // connection check
            return $this->redirectToRoute('app_login');
        }

        if ($this->getUser()->getId() != $id & $this->getUser()->isAdmin() == false) { // If user is not admin and tries to edit other user
            return $this->redirectToRoute('app_users');
        }

        $user = $this->doctrine->getRepository(Users::class)->find($id);
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
        if ($form->get('plainPassword')->getData() != null) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
        }
        else {
            $user->setPassword($user->getPassword());
        }
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute('app_users');
        }
        return $this->render('administration/editUser.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
    
    #[Route('/users/delete/{id}', name: 'app_users_delete')] // delete user
    public function delete($id, EntityManagerInterface $entityManager): Response
    {

        if (!$this->getUser()) { // connection check
            return $this->redirectToRoute('app_login');
        }
        if ($this->getUser()->getId() == $id || $this->getUser()->isAdmin() == 0) { // check if the user is trying to delete himself or if he is not an admin
            return $this->redirectToRoute('app_users');
        }

        $user = $this->doctrine->getRepository(Users::class)->find($id);
        $entityManager->remove($user);
        $entityManager->flush();
        return $this->redirectToRoute('app_users');
    }

}
