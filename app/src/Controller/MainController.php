<?php

namespace App\Controller;

use App\DTO\Jira\ConnectionInfo;
use App\DTO\Jira\Issue\searchIssue;
use App\DTO\Jira\IssueField\getIssueFields;
use App\DTO\Jira\Project\searchProject;
use App\Entity\Project;
use App\Repository\IssueFieldRepository;
use App\Repository\IssueRepository;
use App\Repository\ProjectRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'main')]
    public function getmain(): Response
    {

        $form = $this->createFormBuilder()->getForm();
        return $this->render('base.html.twig', [
            'forms' => array($form->createView()),
            'data' => 'OK'
        ]);
    }

    #[Route('/auth', name: 'authform')]
    public function gettestform(Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add('email', EmailType::class, ['data' => $request->getSession()->get('auth_email')])
            ->add('token', TextType::class, [
                'data' => $request->getSession()->get('auth_token'),
                'attr' => ['maxlength' => 24, 'minlength' => 24]
            ])
            ->add('url', UrlType::class, ['data' => $request->getSession()->get('auth_url')])
            ->add('submit', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $request->getSession()->set('auth_email', $data['email']);
            $request->getSession()->set('auth_token', $data['token']);
            $request->getSession()->set('auth_url', $data['url']);
        }

        $con = ConnectionInfo::getByRequest($request);
        $authredirecturi = $request->getSession()->get('authredirecturi');
        if ($con->isValid() && !empty($authredirecturi)) {
            $request->getSession()->remove('authredirecturi');
            return $this->redirect($authredirecturi);
        }
        return $this->render('base.html.twig', [
            'forms' => array(
                $form->createView()
            ),
            'data' => print_r($request->getContent(), true)
        ]);
    }

    #[Route('/filter', name: 'search_issue')]
    public function search_issue_new(Request $request, ManagerRegistry $managerRegistry): Response
    {
        $connection = ConnectionInfo::getByRequest($request);
        $search = searchProject::getInterface($connection);
        $data = $search->getData();
        $projects = ['Choice' => null];
        foreach ($data as $value) {
            $projects[$value->getName()] = $value->getId();
        }

        $form = $this->createFormBuilder()
            ->add('label', TextType::class, ['required' => false])
            ->add('customlabel', TextType::class, ['required' => false])
            ->add('project', ChoiceType::class, [
                'choices' => $projects
            ])
            ->add('Submit', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $search = searchIssue::getInterface($connection);
            if (isset($data['label'])) {
                $search->addFilter("labels = '" . $data['label'] . "'");
            }
            if (isset($data['customlabel'])) {
                $search->addFilter('cf[10032] = ' . "'" . $data['customlabel'] . "'");
            }
            if (isset($data['project'])) {
                $search->addFilter('project = ' . $data['project']);
            }

            $issues = $search->setManagerRegistry($managerRegistry)->getData();

            if ($search->hasErrors(1030)) {
                $searchField = getIssueFields::getInterface($connection);
                $projectRepository = new ProjectRepository($managerRegistry);
                $fields = $searchField
                    ->setRepository($projectRepository)
                    ->getData();

                if ($searchField->hasErrors(1010)) {
                    $searchProjects = searchProject::getInterface($connection);
                    $projects = $searchProjects->getData();
                    foreach ($projects as $project) {
                        $projectRepository->merge($project);
                    }
                    $projectRepository->flush();

                    $fields = $searchField->extractData();
                }

                $issueFieldRepository = new IssueFieldRepository($managerRegistry);
                foreach ($fields as $field) {
                    $issueFieldRepository->merge($field);
                }
                $issueFieldRepository->flush();

                $issues = $search->extractData();
            }

            $issueRepository = new IssueRepository($managerRegistry);
            foreach ($issues as $issue) {
                $issueRepository->merge($issue);
            }
            $issueRepository->flush();

            return $this->render('issue/index.html.twig', [
                'issues' => $issues,
                'data' => '',
                'forms' => [
                    $form->createView()
                ]
            ]);
        }

        return $this->render('base.html.twig', [
            'data' => print_r($data, true),
            'forms' => [
                $form->createView()
            ]
        ]);

    }

    #[Route('/test', name: 'app_test')]
    public function testroute(IssueFieldRepository $issueFieldRepository)
    {
        $pr = new Project();
        $pr->setId('10001');

        $x = $issueFieldRepository->getForProject($pr);
        dump($x);
        return $this->render(
            'base.html.twig',
            [
                'data' => 'OK',
            ]
        );
    }

//    #[Route('/new_filter', name: 'new_filter')]
//    public function new_filter(ManagerRegistry $managerRegistry)
//    {
//        $IssueFieldRepository = new IssueFieldRepository($managerRegistry);
//        $IssueFields = $IssueFieldRepository->findAll();
//    }
}