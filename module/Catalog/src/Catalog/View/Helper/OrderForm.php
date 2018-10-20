<?php
namespace Catalog\View\Helper;

use Application\Model\Region;
use Aptero\String\Date;
use Delivery\Model\Delivery;
use Zend\View\Helper\AbstractHelper;

class OrderForm extends AbstractHelper
{
    public function __invoke($price)
    {
        if(!$price) {
            return '';
        }

        $timeOptions = '';
        for($i = 10; $i <= 21; $i++) {
            $timeOptions .= '<option>' . $i . ':00</option>';
        }

        $region = Region::getInstance();
        $delivery = Delivery::getInstance();

        $pickupDate = $this->pickupDate();

        $html =
            '<div class="order-box order-form">'
                .'<form action="/order/add-order/" method="post" class="form-box step">'
                    .'<div class="box region">'
                        .'<div class="row">Выбран регион: <a href="/regions/" class="popup">' . $region->get('name') . '</a></div>'
                    .'</div>'

                    .'<div class="box personal">'
                        .'<div class="row">'
                            .'<input class="std-input" name="attrs-name" placeholder="ФИО">'
                        .'</div>'
                        .'<div class="row">'
                            .'<input class="std-input phone" name="phone" placeholder="+7 (___) ___-__-__">'
                        .'</div>'
                    .'</div>';

        if($delivery->getId()) {
            $html .=
                    '<div class="delivery courier" data-type="courier">'
                        .'<div class="title">'
                            .'Курьерская доставка'
                        .'</div>'

                        .'<div class="box">'
                            .'<div class="row">'
                                .'<input class="std-input" placeholder="Адрес доставки" name="attrs-address">'
                            .'</div>'

                            .'<div class="row">'
                                .'<input class="std-input of-datepicker" placeholder="Дата доставки" name="attrs-date">'
                            .'</div>'

                            .'<div class="row">'
                                .'<select class="std-select time" name="attrs-time_from">'
                                    .'<option>Время с</option>'
                                    .$timeOptions
                                .'</select>'

                                .'<select class="std-select time" name="attrs-time_to">'
                                    .'<option>По</option>'
                                    .$timeOptions
                                .'</select>'
                            .'</div>'
                        .'</div>'
                    .'</div>'
                    .'<div class="delivery pickup" data-type="pickup">'
                        .'<div class="title">'
                            .'Самовывоз'
                        .'</div>'

                        .'<div class="box">'
                            .'<div class="row-delivery">'
                                .'Дата доставки: <span>' . $pickupDate->format('d.m.Y') . ' (' . Date::$weekDays[$pickupDate->format('N')] . ') 15:30</span>'
                            .'</div>'
                            .'<div class="row">'
                                .'<input type="hidden" name="attrs-point" value="">'
                                .'<span href="/delivery/points/" class="chose-pickup popup">Выбрать точку самовывоза</span>'
                            .'</div>'
                        .'</div>'
                    .'</div>';

            } else {
                $html .=
                    '<div class="delivery post" data-type="post">'
                        .'<div class="title">'
                            .'Почта России'
                        .'</div>'
                        .'<div class="box">'
                            .'<div class="row-delivery">'
                                .'Доставка почтой доступна только по предоплате'
                            .'</div>'
                            .'<div class="row">'
                                .'<input class="std-input" placeholder="Индекс" name="attrs-index">'
                            .'</div>'
                            .'<div class="row">'
                                .'<input class="std-input" placeholder="Адрес доставки" name="attrs-address">'
                            .'</div>'
                        .'</div>'
                    .'</div>';
            }
            
            $html .=
                    '<div><input type="hidden" name="attrs-delivery" value=""></div>'
                    .'<div class="box summary">'
                        .'<div class="title">Итого</div>'
                        .'<div class="row">'
                            .'<div class="label">Товары:</div>'
                            .'<span class="price"><span class="cart-price"></span> <i class="fas fa-ruble-sign"></i></span>'
                        .'</div>'
                        .'<div class="row">'
                            .'<div class="label">Доставка:</div>'
                            .'<span class="price"><span class="cart-delivery"></span> </span>'
                        .'</div>'

                        .'<div class="row sum">'
                            .'<div class="label">Всего к оплате:</div>'
                            .'<span class="price"><span class="cart-sum-price"></span> <i class="fas fa-ruble-sign"></i></span>'
                        .'</div>'
                        .'<div class="cart-error">Мин. стоимость заказа 400 р</div>'
                        .'<input type="submit" class="btn order-btn" value="Оформить заказ">'
                    .'</div>'
                .'</form>'
                .'<div class="phone-verification step">'
                    .'<div class="title">Подверждение номера</div>'
                    .'<div class="notice">Введите код из SMS</div>'
                    .'<input class="std-input code" placeholder="••••" maxlength="4">'
                    .'<div class="wrong-code"></div>'

                    .'<div class="error-box">'
                        .'<div class="trigger">Не приходит SMS?</div>'
                        .'<div class="help">'
                            .'<div class="text">Если вы не получили код подтверждения, наш сотрудник перезвонит вам в течении часа и подтвердит заказ.</div>'
                            .'<span class="btn continue">Продолжить без кода</span>'
                        .'</div>'
                    .'</div>'
                .'</div>'

                .'<div class="order-processed step">'
                    .'<div class="title">Заказ оформлен</div>'
                    .'<div class="body"></div>'
                .'</div>'
            .'</div>';

        $html .= $this->datepicker();

        return $html;
    }

