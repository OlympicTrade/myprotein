<?php
namespace Catalog\Controller;

use Aptero\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class YandexController extends AbstractActionController
{
    public function xmlAction()
    {
        //Для яндекс справочника
        $this->getYandexYml()->getXML();
    }

    public function ymlAction()
    {
        //Для яндекс метрики и вебмастера
        $source = $this->params()->fromQuery('s');

        switch ($source) {
            case 'mail':
                $options = [
                    'utm'   => [
                        'utm_source=mail',
                        'utm_campaign=market',
                        'utm_medium=cpc'
                    ],
                    'products' => [
                        'full' => false
                    ]
                ];
                break;
            case 'blizko':
                $options = [
                    'utm'   => [
                        'utm_source=blizko',
                        'utm_campaign=market',
                        'utm_medium=cpc'
                    ],
                    'products' => [
                        'full' => false
                    ]
                ];
                break;
            case 'regmarkets':
                $options = [
                    'utm'   => [
                        'utm_source=regmarkets',
                        'utm_campaign=market',
                        'utm_medium=cpc'
                    ],
                    'products' => [
                        'full'  => false,
                    ]
                ];
                break;
            case 'price':
                $options = [
                    'utm'   => [
                        'utm_source=price',
                        'utm_campaign=market',
                        'utm_medium=cpc'
                    ],
                    'products' => [
                        'full' => false
                    ]
                ];
                break;
            case 'nadavi':
                $options = [
                    'utm'   => [
                        'utm_source=nadavi',
                        'utm_campaign=market',
                        'utm_medium=cpc'
                    ],
                    'products' => [
                        'full' => false
                    ]
                ];
                break;
            case 'yandex':
                $options = [
                    'utm'   => [
                        'utm_source=yandex',
                        'utm_campaign=market',
                        'utm_medium=cpc'
                    ],
                    'products' => [
                        'full'  => true,
                        'model' => false,
                    ]
                ];
                break;
            default:
                $options = [
                    'products' => [
                        'full' => false
                    ]
                ];
                break;
        }

        header("Content-type: text/xml; charset=utf-8");
        echo $this->getYandexYml()->getYML($options);
        die();
    }

    public function apiOrderAcceptAction()
    {
        return new JsonModel($this->getYandexMarket()->acceptOrder());
    }

    public function apiCartAction()
    {
        //$query = '{"cart":{"currency":"RUR","items":[{"feedId":488657,"offerId":"7S4S25","feedCategoryId":"3","offerName":"Сывороточный протеин Myprotein Impact Whey Protein (1 кг) Шоколад - орех","count":1,"params":"Вкус: шоколад"},{"feedId":488657,"offerId":"78S112S176","feedCategoryId":"12","offerName":"Гейнер Myprotein Миндальная Паста (1 кг) Без кусочков орехов","count":1,"params":""},{"feedId":488657,"offerId":"98S134S220","feedCategoryId":"3","offerName":"Сывороточный протеин Myprotein Impact Whey Protein (пробник) (25 г) Шоколад-банан","count":1,"params":"Вкус: банан"},{"feedId":488657,"offerId":"35S52S109","feedCategoryId":"13","offerName":"Предтренировочный комплекс Myprotein Креатин Моногидрат (250 г) Арбуз","count":1,"params":""},{"feedId":488657,"offerId":"188S141S237","feedCategoryId":"15","offerName":"Питание для спортсменов Myprotein Батончики High Protein (12 x 80 г) Ваниль - мед","count":1,"params":""},{"feedId":488657,"offerId":"7S6S3","feedCategoryId":"3","offerName":"Сывороточный протеин Myprotein Impact Whey Protein (5 кг) Натуральный вкус","count":1,"params":""},{"feedId":488657,"offerId":"36S55S114","feedCategoryId":"3","offerName":"Казеиновый протеин Myprotein Мицеллярный казеин (1 кг) Клубника","count":1,"params":"Вкус: клубника"},{"feedId":488657,"offerId":"81S115S181","feedCategoryId":"15","offerName":"Питание для спортсменов Myprotein Паста из Кешью (1кг) С кусочками орехов","count":1,"params":""},{"feedId":488657,"offerId":"16S21S62","feedCategoryId":"12","offerName":"Гейнер Myprotein Hard Gainer Extreme (2.5 кг) Клубника со сливками","count":1,"params":"Вкус: клубника"},{"feedId":488657,"offerId":"185S128S197","feedCategoryId":"4","offerName":"Аминокислоты Myprotein Таурин (250 г) Натуральный вкус","count":1,"params":""}],"delivery":{"region":{"id":2,"name":"Санкт-Петербург","type":"CITY","parent":{"id":10174,"name":"Санкт-Петербург и Ленинградская область","type":"SUBJECT_FEDERATION","parent":{"id":17,"name":"Северо-Западный федеральный округ","type":"COUNTRY_DISTRICT","parent":{"id":225,"name":"Россия","type":"COUNTRY"}}}}}}}';
        return new JsonModel($this->getYandexMarket()->confirmCart());
    }

    /**
     * @return \Catalog\Service\YandexMarket
     */
    protected function getYandexMarket()
    {
        return $this->getServiceLocator()->get('Catalog\Service\YandexMarket');
    }

    /**
     * @return \Catalog\Service\YandexYml
     */
    protected function getYandexYml()
    {
        return $this->getServiceLocator()->get('Catalog\Service\YandexYml');
    }
}