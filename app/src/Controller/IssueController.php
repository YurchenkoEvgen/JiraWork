<?php

namespace App\Controller;

use App\DTO\Jira\ConnectionInfo;
use App\DTO\Jira\Issue\createIssue;
use App\DTO\Jira\Issue\deleteIssue;
use App\DTO\Jira\Issue\editIssue;
use App\Entity\Issue;
use App\Form\IssueType;
use App\Repository\IssueFieldRepository;
use App\Repository\IssueRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/issue')]
class IssueController extends AbstractController
{
    #[Route('/', name: 'app_issue_index', methods: ['GET'])]
    public function index(IssueRepository $issueRepository): Response
    {
        return $this->render('issue/index.html.twig', [
            'issues' => $issueRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_issue_new', methods: ['GET', 'POST'])]
    public function new(Request $request, IssueRepository $issueRepository): Response
    {
        $issue = new Issue();
        $form = $this->createForm(IssueType::class, $issue);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $new = createIssue::getInterface(ConnectionInfo::getByRequest($request))->setIssue($issue);
            $issue = $new->getData();
            if ($new->isValid()) {
                $issueRepository->add($issue, true);
                return $this->redirectToRoute('app_issue_index', [], Response::HTTP_SEE_OTHER);
            } else {
                dump($new->getError());
            }
        }

        return $this->renderForm('issue/new.html.twig', [
            'issue' => $issue,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_issue_show', methods: ['GET'])]
    public function show(Issue $issue, IssueFieldRepository $issueFieldRepository): Response
    {
        $issueFieldRepository->getForProject($issue->getProject());
        return $this->render('issue/show.html.twig', [
            'issue' => $issue,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_issue_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Issue $issue, IssueRepository $issueRepository): Response
    {
        $form = $this->createForm(IssueType::class, $issue);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $edit = editIssue::getInterface(ConnectionInfo::getByRequest($request))->setIssue($issue);
            if ($edit->getData()) {
                $issueRepository->add($issue, true);
            } else {
                dump($edit);
            }
        }
        return $this->renderForm('issue/edit.html.twig', [
            'issue' => $issue,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_issue_delete', methods: ['POST'])]
    public function delete(Request $request, Issue $issue, IssueRepository $issueRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $issue->getId(), $request->request->get('_token'))) {
            $delete = deleteIssue::getInterface(ConnectionInfo::getByRequest($request))->setIssue($issue);
            if ($delete->getData()) {
                $issueRepository->remove($issue, true);
                return $this->redirectToRoute('app_issue_index', [], Response::HTTP_SEE_OTHER);
            } else {
                dump($delete->getError());
            }
        }
        $form = $this->createForm(IssueType::class, $issue);
        return $this->renderForm('issue/edit.html.twig', [
            'issue' => $issue,
            'form' => $form,
        ]);
    }
}
