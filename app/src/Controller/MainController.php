<?php

namespace App\Controller;

use App\DTO\Jira\ConnectionInfo;
use App\DTO\Jira\Issue\searchIssue;
use App\DTO\Jira\PostLoader;
use App\DTO\Jira\Project\searchProject;
use App\Entity\Project;
use App\Repository\IssueRepository;
use Doctrine\Persistence\ManagerRegistry;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
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
            if ($search->hasPostload()) {
                $postload = new PostLoader($search,$managerRegistry,$connection);
                $issues = $postload->doPostLoad();
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
    public function testroute(ManagerRegistry $managerRegistry)
    {
        $sheet = new Spreadsheet();
        $sheet->removeSheetByIndex(0);
        $table = $sheet->createSheet();
        $table->setTitle('Projects');
        $projects = $managerRegistry->getRepository(Project::class)->findAll();
        $table->fromArray(['ID','NAME']);
        $table->getStyle('A1:B1')->getFont()->setBold(true);
        $table->getStyle('A1:B1')->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_MEDIUM)->setColor(new Color(Color::COLOR_BLACK));
        foreach ($projects as $iteam) {
            $table->fromArray(
                [$iteam->getID(),$iteam->getName()],
                null,
                $table->getCellByColumnAndRow(1,$table->getHighestDataRow()+1)->getCoordinate()
            );
        }

//        return $this->render('base.html.twig', ['data' =>'','forms' => []]);
        $w = new Xlsx($sheet);
        $fn = 'text.xlsx';
        $name = tempnam(sys_get_temp_dir(),$fn);
        $w->save($name);
        return $this->file($name, $fn,ResponseHeaderBag::DISPOSITION_INLINE);
    }

    #[Route('/mytime', name: 'mytime')]
    public function mytime(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('startData', DateType::class)
            ->add('endData',DateType::class)
            ->add('Submit', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $cli = HttpClient::create();
            $arg = [
                'json'=>[
                    "app-key"=>"0cbc6611f5540bd0809a388dc95a615b:KpSHcl6t6EKp^Pn#D6I3",
                    "user-key"=>"2980:(i(H(D!qo|3iEjRIS*L1",
                    "start-date"=>$form->getData()['startData']->format('Y-m-d'),
                    "end-date"=>$form->getData()['endData']->format('Y-m-d')
                ]
            ];
            $resp = $cli->request('POST','https://pl.itcraft.co/api/client-v1/posts/list',$arg)->toArray();
            $toexel = [['Date', 'Time', 'Task']];
            foreach ($resp['posts'] as $iteam) {
                $toexel[] = [
                    $iteam['posted-on-day'],
                    $iteam['time-taken']/60,
                    $iteam['task']
                ];
            }
            $toexel[] = [
                'Total:',
                array_sum(array_column($toexel,1)),
                ''
            ];

            $sheet = new Spreadsheet();
            $table = $sheet->getActiveSheet();
            $table->fromArray($toexel);
            $table->getColumnDimension('A')->setAutoSize(true);
            $table->getColumnDimension('B')->setAutoSize(true);
            $table->getColumnDimension('C')->setAutoSize(true);
            $table->getStyle([1,1,6,1])->getFont()->setBold(true);
            $table->getStyle([1,$table->getHighestDataRow(),6,$table->getHighestDataRow()])->getFont()->setBold(true);

            $w = new Xlsx($sheet);
            $fn = 'text.xlsx';
            $name = tempnam(sys_get_temp_dir(),$fn);
            $w->save($name);
            return $this->file($name, $fn,ResponseHeaderBag::DISPOSITION_INLINE);
        }

        return $this->render('base.html.twig', [
            'data' =>'',
            'forms' => [
                $form->createView()
            ]
        ]);
    }
}