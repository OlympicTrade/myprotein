<?php
namespace Catalog\Controller;

use Application\Model\Module;
use Aptero\Mvc\Controller\AbstractActionController;

use Catalog\Model\Order;
use Catalog\Service\SyncService;
use User\Service\AuthService;
use Zend\View\Model\JsonModel;

class SyncController extends AbstractActionController
{
    protected $headers;

    protected $path;
    
    public function productAction()
    {
        $id  = $this->params()->fromQuery('id');

        if(!$id) { $this->send404(); }

        return new JsonModel($this->getSyncService()->getProductStock($id));
    }

    public function updateAction()
    {
        /*$import = DATA_DIR . '/sync/1c/' . 'import.xml';
        $offers = DATA_DIR . '/sync/1c/' . 'offers.xml';
        $this->getSyncService()->importParser($import);
        $this->getSyncService()->offersParser($offers);*/
        $this->getSyncService()->updateTime();

        die('SUCCESS');
    }

    public function indexAction()
    {
        $this->path = DATA_DIR . '/sync/1c/';

        $this->authUser();

        if($_GET['type'] == 'catalog') {
            $this->syncCatalog();
        }

        if($_GET['type'] == 'sale') {
            $this->syncOrders();
        }

        $this->sendHeaders();
    }

    protected function syncOrders()
    {
        $orders = $this->path . 'orders.xml';

        if($_GET['type'] == 'sale') {
            if($_GET['mode'] == 'init') {
                $this->addHeader("zip", "no");
                $this->addHeader("file_limit", 100000000);

                if(file_exists($orders)) { unlink ($orders); }
            }

            if($_GET['mode'] == 'query') {
                header('Content-Type: text/html; charset=cp1251');
                die(iconv("utf-8", "cp1251", $this->getSyncService()->ordersXml()));
            }

            if($_GET['mode'] == 'file') {
                $this->saveFile();
                //$this->getSyncService()->ordersParser($this->path . $_REQUEST['filename']);
                $this->addHeader("success");
            }
        }
    }

    protected function syncCatalog()
    {
        $import = $this->path . 'import.xml';
        $offers = $this->path . 'offers.xml';

        if($_GET['mode'] == 'init') {
            $this->addHeader("zip", "no");
            $this->addHeader("file_limit", 100000000);

            if(file_exists($import)) { unlink($import); }
            if(file_exists($offers)) { unlink($offers); }
        }

        if($_GET['mode'] == 'file') {
            if(!$this->saveFile()) {
                return;
            }

            $this->addHeader("success");
            $this->addHeader($_REQUEST ['filename']);
        }

        if($_GET['mode'] == 'import') {
            $syncService = $this->getSyncService();

            switch ($_REQUEST['filename']) {
                case "import.xml" :
                    if (file_exists($import)) {
                        $syncService->importParser($import);
                        $this->addHeader("success");
                    }
                    break;
                case "offers.xml" :
                    if (file_exists($offers)) {
                        $syncService->offersParser($offers);
                        $this->addHeader("success");
                    }
                    break;
            }
        }
    }

    protected function authUser()
    {
        $cookieFile = $this->path . 'cookie.txt';

        if($_GET['mode'] != 'checkauth') {
            $cookie = file_get_contents($cookieFile);

            if($_COOKIE['secret'] == $cookie) {
                return;
            } else {
                $this->sendHeaders();
            }
        }

        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            $this->sendHeaders();
        }

        $username = trim($_SERVER['PHP_AUTH_USER']);
        $password = trim($_SERVER['PHP_AUTH_PW']);

        $productModule = new  Module();
        $settings = $productModule->setModuleName('Catalog')->setSectionName('Products')->load()->getPlugin('settings');

        if($username != $settings->get('1cLogin') || $password != $settings->get('1cPassword')) {
            $this->sendHeaders();
        }

        $cookie = rand(1, 10000);

        file_put_contents($cookieFile, $cookie);

        $this->addHeader("success");
        $this->addHeader('secret');
        $this->addHeader($cookie);

        $this->sendHeaders();
    }

    protected function sendHeaders()
    {
        die($this->headers);
    }

    protected function addHeader($key, $val = "")
    {
        $this->headers .= $key;

        if(!empty($val)) {
            $this->headers .= "=".$val;
        }

        $this->headers .= "\n";
    }
    protected function saveFile()
    {
        $filename = $_REQUEST['filename'];

        $path = explode('/',$filename);

        array_pop($path);

        if(count($path)) {
            $curDir = $this->path;
            foreach($path as $dir) {
                $curDir .= '/' . $dir;

                if(!file_exists($curDir)) {
                    mkdir($curDir);
                }
            }
        }

        return copy("php://input", $this->path . '/' . $filename);
    }

    public function saveGet($filename = 'resp')
    {
        $message = '';
        $message = $_REQUEST['filename'];

        /*foreach($_GET as $key => $val) {
            $message .= $key . ' = ' . $val . "\n";
        }*/

        file_put_contents(DATA_DIR . '/trash/' . $filename . '.txt', $message);
    }

    /**
     * @return \Catalog\Service\SyncService
     */
    protected function getSyncService()
    {
        return $this->getServiceLocator()->get('Catalog\Service\SyncService');
    }
}