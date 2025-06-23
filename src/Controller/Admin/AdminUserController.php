<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserForm;
use App\Form\UserPasswordForm;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/user')]
class AdminUserController extends AbstractController
{
    #[Route('/', name: 'admin_user_index')]
    public function index(UserRepository $userRepository): Response
    {
        $activeUsers = $userRepository->findBy(['archived' => false]);
        $archivedUsers = $userRepository->findBy(['archived' => true]);

        return $this->render('admin/user/index.html.twig', [
            'activeUsers' => $activeUsers,
            'archivedUsers' => $archivedUsers,
        ]);
    }

    #[Route('/new', name: 'admin_user_new')]
    public function create(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('password')->getData();
            $confirm = $form->get('confirmPassword')->getData();

            if ($password !== $confirm) {
                $form->get('confirmPassword')->addError(new \Symfony\Component\Form\FormError('Les mots de passe ne correspondent pas.'));
            } else {
                $hashedPassword = $hasher->hashPassword($user, $password);
                $user->setPassword($hashedPassword);
                $user->setRoles(['ROLE_USER']);
                $user->setArchived(false);

                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'Vendeuse créée avec succès.');
                return $this->redirectToRoute('admin_user_index');
            }
        }

        return $this->render('admin/user/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/archive', name: 'admin_user_archive')]
    public function archive(User $user, EntityManagerInterface $em): Response
    {
        $user->setArchived(true);
        $em->flush();

        $this->addFlash('info', 'Vendeuse archivée.');
        return $this->redirectToRoute('admin_user_index');
    }

    #[Route('/{id}/unarchive', name: 'admin_user_unarchive')]
    public function unarchive(User $user, EntityManagerInterface $em): Response
    {
        $user->setArchived(false);
        $em->flush();

        $this->addFlash('info', 'Vendeuse réactivée.');
        return $this->redirectToRoute('admin_user_index');
    }

    #[Route('/{id}/password', name: 'admin_user_password')]
    public function editPassword(Request $request, User $user, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        $form = $this->createForm(UserPasswordForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hashed = $hasher->hashPassword($user, $form->get('plainPassword')->getData());
            $user->setPassword($hashed);
            $em->flush();

            $this->addFlash('success', 'Mot de passe mis à jour.');
            return $this->redirectToRoute('admin_user_index');
        }

        return $this->render('admin/user/edit_password.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
}
