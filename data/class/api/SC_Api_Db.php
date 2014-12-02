<?php
// TODO: DBのAPI実装

class SC_Api_Db
{
    function __construct()
    {   /** TODO /api/db/[table名]/~ の形で実装する.
        $uri = $_SERVER['REQUEST_URI'];
        $pattern = "#^/api/db/(.+)/$#";
        if (preg_match($pattern, $uri, $match)) {
            // mtb_があるのでこの辺は要件等
            $table = 'dtb_' . $match[1];
        } else {
            // 形式に沿っていないREQUEST
            echo "Error:";
        }
        **/
    }

    public function get($arrParam)
    {
        $objQuery =& SC_Query_Ex::getSingletonInstance();
        list($cols, $from, $where, $arrWhereVal) = $this->getParse($arrParam);
        $value = $objQuery->select($cols, $from, $where, $arrWhereVal);
        
        return SC_Utils::jsonEncode($value);
    }

    public function post($arrParam)
    {
        //echo "POST";
        //        $objQuery =& SC_Query_Ex::getSingletonInstance();
        //        $cols = '';
        //        $from = '';
        //        $value = $objQuery->insert($table, $arrVal, $arrSql, $arrSqlVal, $from, $arrFromVal);
    }

    public function put($arrParam)
    {
        //echo "PUT";
    }

    public function delete($arrParam)
    {
        //echo "DELETE";
    }

    public function getParse($arrParam)
    {
        $obj = SC_Utils::jsonDecode($arrParam['json']);
        
        $limit = $obj->limit;                    // 検索条件 LIMT
        $offset = $obj->offset;                  // 検索条件 OFFSET
        $sort = get_object_vars($obj->sort);     // 検索条件 DESC, ASC
        $filter = get_object_vars($obj->filter); // 検索条件 WHERE
        $include = $obj->include;                // 取得するカラム
        
        $where = '';
        $arrWhereVal = '';

        // TODO: sortとlimitの追加         
        foreach ($filter as $key => $value) {
            if ($where) {
                 $where .= " AND {$key} = ? ";
                 $arrWhereVal = $value;
            } else {
                 $where .= "{$key} = ? ";
                 $arrWhereVal .= $value;
            }
        }
        if ($arrWhereVal) {
            $arrWhereVal = array($arrWhereVal);
        }
        $cols =  implode(",", $include);

        // TODO:
        // api/db/cutomer/~ でREQUESTが来た場合を想定.
        // customerの部分は、任意で指定できるようにする.
        $from = 'dtb_customer';

        return array($cols, $from, $where, $arrWhereVal);
    }
}
