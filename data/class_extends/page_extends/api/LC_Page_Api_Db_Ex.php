<?php

require_once CLASS_REALDIR . 'pages/api/LC_Page_Api_Db.php';

/**
 * API のDBページクラス(拡張).
 *
 * LC_Page_Api をカスタマイズする場合はこのクラスを編集する.
 *
 * @package Page
 * @author LOCKON CO.,LTD.
 * @version $Id$
 */
class LC_Page_Api_Db_Ex extends LC_Page_Api_Db
{
    /**
     * Page を初期化する.
     *
     * @return void
     */
    function init()
    {
        parent::init();
    }

    /**
     * Page のプロセス.
     *
     * @return void
     */
    function process()
    {
        parent::process();
    }
}
