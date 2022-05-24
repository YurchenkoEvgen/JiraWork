<?php

namespace App\Controller;

use App\DTO\Jira\ConnectionInfo;
use App\DTO\Jira\JiraAPI;
use App\DTO\Jira\JiraAPIInterfacesClass;
use App\DTO\Jira\Project\searchProject;
use App\Entity\Issue;
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
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MainController extends AbstractController
{
    public function __construct(private ManagerRegistry $doctrine){}

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
        $con = new ConnectionInfo($request->getSession());
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
    public function search_issue(Request $request, ManagerRegistry $managerRegistry): Response {
        $JiraAPI = JiraAPI::GetAPIBuilder($request->getSession());
        $data = searchProject::getInterface($JiraAPI)->getData(false);
        $projects = ['Choice'=>null] + array_combine(array_column($data,'name'),array_column($data,'id'));

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

            $arg = array();
            if (isset($data['label'])) {
                $arg[] = "labels = '".$data['label']."'";
            }
            if (isset($data['customlabel'])){
                $arg[] = 'cf[10032] = '."'".$data['customlabel']."'";
            }
            if (isset($data['project'])) {
                $arg[] = 'project = '.$data['project'];
            }
            $str = implode(' AND ', $arg);

            $JiraAPI = JiraAPI::GetAPIBuilder($request->getSession());
            $JiraAPI->setUri('search')
                ->setMethod('POST')
                ->setJson(['jql'=>$str])->sendRequest();
            if ($JiraAPI->isValid()) {
                $data = $JiraAPI->getContentAsArray();
                $arrayofissue = array();
                foreach ($data['issues'] as $value) {
                    $arrayofissue[] = new Issue($value);
                }

                $projectRepository = new ProjectRepository($managerRegistry);
                $issueRepository = new IssueRepository($managerRegistry);
                foreach ($arrayofissue as $issue) {

                    $projectRepository->merge($issue->getProject(), true);
                    $issueRepository->merge($issue, true);
                }
                return $this->render('issue/index.html.twig', [
                    'issues' =>  $arrayofissue,
                    'data'=>print_r($data, true),
                    'forms'=>[
                        $form->createView()
                    ]
                ]);
            }
        }

        return $this->render('base.html.twig',[
            'data'=>print_r($data, true),
            'forms'=>[
                $form->createView()
            ]
        ]);

    }

    #[Route('/test', name: 'test')]
    public function test(Request $request) {
        $j = new JiraAPI($request->getSession());
        $j->setUri('/test');
        $t = new JiraAPIInterfacesClass($j);
        $j->setMethod('POST');
        $t2 = new JiraAPIInterfacesClass($j);
        dump($t);
        dump($t2);
        return $this->render('base.html.twig',[
            'data'=>'OK',
            'forms'=>[]
        ]);
    }
}