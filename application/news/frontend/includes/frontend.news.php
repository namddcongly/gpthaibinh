<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
require_once   ROOT_PATH.'system/kernel/includes/single.database.php';
require_once  'application/news/includes/property_news.php';
require_once   UTILS_PATH.'cache.file.php';
class FrontendNews 
{
	private static $dbNews;
	function __construct($config=array ('username' => 'congly', 'password' => 'qTKCMLdxzx7mjX7G', 'host' => 'localhost','host_reserve'=>'localhost', 'dbname' => 'congly_news'))
	{
        $config = array(
            'username' => 'etstech_dev',
            'password' => 'sX5X$qtawp@kYx&^',
            'host' => 'localhost',
            'host_reserve' => 'localhost',
            'dbname' => 'news'
        );
		self::$dbNews=new SingleDatabase($config);
        self::$dbNews->query('SET NAMES "utf8"');
	}
	
	/**
	 * Hàm lấy danh mục
	 * @param  $wh
	 * @param  $order
	 * @param  $limit
	 * @param  $is_cache
	 * @return array();
	 */
	
	function getCategory($wh='',$order='arrange asc',$limit='200',$is_cache=false)
	{
		
		$list_field='id,name,name_display,cate_id1,cate_id2,cate_id3,cate_id4,cate_name1,cate_name2,cate_name3,cate_name4,alias,title,keyword,description,arrange,level,property,icon,layout,block_home,order_cate,number,arrange';
		if($wh)
			$wh=$wh." AND property & 1=1";
		else
			$wh='property & 1 = 1';	
		
		if($is_cache){
			$Cache=new CacheFile();
			$data=$Cache->get(md5($wh.'category'),'',CACHE_FILE_PATH.'news'.DS,300);
			if($data){
				return $data;
			}
		}
		
		$result=self::$dbNews->select('category',$list_field,$wh,$order,$limit,'id');
		if($is_cache){
			$Cache->set(md5($wh.'category'),$result,300,'',CACHE_FILE_PATH.'news'.DS);
		}
		return $result;
	}
	public function readSeo($id)
	{
		settype($id,'int');
		if(!$id) return array();
		return self::$dbNews->selectOne('store_seo','id,title,description','id='.$id);
	}
	/**
	 * Lấy danh sách tin trên trang chủ
	 */
	function getNewsHome($is_cache=false,$list_field='id,nw_id,censor_id,arrange,cate_id,type_post,cate_path,title,description,tag,img1,img2,img3,property,time_public,time_created,is_video',$wh=null)
	{
		if($is_cache){
			$Cache=new CacheFile();
			$data=$Cache->get(md5('store_home'),'',CACHE_FILE_PATH.'news'.DS,300);
			if($data){
				return $data;
			}
		}
		$result=self::$dbNews->select('store_home',$list_field,$wh,'time_public desc',null,'nw_id');
		if($is_cache){
			$Cache->set(md5('store_home'),$result,300,'',CACHE_FILE_PATH.'news'.DS);
		}
		return $result;
	}
	/**
	 * Lấy danh sách các vùng trong danh mục
	 * @param $cate_id
	 * @return array();
	 */
	function getRegionInCategory($cate_ids='',$is_cache=false)
	{
		$list_field='id,cate_id,region_id,skins_type,number_record,arrange,property';
		if($is_cache){
			$Cache=new CacheFile();
			$data=$Cache->get(md5($cate_ids.'region_category'),'',CACHE_FILE_PATH.'news'.DS,300);
			if($data){
				return $data;
			}
		}
		if($cate_ids) $wh="cate_id IN ({$cate_ids}) AND property=1";
		else $wh='property =1';
		$result=self::$dbNews->select('region_category',$list_field,$wh,'arrange asc',null,null);
		if($is_cache)
		{
			$Cache->set(md5($cate_ids.'region_category'),$result,300,'',CACHE_FILE_PATH.'news'.DS);
		}
		return $result;
	}
	/**
	 * Lấy thông tin về vùng
	 * @param $region_ids
	 */
	function getRegion($region_ids='',$order=null,$is_cache=false)
	{
		$list_field='id,name';
		if($is_cache){
			$Cache=new CacheFile();
			$data=$Cache->get(md5($region_ids.'region'),'',CACHE_FILE_PATH.'news'.DS,300);
			if($data){
				return $data;
			}
		}
		if($region_ids) $wh="id IN ({$region_ids})";
		else $wh=null;

		$result=self::$dbNews->select('region',$list_field,$wh,$order,null,'id'); 

		if($is_cache){
			$Cache->set(md5($region_ids.'region'),$result,300,'',CACHE_FILE_PATH.'news'.DS);
		}
		return $result;
	}
	
