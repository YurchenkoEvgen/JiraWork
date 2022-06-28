<?php

namespace App\Controller;

use App\Entity\AppUser;
use App\Form\AppUserType;
use App\Repository\AppUserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/app/user')]
class AppUserController extends AbstractController
{
    #[Route('/', name: 'app_appuser_index', methods: ['GET'])]
    public function index(AppUserRepository $appUserRepository): Response
    {
        return $this->render('app_user/index.html.twig', [
            'app_users' => $appUserRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_appuser_new', methods: ['GET', 'POST'])]
    public function new(Request $request, AppUserRepository $appUserRepository): Response
    {
        $appUser = new AppUser();
        $form = $this->createForm(AppUserType::class, $appUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $appUserRepository->add($appUser, true);

            return $this->redirectToRoute('app_appuser_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('app_user/new.html.twig', [
            'app_user' => $appUser,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_appuser_show', methods: ['GET'])]
    public function show(AppUser $appUser): Response
    {
        return $this->render('app_user/show.html.twig', [
            'app_user' => $appUser,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_appuser_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, AppUser $appUser, AppUserRepository $appUserRepository): Response
    {
        $form = $this->createForm(AppUserType::class, $appUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $appUserRepository->add($appUser, true);

            return $this->redirectToRoute('app_appuser_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('app_user/edit.html.twig', [
            'app_user' => $appUser,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_appuser_delete', methods: ['POST'])]
    public function delete(Request $request, AppUser $appUser, AppUserRepository $appUserRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$appUser->getId(), $request->request->get('_token'))) {
            $appUserRepository->remove($appUser, true);
        }

        return $this->redirectToRoute('app_appuser_index', [], Response::HTTP_SEE_OTHER);
    }
}
