<?php

namespace App\Controller;

use App\DTO\Jira\ConnectionInfo;
use App\DTO\Jira\Issue\searchIssue;
use App\DTO\Jira\PostLoader;
use App\DTO\Jira\Project\searchProject;
use App\Repository\IssueRepository;
use Doctrine\Persistence\ManagerRegistry;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Layout;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Command\DumpCompletionCommand;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Validator\Constraints\File;
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
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        $worksheet->fromArray(
            [
                ['', 2010, 2011, 2012],
                ['Q1', 12, 15, 21],
                ['Q2', 56, 73, 86],
                ['Q3', 52, 61, 69],
                ['Q4', 30, 32, 0],
            ]
        );

        $dataSeriesLabels1 = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Worksheet!$C$1', null,1)
        ];
        $xAxisTickValues1 = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Worksheet!$A$2:$A$5', null, 4), // Q1 to Q4
        ];
        $dataSeriesValues1 = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, 'Worksheet!$C$2:$C$5', null, 4),
        ];
        $series1 = new DataSeries(
            DataSeries::TYPE_PIECHART, // plotType
            null, // plotGrouping (Pie charts don't have any grouping)
            range(0, count($dataSeriesValues1) - 1), // plotOrder
            $dataSeriesLabels1, // plotLabel
            $xAxisTickValues1, // plotCategory
            $dataSeriesValues1          // plotValues
        );

        $layout1 = new Layout();
        $layout1->setShowVal(true);
        $layout1->setShowPercent(true);
        $plotArea1 = new PlotArea($layout1, [$series1]);
        $legend1 = new Legend();
        $title1 = new Title('Test Pie Chart');

        $chart1 = new Chart(
            'chart1', // name
            $title1, // title
            $legend1, // legend
            $plotArea1, // plotArea
            true, // plotVisibleOnly
            DataSeries::EMPTY_AS_GAP, // displayBlanksAs
            null, // xAxisLabel
            null   // yAxisLabel - Pie charts don't have a Y-Axis
        );

        $chart1->setTopLeftPosition('A7');
        $chart1->setBottomRightPosition('H20');
        $worksheet->addChart($chart1);
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->setIncludeCharts(true);
        $fn = 'text.xlsx';
        $name = tempnam(sys_get_temp_dir(),$fn);
        $writer->save($name);
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
            $w->setIncludeCharts(true)->save($name);
            return $this->file($name, $fn,ResponseHeaderBag::DISPOSITION_INLINE);
        }

        return $this->render('base.html.twig', [
            'data' =>'',
            'forms' => [
                $form->createView()
            ]
        ]);
    }

    #[Route('/testup', name: 'testup')]
    public function testup(Request $request)
    {
        $form= $this->createFormBuilder()
            ->add('file', FileType::class,[
                'attr'=>['accept'=>'.xlsx,.xls,.typ'],
                'constraints'=>new File(mimeTypes: [
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.oasis.opendocument.spreadsheet'
                ])
            ])
            ->add('upd', SubmitType::class)
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->get('file')->getData()->getPathname();

            $sheet = IOFactory::load($data);
            $worksheet = $sheet->getSheet(1);

            $dataSeriesLabels1 = [
                new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Pivot!$A$1', null,1)
            ];
            $xAxisTickValues1 = [
                new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Pivot!$A$2:$A$6', null, 5), // Q1 to Q4
            ];
            $dataSeriesValues1 = [
                new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, 'Pivot!$B$1:$B$6', null, 5),
            ];
            $series1 = new DataSeries(
                DataSeries::TYPE_PIECHART, // plotType
                null, // plotGrouping (Pie charts don't have any grouping)
                range(0, count($dataSeriesValues1) - 1), // plotOrder
                $dataSeriesLabels1, // plotLabel
                $xAxisTickValues1, // plotCategory
                $dataSeriesValues1          // plotValues
            );

            $layout1 = new Layout();
            $layout1->setShowVal(true);
            $layout1->setShowPercent(true);
            $plotArea1 = new PlotArea($layout1, [$series1]);
            $legend1 = new Legend();
            $title1 = new Title('Test Pie Chart');

            $chart1 = new Chart(
                'chart1', // name
                $title1, // title
                $legend1, // legend
                $plotArea1, // plotArea
                true, // plotVisibleOnly
                DataSeries::EMPTY_AS_GAP, // displayBlanksAs
                null, // xAxisLabel
                null   // yAxisLabel - Pie charts don't have a Y-Axis
            );

            $chart1->setTopLeftPosition('D7');
            $chart1->setBottomRightPosition('K20');
            $worksheet->addChart($chart1);

            $w = new Xlsx($sheet);
            $w->setIncludeCharts(true);
            $fn = 'text.xlsx';
            $name = tempnam(sys_get_temp_dir(),$fn);
            dump($name);
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