<?php
namespace Catalog\Controller;

use Aptero\Mvc\Controller\AbstractActionController;
use Catalog\Model\Product;
use Catalog\Model\Reviews;

class ParserController extends AbstractActionController
{
    public function reviewsAction()
    {
        $products = Product::getEntityCollection();

        foreach ($products as $product) {
            if(!$product->get('mp_url')) {
                echo '<a href="/admin/catalog/products/edit/?id=' . $product->getId() . '">' . $product->get('name') . '</a><br>';
            }
        }

        die('END');
    }

    public function parserAction()
    {
        $products = Product::getEntityCollection();
        $products->select()->where
            ->greaterThan('t.id', 46);

        $i = 0;
		$counter = 0;
        foreach ($products as $product) {
            if(!$product->get('mp_url')) {
                continue;
            }
            $i++;
            if($i > 20) {
                break;
            }
            $counter += $this->parse($product);
            sleep(2);
        }

		
        echo('Добавлено: ' . $counter) . '<br>';
        echo 'Last ID: ' . $product->getId();
        die();
    }

    public function parse($product)
    {
        include_once(MAIN_DIR . '/vendor/phpquery/phpQuery.php');
        $html = file_get_contents($product->get('mp_url'));
        $document = \phpQuery::newDocumentHTML($html);

        $reviews = $document->find('div.review-block');

        $counter = 0;
        foreach($reviews as $review) {
            $data = [
                'product_id' => $product->getId(),
            ];
            $review = \phpQuery::pq($review);

            $data['name'] = trim($review->find('.product-review-author span')->text());

            $data['stars'] = trim($review->find('.rating-holder .rating-stars:not(.rating-stars-secondary)')->text());
            $desc = $review->find('.review-description')->html();
            $desc = str_replace(["\n", "\r"], ' ', $desc);
            $desc = preg_replace('/\s+/', ' ', $desc);

            $cutPos = strpos($desc,'<b>Данный продукт отлично сочетается с:</b>');
            if($cutPos) {
                $desc = trim(substr($desc, 0, strpos($desc,'<b>Данный продукт отлично сочетается с:</b>')));
            }

            $data['review'] = $desc;

            $date = \DateTime::createFromFormat('D M d H:i:s Y', str_replace(['BST ', 'GMT '], '', $review->find('meta[itemprop="datePublished"]')->attr('content')));
            $data['time_create'] = $date->format('Y-m-d H:i:s');

            $review = new Reviews();
            $review->select()->where($data);

            if($review->load()) {
                continue;
            }

            $data['status'] = Reviews::STATUS_NEW;
            $data['source'] = Reviews::SOURCE_MYRPOTEIN;
            $review->setVariables($data)->save();
            $counter++;
        }

		return $counter;
    }

    /**
     * @return \Catalog\Service\OrdersService
     */
    protected function getOrdersService()
    {
        return $this->getServiceLocator()->get('Catalog\Service\OrdersService');
    }
}