    protected function pickupDate()
    {
        $date = new \DateTime();

        switch ($date->format('N')) {
            case 1: $deliveryDelay = 2; break;
            case 5: $deliveryDelay = 3; break;
            case 6: $deliveryDelay = 2; break;
            case 7: $deliveryDelay = 1; break;
            default:
                if($date->format('H') < 15) {
                    $deliveryDelay = 1;
                } else {
                    $deliveryDelay = 2;
                }
                break;
        }

        $delivery = Delivery::getInstance();
        $deliveryDelay += $delivery->get('delay');

        $date->modify('+ ' . $deliveryDelay . ' days');

        return $date;
    }

    protected function datepicker()
    {
        $region = Region::getInstance();
        //if($region->get('name') != 'Санкт-Петербург') {
            $date = new \DateTime();

            switch ($date->format('N')) {
                case 1: $deliveryDelay = 2; break;
                case 5: $deliveryDelay = 3; break;
                case 6: $deliveryDelay = 2; break;
                case 7: $deliveryDelay = 1; break;
                default:
                    if($date->format('H') < 15) {
                        $deliveryDelay = 1;
                    } else {
                        $deliveryDelay = 2;
                    }
                    break;
            }

            $delivery = Delivery::getInstance();
            $deliveryDelay += $delivery->get('delay');

            $start = (new \DateTime());
            $start->modify('-5 hours');

            $interval = new \DateInterval('P1D');

            $end = clone $start;
            $end->modify('+1 month');

            $period = new \DatePeriod($start, $interval, $end);
            $excludDates = [];

            foreach ($period as $day) {
                if ($day->format('N') == 7) {
                    $excludDates[] = $day->format('d.m.Y');
                }
            }

        $html =
            '<script>
                $.getScript(libs.jqueryUi, function() {
                var options = $.config.datepicker;
                
                options.minDate = ' . $deliveryDelay . ';
                
                var dates = ["' . implode('", "', $excludDates) . '"];
                
                options.beforeShowDay = function(date){
                    var string = jQuery.datepicker.formatDate(\'dd.mm.yy\', date);
                    return [dates.indexOf(string) == -1]
                }
                
                $(".of-datepicker").datepicker(options);
            });
            </script>';

        return $html;
    }
}