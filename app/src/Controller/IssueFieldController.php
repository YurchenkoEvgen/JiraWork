<?php

namespace App\Controller;

use App\DTO\Jira\ConnectionInfo;
use App\DTO\Jira\IssueField\getIssueFields;
use App\Entity\IssueField;
use App\Form\IssueFieldType;
use App\Repository\IssueFieldRepository;
use App\Repository\ProjectRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/issuefield')]
class IssueFieldController extends AbstractController
{
    #[Route('/', name: 'app_issue_field_index', methods: ['GET', 'POST'])]
    public function index(IssueFieldRepository $issueFieldRepository, Request $request, ManagerRegistry $managerRegistry): Response
    {
        $form = $this->createFormBuilder()
            ->add('Sync', SubmitType::class,['label'=>'Sync Fields'])->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $projectRepository = new ProjectRepository($managerRegistry);
            $issueFieldRepository = new IssueFieldRepository($managerRegistry);
            $issueFieldRepository->findAll();
            $fields = getIssueFields::getInterface(ConnectionInfo::getByRequest($request))
                ->setRepository($projectRepository)
                ->getData();
            foreach ($fields as $field) {
                $issueFieldRepository->merge($field);
            }
            $issueFieldRepository->flush();
        }

        return $this->render('issue_field/index.html.twig', [
            'forms' => [$form->createView()],
            'issue_fields' => $issueFieldRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_issue_field_new', methods: ['GET', 'POST'])]
    public function new(Request $request, IssueFieldRepository $issueFieldRepository): Response
    {
        $issueField = new IssueField();
        $form = $this->createForm(IssueFieldType::class, $issueField);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $issueFieldRepository->add($issueField, true);

            return $this->redirectToRoute('app_issue_field_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('issue_field/new.html.twig', [
            'issue_field' => $issueField,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_issue_field_show', methods: ['GET'])]
    public function show(IssueField $issueField): Response
    {
        return $this->render('issue_field/show.html.twig', [
            'issue_field' => $issueField,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_issue_field_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, IssueField $issueField, IssueFieldRepository $issueFieldRepository): Response
    {
        $form = $this->createForm(IssueFieldType::class, $issueField);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $issueFieldRepository->add($issueField, true);

            return $this->redirectToRoute('app_issue_field_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('issue_field/edit.html.twig', [
            'issue_field' => $issueField,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_issue_field_delete', methods: ['POST'])]
    public function delete(Request $request, IssueField $issueField, IssueFieldRepository $issueFieldRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$issueField->getId(), $request->request->get('_token'))) {
            $issueFieldRepository->remove($issueField, true);
        }

        return $this->redirectToRoute('app_issue_field_index', [], Response::HTTP_SEE_OTHER);
    }
}
