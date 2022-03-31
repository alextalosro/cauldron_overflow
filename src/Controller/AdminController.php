<?php

namespace App\Controller;

use App\Entity\Question;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin/dashboard", name="admin_dashboard")
     */
    public function dashboard(ChartBuilderInterface $chartBuilder)
    {
        $chart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
            'datasets' => [
                [
                    'label' => 'Potions Brewed',
                    'backgroundColor' => 'rgb(255, 99, 132)',
                    'data' => [0, 10, 5, 2, 20, 30, 45],
                ],
                [
                    'label' => 'Frogs Created',
                    'backgroundColor' => 'rgb(86, 217, 0)',
                    'data' => [40, 25, 55, 32, 22, 10, 6],
                ],
            ],
        ]);

        $chart2 = $chartBuilder->createChart(Chart::TYPE_PIE);
        $chart2->setData([
            'labels' => ['Self-Vanishing', 'Miniaturization', 'Clown Feet', 'Poor Musical Taste'],
            'datasets' => [
                [
                    'label' => 'Accidents',
                    'data' => [40, 66, 110, 20],
                    'backgroundColor' => [
                      'rgb(255, 99, 132)',
                      'rgb(54, 162, 235)',
                      'rgb(255, 205, 86)'
                    ],
                    'hoverOffset' => 4,
                ],
            ],
        ]);

        return $this->render('admin/dashboard.html.twig', [
            'chart' => $chart,
            'chart2' => $chart2,
        ]);
    }
	
	/**
	 * @Route("/admin/login");
	 */
	public function adminLogin()
	{
		return new Response('Pretend admin login page that should be public');
	}
	
	/**
	 * @Route("/admin/answers");
	 */
	public function adminAnswers()
	{
		$this->denyAccessUnlessGranted('ROLE_COMMENT_ADMIN');
		
		return new Response('Pretend admin answer page');
	}
	
	/**
	 * @Route("/admin/questions", name="admin_questions")
	 */
	public function index(QuestionRepository $repository)
	{
		$questions = $repository->findAll();
		
		return $this->render('admin/questions.html.twig', [
			'questions' => $questions,
		]);
	}
	
	/**
	 * @Route("admin/questions/edit/{slug}", name="admin_app_question_edit")
	 */
	public function edit(Question $question)
	{
		return $this->render('question/edit.html.twig', [
			'question' => $question,
		]);
	}
	
	/**
	 * @Route("admin/questions/update/{slug}", name="admin_app_question_update")
	 */
	public function update(Question $question, EntityManagerInterface $entityManager,Request $request)
	{
		
		$question->setName($request->request->get('name'));
		$question->setSlug($request->request->get('slug'));
		$question->setQuestion($request->request->get('question'));
		
		$entityManager->flush();
		
		return $this->render('question/show.html.twig', [
			'question' => $question,
		]);
	}
	
	/**
	 * @Route("admin/questions/delete/{slug}", name="admin_app_question_delete")
	 */
	public function delete(Question $question, EntityManagerInterface $entityManager)
	{
		$entityManager->remove($question);
		$entityManager->flush();

		return $this->redirectToRoute('admin_questions');
	}
	
	/**
	 * @Route("admin/questions/publish/{slug}", name="admin_app_question_publish")
	 */
	public function publish(Question $question, EntityManagerInterface $entityManager)
	{
		($question->getIsPublished() ? $question->setIsPublished(0) : $question->setIsPublished(1));
		$entityManager->flush();
		
		$this->addFlash('success', 'Status changed!');
		return $this->redirectToRoute('admin_questions');
	}
}
