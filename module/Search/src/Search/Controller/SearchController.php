<?php
namespace Search\Controller;

use Aptero\Mvc\Controller\AbstractActionController;

use Search\Form\SearchForm;

class SearchController extends AbstractActionController
{
    public function indexAction()
    {
        $this->generate();

        $searchService = $this->getSearchService();

        $query = $this->params()->fromQuery('query');
        $searchForm = new SearchForm();
        if($query) {
            $searchForm->setData(array('query' => $query))->setFilters()->isValid();
            $searchService->search($searchForm->getData()['query']);
        }

        return array(
            'searchForm' => $searchForm
        );
    }

    /**
     * @return \Search\Service\SearchService
     */
    public function getSearchService()
    {
        return $this->getServiceLocator()->get('Search\Service\SearchService');
    }
}