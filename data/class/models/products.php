<?php
include_once(CLASS_REALDIR."models/database_list.php");

class ProductList extends DatabaseList
{

    public $indexes;
    public $products;

    public function __construct()
    {
        parent::__construct();
        $this->setBaseQuery();
    }
    
    public function filterByCategoryID($category_id = 0)
    {
        if($category_id > 0)
        {
            $searchCategories = SC_Helper_DB_Ex::sfGetCatWhere($category_id);
            $this->filter($searchCategories[0], $searchCategories[1] ,false);
        }
    }

    public function filterByVenderID($vender_id = 0)
    {
        if($vender_id > 0)
        {
            $this->filter("psi.vender_id", $category_id );
        }
    }

    public function filterByKeywords($keywords = "")
    {
        if($keywords != "")
        {
            $keywords = explode(" ", str_replace("　"," ",$keywords));
            foreach($keywords as $keyword){
                $this->filter("psi.name LIKE ?", "%".$keyword."%" , false );
            }
        }
    }
    
    public function orderBy($str)
    {
        switch($str){
            case "date":
                $str = "psi.create_date DESC";
                break;
            case "price":
                $str = "psi.price ASC";
                break;
            default:
                break;
        }
        parent::orderBy($str);
    }

    private function setBaseQuery()
    {
        $this->col = "*";
        $this->from = "dtb_products_search_index as psi INNER JOIN dtb_product_categories as cat ON cat.product_index_id = psi.product_index_id";
        $this->where = "psi.del_flg = 0";
        $this->groupBy = "";
        $this->orderBy = "cat.rank DESC";
    }
    
    public function get()
    {
        $productIndexes = parent::get();
        $this->from = "dtb_products";
        $this->order = "product_index_id asc";
        if(count($productIndexes) > 0){
            $product_ids = array();
            foreach($productIndexes as $p){
                $product_ids[] = $p["product_index_id"];
            }
            $this->where = "product_index_id IN ( ".implode(" , ", $product_ids)." )";
            $this->limit(0,0);
            $rs = parent::get();
            $products = array();
            foreach($rs as $p){
                if(!isset($products[$p["product_index_id"]])){
                    $products += array($p["product_index_id"]=>array($p));
                }else{
                    $products[$p["product_index_id"]][] = $p;
                }
            }
            $this->products = $products;
        }
        $this->indexes = $productIndexes;
    }
    
}