	/**
	 * Lây danh sach tin ở một bảng bất kỳ
	 * @param unknown_type $table
	 * @param unknown_type $list_field
	 * @param unknown_type $wh
	 * @param unknown_type $order
	 * @param unknown_type $limit
	 * @param unknown_type $key
	 */
	function getNews($table,$list_field,$wh=null,$order=null,$limit=null,$key=null,$is_cache=false)
	{
		
		if($table=='store')
		{
			if($wh == "") $wh='time_public < '.time();
			else $wh.=" AND time_public < ".time();				
		}
		
		if($is_cache){
			$Cache=new CacheFile();
			$data=$Cache->get(md5($wh.$table),'',CACHE_FILE_PATH.'news'.DS,300);
			if($data){
				return $data;
			}
		}
        //echo $wh;
		$result=self::$dbNews->select($table,$list_field,$wh,$order,$limit,$key);
		if($is_cache){
			$Cache->set(md5($wh.$table),$result,300,'',CACHE_FILE_PATH.'news'.DS);
		}
		return $result;	
	}
	/**
	 * Lấy danh sách các tin trong vùng
	 * @param $region_ids
	 */
	function getNewsInRegion($region_ids='',$is_cache=false)
	{
		$list_field='nw_id,region_id,arrange';
		if($is_cache){
			$Cache=new CacheFile();
			$data=$Cache->get(md5($region_ids.'region_store'),'',CACHE_FILE_PATH.'news'.DS,300);
			if($data){
				return $data;
			}
		}
		if($region_ids) $wh="region_id IN ({$region_ids})";
		else $wh=null;
		$result=self::$dbNews->select('region_store',$list_field,$wh,'nw_id DESC',null,null);
		if($is_cache){
			$Cache->set(md5($region_ids.'region_store'),$result,300,'',CACHE_FILE_PATH.'news'.DS);
		}
		return $result;
	}
	public function getPathNews($time)
	{
		if($_SERVER['HTTP_HOST']=='localhost')
			return 'news/'.date('Y',$time).'/'.date('n',$time).'/'.date('j',$time).'/';
		return 'news/'.date('Y',$time).'/'.date('n',$time).'/'.date('j',$time).'/';	
	}
	public function detail($id)
	{
	    settype($id,'int');
		$content = self::$dbNews->selectOne('store_content', 'content', "nw_id=".$id);
		self::$dbNews->query('UPDATE store_hit SET hit=hit+1 WHERE nw_id='.$id);
	    return isset($content['content']) ? $content['content'] : NULL;
	}
	
