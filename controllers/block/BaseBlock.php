<?php
/**
 * FecShop file.
 *
 * @link http://www.fecshop.com/
 * @copyright Copyright (c) 2016 FecShop Software LLC
 * @license http://www.fecshop.com/license/
 */

namespace buddysoft\widget\controllers\block;

use Yii;
use yii\base\BaseObject;

/**
 * @author Terry Zhao <2358269014@qq.com>
 * @since 1.0
 */
class BaseBlock extends BaseObject  implements BlockInterface
{
    /**
     * parameter storage front passed.
     */
    public $_param = [];
    /**
     * fecshop service.
     */
    public $_service;
    /**
     * default pages number.
     */
    public $_pageNum = 1;
    /**
     * collection default number displayed.
     */
    public $_numPerPage = 50;
    /**
     * collection primary key.
     */
    public $_primaryKey;

    /**
     * collection sort direction , the default value is 'desc'.
     */
    public $_sortDirection = 'desc';
    /**
     * collection sort field , the default value is primary key.
     */
    public $_orderField;

    public $_asArray = true;
    /**
     * current url with param,like http://xxx.com?p=3&sort=desc.
     */
    public $_currentParamUrl;
    /**
     * current url with no param,like http://xxx.com.
     */
    public $_currentUrlKey;
    /**
     * data edit url, if you not set value ,it will be equal to current url.
     */
    public $_editUrl;
    /**
     * data delete url, if you not set value ,it will be equal to current url.
     */
    public $_deleteUrl;
    public $_currentUrl;

    /**
     * 为兼容 Yii 2.0.36 \yii\base\Controller 引入的 request 属性
     *
     * @var [type]
     */
    public $request;

    /**
     * it will be execute during initialization ,the following object variables will be initialize.
     * $_primaryKey , $_param , $_currentUrl ,
     * $_currentParamUrl , $_addUrl , $_editUrl,
     * $_deleteUrl.
     */
    public function init()
    {
        parent::init();
        if (!($this instanceof BlockInterface)) {
            echo  'Manager  must implements BackendBlockInterface';
            exit;
        }

        $this->request = Yii::$app->request;
    }

    /**
     * generate pager form html, it is important to j-ui js framework, which will storage current request param as hidden way.
     * @return $str|string , html format string.
     */
    public function getPagerForm()
    {
        $str = '';
        if (is_array($this->_param) && !empty($this->_param)) {
            foreach ($this->_param as $k=>$v) {
                if ($k != '_csrf') {
                    $str .= '<input type="hidden" name="'.$k.'" value="'.$v.'">';
                }
            }
        }

        return $str;
    }

    /**
     * @param $data|Array, it was return by defined function getSearchArr();
     * generate search section html,
     */
    public function getSearchBarHtml($data)
    {
        if (is_array($data) && !empty($data)) {
            $r_data = [];
            $i = 0;
            foreach ($data as $k=>$d) {
                $type11 = $d['type'];
                if ($type11 == 'select') {
                    $value = $d['value'];
                    $name = $d['name'];
                    $title = $d['title'];
                    $d['value'] = $this->getSearchBarSelectHtml($name, $value, $title);
                } elseif ($type11 == 'chosen_select') {
                    $i++;
                    $value = $d['value'];
                    $name = $d['name'];
                    $title = $d['title'];
                    $d['value'] = $this->getSearchBarChosenSelectHtml($name, $value, $title, $i);
                }
                $r_data[$k] = $d;
            }
        }
        $searchBar = $this->getDbSearchBarHtml($r_data);

        return $searchBar;
    }

    /**
     * @param $name|string , html code select name.
     * @param $data|Array,  select options key and value.
     * @param $title|string , select title , as select default display.
     * generate html select code .
     * @return String, select html code.
     */
    public function getSearchBarSelectHtml($name, $data, $title)
    {
        if (is_array($data) && !empty($data)) {
            $html_chosen_select = '<select class="combox" name="'.$name.'">';
            $html_chosen_select .= '<option value="">'.$title.'</option>';
            $selected = $this->_param[$name];
            if (is_array($selected)) {
                $selected = $selected['$regex'];
            }
            foreach ($data as $k=>$v) {
                if ($selected == $k) {
                    $html_chosen_select .= '<option selected="selected" value="'.$k.'">'.$v.'</option>';
                } else {
                    $html_chosen_select .= '<option value="'.$k.'">'.$v.'</option>';
                }
            }
            $html_chosen_select .= '</select>';

            return $html_chosen_select;
        } else {
            return '';
        }
    }

    public function getLastData()
    {
        return [];
    }
}
