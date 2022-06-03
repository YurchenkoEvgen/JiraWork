<?php

namespace App\Controller;

use App\DTO\Jira\ConnectionInfo;
use App\DTO\Jira\Issue\searchIssue;
use App\DTO\Jira\Project\searchProject;
use App\Repository\IssueRepository;
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
    public function getmain(): Response {

        $form = $this->createFormBuilder()->getForm();
        return $this->render('base.html.twig',[
            'forms' => array($form->createView()),
            'data' => 'OK'
        ]);
    }

    #[Route('/auth', name: 'authform')]
    public function gettestform(Request $request): Response {
        $form = $this->createFormBuilder()
            ->add('email', EmailType::class, ['data'=>$request->getSession()->get('auth_email')])
            ->add('token', TextType::class, [
                'data'=>$request->getSession()->get('auth_token'),
                'attr'=>['maxlength'=>24, 'minlength'=>24]
            ])
            ->add('url', UrlType::class, ['data'=>$request->getSession()->get('auth_url')])
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
        return $this->render('base.html.twig',[
            'forms' => array(
                $form->createView()
            ),
            'data' => print_r($request->getContent(),true)
        ]);
    }

    #[Route('/filter', name: 'search_issue')]
    public function search_issue_new(Request $request, ManagerRegistry $managerRegistry): Response {
        $connection = ConnectionInfo::getByRequest($request);
        $search = searchProject::getInterface($connection);
        $data = $search->getData();
        $projects = ['Choice'=>null];
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
                $search->addFilter("labels = '".$data['label']."'");
            }
            if (isset($data['customlabel'])){
                $search->addFilter('cf[10032] = '."'".$data['customlabel']."'");
            }
            if (isset($data['project'])) {
                $search->addFilter('project = '.$data['project']);
            }
            $issueRepository = new IssueRepository($managerRegistry);
            $data = $search->getData();
            foreach ($data as $issue) {
                $issueRepository->merge($issue, true);
            }

            return $this->render('issue/index.html.twig', [
                'issues' => $data,
                'data' => '',
                'forms' => [
                    $form->createView()
                ]
            ]);
        }

        return $this->render('base.html.twig',[
            'data'=>print_r($data, true),
            'forms'=>[
                $form->createView()
            ]
        ]);

    }
}