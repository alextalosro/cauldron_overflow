<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Repository\AnswerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminAnswerController extends BaseController
{
	/**
	 * @Route("/admin/answers", name="admin_answers")
	 */
	public function index(AnswerRepository $repository): Response
	{
		$answers = $repository->findAll();
		
		return $this->render('admin/answers.html.twig', [
			'answers' => $answers,
		]);
	}
	
	/**
	 * @Route("admin/answers/delete/{id}", name="admin_app_answer_delete")
	 */
	public function delete(Answer $answer, EntityManagerInterface $entityManager)
	{
		$entityManager->remove($answer);
		$entityManager->flush();
		
		return $this->redirectToRoute('admin_answers');
	}
	
	/**
	 * @Route("admin/answers/publish/{id}", name="admin_app_answer_publish")
	 */
	public function publish(Answer $answer, EntityManagerInterface $entityManager)
	{
		($answer->getStatus() === 'approved' ? $answer->setStatus($answer::STATUS_NEEDS_APPROVAL) : $answer->setStatus($answer::STATUS_APPROVED));
		$entityManager->flush();
		
		$this->addFlash('success', 'Status changed!');
		return $this->redirectToRoute('admin_answers');
	}
}
