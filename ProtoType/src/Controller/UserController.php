<?php

namespace App\Controller;
use App\Entity\User ;
use App\Form\SignUpType;
use App\Form\LoginType;
use App\Form\VerifType;
use App\Form\RestorePasswordType;
use App\Form\ForgotPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class UserController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    private function checkUserAndPassword(string $email, string $password): bool
    {
        $result = $this->entityManager->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u')
            ->where('u.email = :email')
            ->andWhere('u.password = :password')
            ->setParameter('email', $email)
            ->setParameter('password', $password)
            ->getQuery()
            ->getOneOrNullResult();

        return $result !== null;
    }
    private function searchEmail(string $email): bool
    {
        $result = $this->entityManager->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u')
            ->where('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();

        return $result !== null;
    }
    private function updatePassword( $email , $newPassword) : bool 
    {
        $result = $this->entityManager->createQueryBuilder()
            ->update(User::class, 'u')
            ->set('u.password', ':newPassword')
            ->where('u.email = :email')
            ->setParameter('newPassword', $newPassword)
            ->setParameter('email', $email)
            ->getQuery()
            ->execute();

        return $result !== null;
    }
    private function sendEmail(MailerInterface $mailer , String $mail ,String $verifCode)
    {
            $email = (new Email())
                ->from('alhwyt237@gmail.com')
                ->to($mail)
                ->subject('Esprit Verse verfification code')
                ->html("This is the verification code of your esprit verse account : " . htmlspecialchars($verifCode))
                ;
            $mailer->send($email);
    }
    #[Route('/user/login', name: 'app_user_login')]
    public function Login(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(LoginType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (str_contains($user->getEmail(), '@esprit.tn')) {
                if($this->checkUserAndPassword($user->getEmail(), $user->getPassword())){
                    return $this->redirectToRoute('app_data');
                }
                else{
                    $this->addFlash('error', 'Email or Password is incorrect');
                }
            }
            else {
                $this->addFlash('error', 'Your email must use the "@esprit.tn" domain to login');
            }

        }
        return $this->render('user/login.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/user/signup', name: 'app_user_signup')]
    public function SignUp(Request $request ,MailerInterface $mailer,SessionInterface $session): Response
    {
        $user = new User();
        $form = $this->createForm(SignUpType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
                $email = $form->get("email")->getData() ;
                if(str_contains($email, '@esprit.tn')){
                $verifCode = strval(random_int(1000, 9999));
                $session->set('verifCode', $verifCode);
                $session->set('user', $user);
                $this->sendEmail( $mailer, $email , $verifCode);
                return $this->redirectToRoute('app_user_verifcode');
                }
                else {
                    $this->addFlash('error', 'Your email must use the "@esprit.tn" domain to sign up');
                }
        }
        return $this->render('user/signUp.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/user/signup/verif', name: 'app_user_verifcode')]
    public function VerifCode(Request $request,SessionInterface $session): Response
    {
        $form = $this->createForm(verifType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
                if(intval($session->get('verifCode')) == $form->get("verificationCode")->getData())
                {
                    $session->remove('verifCode');
                    $user = $session->get('user');
                    if($user->getType() == "student"){
                        $user->setTokens(3);
                    }
                    $this->entityManager->persist($user);
                    $this->entityManager->flush();
                    $session->remove('user');
                    return $this->redirectToRoute('app_user_login');
                }
                else {
                    $this->addFlash('codeError', 'The vrification code is incorrect');
                    return $this->redirectToRoute('app_user_verifcode');
                }
        }
        return $this->render('user/verif.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/user/login/forgotPassword', name: 'app_user_forgotPassword')]
    public function forgotPassword(Request $request,MailerInterface $mailer,SessionInterface $session): Response
    {
        $form = $this->createForm(ForgotPasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get("email")->getData();
            if($this->searchEmail($email)){
            $verifCode = strval(random_int(1000, 9999));
            $session->set('verifCode', $verifCode);
            $session->set('email', $email);
            $this->sendEmail( $mailer, $email , $verifCode);
            return $this->redirectToRoute('app_user_restorePassword');
            }
            else {
                $this->addFlash('error', 'Please enter a valid email');
            }
        }
        return $this->render('user/forgotPassword.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/user/login/restorePassword', name: 'app_user_restorePassword')]
    public function restorePassword(Request $request, SessionInterface $session): Response
    {
        if ($session->has('verifCode') && !$session->has('verified')) {
            $form = $this->createForm(verifType::class);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                if (intval($session->get('verifCode')) == $form->get("verificationCode")->getData()) {
                    $session->set('verified', true);
                    $session->remove('verifCode'); 
                    return $this->redirectToRoute('app_user_restorePassword');
                } else {
                    $this->addFlash('codeError', 'The verification code is incorrect');
                    return $this->redirectToRoute('app_user_restorePassword');
                }
            }
            return $this->render('user/verif.html.twig', [
                'form' => $form->createView(),
            ]);
        }
        $form = $this->createForm(RestorePasswordType::class);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $this->updatePassword($session->get('email'), $form->get("password")->getData());
            $session->remove('email');
            $session->remove('verified'); 
            
            return $this->redirectToRoute('app_user_login');
        }
        return $this->render('user/restorePassword.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
}