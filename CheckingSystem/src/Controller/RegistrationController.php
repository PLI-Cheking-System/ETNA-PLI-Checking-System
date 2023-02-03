<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\RegistrationFormType;
use App\Form\ResetPasswordFormType;
use App\Form\EmailResetPasswordFormType;
use App\Security\UsersAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;

class RegistrationController extends AbstractController
{
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    #[Route('/register', name: 'app_register')] // registration page
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, UsersAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
    {
        $user = new Users();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_main');
        }

        return $this->render('administration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/login-as-user/{id}', name: 'app_users_login')] // login as user
    public function loginAsUser(Users $user, UserAuthenticatorInterface $authenticator, UsersAuthenticator $oui, Request $request): Response
    {
        $passport = new Passport(
            new UserBadge($user->getEmail()),
            new PasswordCredentials($user->getPassword()),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
        return $authenticator->authenticateUser($passport, $this->getUser(), $this->getSubscribedServices('session'));
    }

    #[Route('/resetpassword', name: 'app_reset_password')]
    public function formresetpassword(Request $request,MailerInterface $mailer) : Response
    {
        $form = $this->createForm(EmailResetPasswordFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->get('email')->getData();
            $user = $this->doctrine->getRepository(Users::class)->findIdbyEmail($data);
            $email_send = (new TemplatedEmail())
            ->from('forgot-password@scrutiny.com')
            ->to($data)
            ->subject('Reset Password')
            ->htmlTemplate('email/emailresetpassword.html.twig')
            ->context([
                'email_user' => $data,
                'id' => $user[0]->id,
                'user' => $user[0]
            ]);
            // Mettre sur html au dessus un lien vers reset de password
            $mailer->send($email_send);
        }
        return $this->render('security/emailresetpassword.html.twig', [
            'emailresetpasswordForm' => $form->createView(),
        ]);
    }

    #[Route('/resetpassword/{email}/{id}', name: 'app_users_forget_password')]
    public function resetpassword($id,$email,Request $request,EntityManagerInterface $entityManager,UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = $this->doctrine->getRepository(Users::class)->find($id);
        $form = $this->createForm(ResetPasswordFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute('app_login');
        }
        if ($form->isSubmitted() && $form->isValid()){

        }
        return $this->render('security/resetpassword.html.twig', [
            'user' => $user,
            'resetpasswordForm' => $form->createView(),
        ]);
    }
}
