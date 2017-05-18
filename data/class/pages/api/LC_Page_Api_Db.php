<?php

require_once CLASS_EX_REALDIR . 'page_extends/LC_Page_Ex.php';
require_once CLASS_EX_REALDIR . 'api_extends/SC_Api_Db_Ex.php';

/**
 * APIのDBページクラス.
 *
 * @package Page
 * @author LOCKON CO.,LTD.
 * @version $Id$
 */
class LC_Page_Api_Db extends LC_Page_Ex
{
    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        parent::init();
    }

    /**
     * Page のプロセス.
     *
     * @return void
     */
    public function process()
    {
        $this->action();
    }

    /**
     * Page のアクション.
     *
     * @return void
     */
    public function action()
    {
        if (API_ENABLE_FLAG == false) {
            $arrErr['ECCUBE.Function.Disable'] = 'API機能が無効です。';
            echo 'API機能が無効です。'; // TODO:今後ちゃんと
        } else {
            $json = $_REQUEST;

            $objApiDb = new SC_Api_Db_Ex();
 
            $method = $_SERVER["REQUEST_METHOD"];

            switch ($method) {
                case 'GET':
                    echo $objApiDb->get($json);
                    break;
                case 'POST':
                    echo $objApiDb->post($json);
                    break;
                case 'PUT':
                    echo $objApiDb->put($json);
                    break;
                case 'DELETE':
                    echo $objApiDb->delete($json);
                    break;
                default:
            SC_Response_Ex::actionExit();
            }
        }
    }
}
