<?php

namespace Blog\Service;

use Aptero\Service\AbstractService;
use Blog\Model\Article;
use Blog\Model\Comment;
use User\Service\AuthService;

class BlogService extends AbstractService
{
    public function getPaginator($page, $filter = array())
    {
        $itemsPerPage = 12;

        $articles = $this->getArticles();

        return $articles->getPaginator($page, $itemsPerPage);
    }

    public function getArticle($url)
    {
        $article = new Article();
        $article->select()
            ->where
            ->lessThanOrEqualTo('time_create', date("Y-m-d H:i:s"))
            ->equalTo('url', $url);

        return $article;
    }

    public function getArticles($filters = [])
    {
        $articles = Article::getEntityCollection();

        $articles->setSelect($this->getArticlesSelect($filters));

        return $articles;
    }

    public function getArticlesSelect($filters)
    {
        $select = $this->getSql()->select()
            ->from(['t' => 'articles'])
            ->columns(['name', 'url', 'preview', 'time_create'])
            ->join(['ai' => 'articles_images'], 't.id = ai.depend', ['image-id' => 'id', 'image-filename' => 'filename'], 'left')
            ->order('time_create DESC');

        $select
            ->where->lessThanOrEqualTo('time_create', date("Y-m-d H:i:s"));

        if($filters['limit']) {
            $select->limit($filters['limit']);
        }

        return $select;
    }

    public function getRecoArticles($article, $filter = [])
    {
        $articles = $this->getArticles(['limit' => 4]);
        $articles->select()
            ->where
                ->notEqualTo('t.id', $article->getId())
                ->lessThanOrEqualTo('time_create', date("Y-m-d H:i:s"));

        return $articles;
    }

    public function addComment($data)
    {
        if($user = AuthService::getUser()) {
            $data['user_id'] =  $user->getId();
        }

        $data['status'] = Comment::STATUS_NEW;

        $review = new Comment();
        $review->setVariables($data)->save();
    }
}