	public function newsOne($id)
	{
	    $list_field='id,cate_id,cate_path,type_post,title,description,tag,time_created,time_public,author,img1,img2,province_id,img3,img4,file,origin,relate,is_video,is_img';
	    return self::$dbNews->selectOne('store', $list_field, "id=".$id);
	}
	function newsOther($cond = "",$limit='0,10')
	{
		if($cond =="") $cond='time_public < '.time();
	    else $cond.=" AND time_public < ".time();
		return self::$dbNews->select("store", "id AS nw_id,title,cate_id,time_created,description,time_public,img2", $cond,"id DESC",$limit);
	}
	function newsRelate($like = array(), $tag = "")
	{
	    if($tag == "")	    
	    {
	        $leng = count($like);
	        if($leng > 0)	    
	            for($i=0; $i < $leng; $i++)
	               $tag .= ($tag == "" ? "" : " OR ")."(tag like '%".$like[$i]."%')";
	    }
	    
	    if($tag != "")
	       return self::$dbNews->select("store", "nw_id,title,description,time_public", $tag, "id desc", "0,30");
	}
	function showContent($content,$arr_search=array('https://cms.congly.com.vn',"<div","</div>"),$arr_replace=array('http://congly.com.vn',"<p","</p>"))
	{
		
			$content = str_replace($arr_search,$arr_replace,$content);
		
		return $content;	
	
	}
	/**
	 * Lây danh sách id của từng danh mục
	 * @param unknown_type $table
	 * @param unknown_type $cate_ids
	 * @param unknown_type $limit
	 */
	function getIdNewsCate($table,$cate_ids,$order='ORDER BY time_public DESC',$limit=5,$is_cache=false,$ext='')
	{
		
		if($table=='store')
			$sql="SELECT cate_id, SUBSTRING_INDEX(GROUP_CONCAT(id {$order}),',',{$limit})  AS ids FROM {$table} WHERE cate_id IN ($cate_ids) ".($ext != "" ? $ext : "")." AND time_public < ".time()." GROUP BY cate_id";
		else
			$sql="SELECT cate_id, SUBSTRING_INDEX(GROUP_CONCAT(id {$order}),',',{$limit})  AS ids FROM {$table} WHERE cate_id IN ($cate_ids) ".($ext != "" ? $ext : "")." GROUP BY cate_id";
		self::$dbNews->query($sql);
		if($is_cache){
			$Cache=new CacheFile();
			$data=$Cache->get(md5($table.$cate_ids.$order.$limit),'',CACHE_FILE_PATH.'news'.DS,300);
			if($data){
				return $data;
			}
		}
		$data= self::$dbNews->fetchAll('');
		if($is_cache){
			$Cache->set(md5($table.$cate_ids.$order.$limit),$data,300,'',CACHE_FILE_PATH.'news'.DS);
		}
		return $data;
	}
	function searchFullTextNews($keyword,$wh='',$limit='0,20',$accurate=false)
	{
		global $TOTAL_ROWCOUNT;
		$keyword=mysql_escape_string(str_replace(array('\'','"','-'),array('','',''),$keyword));
		$keyword=trim($keyword);
		
		if($wh !="" && !$accurate) $wh =" AND {$wh}";
		
		//if($accurate) $wh.=" AND keyword LIKE '%{$keyword}%'";
		if($keyword)
		{
			#$value = str_replace(" "," +",$keyword);
			$value = $keyword;
			if($accurate)
			{
				$sql="SELECT SQL_CALC_FOUND_ROWS nw_id FROM search WHERE {$wh} ORDER BY nw_id DESC";	
				
			}
			else 
			{
				$order='Priority DESC';
				$sql="SELECT SQL_CALC_FOUND_ROWS nw_id,nw_id, MATCH(keyword) AGAINST('$value' IN BOOLEAN MODE) AS Priority  FROM search WHERE MATCH(keyword) AGAINST('$value' IN BOOLEAN MODE)  {$wh}  ORDER BY Priority DESC,nw_id DESC";
			}

			if($limit)
				$limit=" LIMIT {$limit}";
			else
				$limit="";
			$sql=$sql.$limit;
			self::$dbNews->query($sql);
			$data= self::$dbNews->fetchAll('');
			self::$dbNews->query('SELECT FOUND_ROWS() AS total_rows');
			$row=self::$dbNews->fetchAll('');
			$TOTAL_ROWCOUNT=$row[0]['total_rows'];
			$news_ids='';
			if(count($data))
			{
				$_result=array();
				foreach($data as $_temp)
				{
					$news_ids.=$_temp['nw_id'].',';
				}
				$news_ids=rtrim($news_ids,',');
				$result=$this->getNews('store','id,title,cate_id,img1,description,is_img,is_video,time_created,time_public',"id IN ({$news_ids}) AND time_public > 0",null,null,'id');
                
				foreach($data as $_temp)
				{
					$_result[]=$result[$_temp['nw_id']];
				}
				
				$_result["TOTAL_ROW"] = $TOTAL_ROWCOUNT;
				return $_result;
			}
			return array();
		}
		return array();	
	}
	public function getProvince($is_cache=true)
	{
		
		if($is_cache){
			$Cache=new CacheFile();
			$data=$Cache->get(md5('province'),'',CACHE_FILE_PATH.'news'.DS,36000);
			if($data)
				return $data;
			else
				$result = self::$dbNews->select('province','id,name',null,null,null,'id');
		}
		$result = self::$dbNews->select('province','id,name',null,null,null,'id');
		if($is_cache){
			$Cache->set(md5('province'),$result,36000,'',CACHE_FILE_PATH.'news'.DS);
		}
		return $result;

	}
	public function countRecord($table,$where=null,$key='id')
	{
		return self::$dbNews->count($table,$where,$key);
	}
	public function getNewsReview($id)
	{
		 return self::$dbNews->selectOne('review','*', "id=".$id);
	}
	public function showNavigation($LIST_CATEGORY,$infoNews=array())
	{
		$id=SystemIO::get('id','int',0);
		$cate_id=SystemIO::get('cate_id','int',0);
		if($id)
		{
			if($LIST_CATEGORY[$infoNews['cate_id']]['cate_id1']==0)
				$navigation='<span itemtype="http://data-vocabulary.org/Breadcrumb" itemscope="" class="navigation">
					<a title="'.$LIST_CATEGORY[$infoNews['cate_id']]['name'].'" itemprop="url" href="'.$LIST_CATEGORY[$infoNews['cate_id']]['alias'].'" class="path-a">'.$LIST_CATEGORY[$infoNews['cate_id']]['name'].'</a>
				</span>';
			else
			{
				$navigation='<span itemtype="http://data-vocabulary.org/Breadcrumb" itemscope="" class="navigation">
				<a title="'.$LIST_CATEGORY[$LIST_CATEGORY[$infoNews['cate_id']]['cate_id1']]['name'].'" itemprop="url" href="'.$LIST_CATEGORY[$LIST_CATEGORY[$infoNews['cate_id']]['cate_id1']]['alias'].'" itemprop="url">'.$LIST_CATEGORY[$LIST_CATEGORY[$infoNews['cate_id']]['cate_id1']]['name'].'</a>	
				<a title="'.$LIST_CATEGORY[$infoNews['cate_id']]['name'].'" itemprop="url" href="'.$LIST_CATEGORY[$infoNews['cate_id']]['alias'].'" class="path-a">'.$LIST_CATEGORY[$infoNews['cate_id']]['name'].'</a>
				</span>';
			}
		}
		else
		{
			if($LIST_CATEGORY[$cate_id]['cate_id1']==0)
				$navigation='<span itemtype="http://data-vocabulary.org/Breadcrumb" itemscope="" class="navigation">
					<a title="'.$LIST_CATEGORY[$cate_id]['name'].'" itemprop="url" href="'.$LIST_CATEGORY[$cate_id]['alias'].'" class="path-a">'.$LIST_CATEGORY[$cate_id]['name'].'</a>
				</span>';
			else
			{
				$navigation='<span itemtype="http://data-vocabulary.org/Breadcrumb" itemscope="" class="navigation">
				<a title="'.$LIST_CATEGORY[$LIST_CATEGORY[$cate_id]['cate_id1']]['name'].'" itemprop="url" href="'.$LIST_CATEGORY[$LIST_CATEGORY[$cate_id]['cate_id1']]['alias'].'" itemprop="url">'.$LIST_CATEGORY[$LIST_CATEGORY[$cate_id]['cate_id1']]['name'].'</a>	
				<a title="'.$LIST_CATEGORY[$cate_id]['name'].'" itemprop="url" href="'.$LIST_CATEGORY[$cate_id]['alias'].'" class="path-a">'.$LIST_CATEGORY[$cate_id]['name'].'</a>
				</span>';
			}
		}
        return $navigation;
         
	}
	function link_detail($id, $title){
  		return Url::buildUrlRewrite(array('id' => $id, 'title' => $title), 'news', 'detail');
 	}
 	function showIcon($info,$prefix='')
 	{
 		if($info['is_video'])
 			joc()->set_var('is_video'.$prefix,'webskins/skins/news/images/video.gif');
 		else
 			joc()->set_var('is_video'.$prefix,'');	
 		if($info['is_img'])
 			joc()->set_var('is_img'.$prefix,'webskins/skins/news/images/image.gif');
 		else
 			joc()->set_var('is_img'.$prefix,'');	
 	}
 	function newsFocus($list_field = "*" , $cate_id = 0,$order = "arrange DESC ,hit DESC", $limit="0,10",$wh='',$day=0,$is_cache=false)
 	{
 		$cond = "time_created > ".(time()-86400*$day); // 4 ngÃ y
 		if($cate_id > 0)
 			$cond .= " AND cate_path like '%,$cate_id,%'";
 		if($wh)
 			$cond .=" AND {$wh}";	
 		if($is_cache){
			$Cache=new CacheFile();
			$data=$Cache->get(md5($cate_id.$order.$limit.$wh.'news_focus'),'',CACHE_FILE_PATH.'news'.DS,300);
			if($data){
				return $data;
			}
		}
		$hits = self::$dbNews->select('store_hit',"nw_id",$cond,$order,$limit);
		if(count($hits) > 0)
		{
			$ids = "0";
			
			foreach($hits as $hit)			
				$ids .= ",".$hit["nw_id"]; 

			if($ids != "0")
			{	
				$data=self::$dbNews->select('store',$list_field,"id IN($ids)  AND time_public > 0 AND time_public < ".time(),null,null,'id');
				foreach($hits as $_tmp)
				{
					if($data[$_tmp['nw_id']]['id'])
						$_data[$_tmp['nw_id']]=$data[$_tmp['nw_id']];
				}
				if($is_cache)
				{
					$Cache->set(md5($cate_id.$order.$limit.$wh.'news_focus'),$_data,300,'',CACHE_FILE_PATH.'news'.DS);
				}
				return $_data;
			}
		}	
		return array();
 	}
	function getPoll($poll_id)
	{
		$poll = self::$dbNews->selectOne('poll', "id,name,type", "id=".$poll_id);
		$option = self::$dbNews->select('poll_option',"id,text,voted","poll_id=".$poll_id,"id asc",null);
		
		return array("poll" => $poll,"option" => $option);
	}
}
