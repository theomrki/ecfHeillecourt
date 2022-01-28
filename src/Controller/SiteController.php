<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\ArticleFormType;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class SiteController extends AbstractController
{
    /**
     * @Route("/site", name="site")
     */
    public function index(ArticleRepository $repository)
    {

        $articles = $repository->findAll();

        return $this->render('site/index.html.twig', [
            'controller_name' => 'SiteController',
            'articles' => $articles
        ]);
    }

    /**
     * @Route("/", name="home")
     */
    public function home()
    {
        return $this->render('site/home.html.twig');
    }

    /**
     * @Route("/site/create", name="create_content")
     * @Route("/site/{id}/edit", name="edit_content")
     */
    public function form(Article $article = null, Request $request, EntityManagerInterface $manager)
    {
        if (!$article) {
            $article = new Article();
        }

        $form = $this->createForm(ArticleFormType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($article->getId()) {
                $article->setCreatedAt(new \DateTime);
            }

            $manager->persist($article);
            $manager->flush();
            return $this->redirectToRoute('show', ['id' => $article->getId()]);
        }

        return $this->render('site/create.html.twig', [
            'formArticle' => $form->createView(),
            'editArticle' => $article->getId() !== null
        ]);
    }

    /**
     * @Route("/site/{id}", name="show")
     */
    public function show(Article $article, Request $request, EntityManagerInterface $manager)
    {
        $comment = new Comment();

        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setCreatedAt(new \DateTime())
                ->setArticle($article);

            $manager->persist($comment);
            $manager->flush();

            return $this->redirectToRoute('show', ['id' => $article->getId()]);
        }

        return $this->render('site/show.html.twig', [
            'article' => $article,
            'commentForm' => $form->createView()
        ]);
    }
}
