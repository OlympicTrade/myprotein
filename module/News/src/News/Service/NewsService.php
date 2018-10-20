<?php
namespace News\Service;

use News\Model\News;
use Aptero\Service\AbstractService;

class NewsService extends AbstractService
{
    /**
     * @param int $page
     * @return Paginator
     */
    public function getPaginator($page)
    {
        $news = new News();
        $news = $news->getCollection();
        $news->select()->where
            ->equalTo('status', 1)
            ->lessThanOrEqualTo('time_create', date('Y-m-d H:i:s'));

        return $news->getPaginator($page, 10);
    }

}