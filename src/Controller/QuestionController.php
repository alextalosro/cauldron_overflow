<?php

namespace App\Controller;

use App\Entity\Question;
use App\Repository\AnswerRepository;
use App\Repository\QuestionRepository;
use App\Service\MarkdownHelper;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class QuestionController extends BaseController
{
    private $logger;
    private $isDebug;

    public function __construct(LoggerInterface $logger, bool $isDebug)
    {
        $this->logger = $logger;
        $this->isDebug = $isDebug;
    }


    /**
     * @Route("/{page<\d+>}", name="app_homepage")
     */
    public function homepage(QuestionRepository $repository, int $page = 1)
    {
        $queryBuilder = $repository->createAskedOrderedByNewestQueryBuilder();

        $pagerfanta = new Pagerfanta(new QueryAdapter($queryBuilder));
        $pagerfanta->setMaxPerPage(5);
        $pagerfanta->setCurrentPage($page);

        return $this->render('question/homepage.html.twig', [
            'pager' => $pagerfanta,
        ]);
    }

    /**
     * @Route("/questions/new", name="app_question_create")
     * @IsGranted("ROLE_USER")
     */
    public function new(Request $request)
    {
		$this->denyAccessUnlessGranted('ROLE_USER');
	
	    return $this->render('question/create.html.twig', [
	    'request' => $request->request,
		    'errors'=> []
	    ]);
    }
	
	/**
	 * @Route("/questions/store", name="app_question_store")
	 * @IsGranted("ROLE_USER")
	 */
	public function store(EntityManagerInterface $entityManager,
	                      Request $request,
	                      ValidatorInterface $validator)
	{
		$question = new Question();
		
		$question->setName($request->get('name'));
		$question->setSlug($request->get('slug'));
		$question->setQuestion($request->get('question'));
		$question->setOwner($this->getUser());
		$question->setIsPublished(0); //Need to be aproved by admin.
		$question->setAskedAt(Carbon::now());
		
		$errors = $validator->validate($question);
		
		if (count($errors) > 0) {
			return $this->render('question/create.html.twig', [
				'errors' => $errors,
				'request' => $request->request
			]);
		}
		
		$entityManager->persist($question);
		$entityManager->flush();
		
		return $this->redirectToRoute('app_question_show', [
			'slug'=> $question->getSlug()
		]);
	}

    /**
     * @Route("/questions/{slug}", name="app_question_show")
     */
    public function show(Question $question)
    {
        if ($this->isDebug) {
            $this->logger->info('We are in debug mode!');
        }

        return $this->render('question/show.html.twig', [
            'question' => $question,
        ]);
    }
	
	/**
	 * @Route("/questions/edit/{slug}", name="app_question_edit")
	 */
	public function edit(Question $question)
	{
		$this->denyAccessUnlessGranted('EDIT', $question);
		
		return $this->render('question/edit.html.twig', [
			'question' => $question,
		]);
	}
	
    /**
     * @Route("/questions/{slug}/vote", name="app_question_vote", methods="POST")
     */
    public function questionVote(Question $question, Request $request, EntityManagerInterface $entityManager)
    {
        $direction = $request->request->get('direction');

        if ($direction === 'up') {
            $question->upVote();
        } elseif ($direction === 'down') {
            $question->downVote();
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_question_show', [
            'slug' => $question->getSlug()
        ]);
    }
}
