<?php
require_once UTILS_PATH.'paging.php';
require(APPLICATION_PATH.'news'.DS."includes".DS."statistic_model.php");
require(APPLICATION_PATH.'news'. DS . 'backend' . DS."includes".DS."backend.news.php");
require(APPLICATION_PATH.'main'. DS."includes".DS."user.php");

class AdminNewsStatistic
{
	// Declaration
	public $total_articles 	= 0;
	public $bai_suu_tam		= 0;
	public $bai_tu_viet		= 0;
	public $bai_dich		= 0;
	public $bai_tong_hop	= 0;
	public $bai_thong_tan	= 0;
	public $tin_video		= 0;
	public $tin_anh			= 0;
	public $tin_thuong		= 0;
	
	function index(){			
		if(!UserCurrent::havePrivilege('ADMIN_STATISTIC')){
			//Url::urlDenied();
		}
        if (!UserCurrent::isLogin()) {
            @header('Location:?app=main&page=admin_login');
        }
		$cmd 	= SystemIO::get('cmd');
		switch($cmd)
		{
			case 'detail':
				$uid = SystemIO::get('uid', 'int', 0);
				return $this->detail($uid);
				break;
				
			case 'advan':
				return $this->advan();
				break;
				
			case 'find_user':
				$keyword = SystemIO::get('keyword', 'str');
				return $this->find_user($keyword);
				break;
			
			default:				
				return $this->listUser();
				break;
		}
	}	
	
	function find_user($keyword){
		dbObject()->setProperty('db','user');
		$sql = "SELECT id, full_name FROM user WHERE full_name LIKE '%{$keyword}%'";
		dbObject()->query($sql);
		$data = dbObject()->fetchAll();
		if(count($data) > 0 ){
			echo '<ul class="list_user">';
			foreach($data as $val){
				echo '<li title="'.$val['id'].'">' . $val['full_name'] . '</li>';
			}
			echo '</ul>';
		}		
		exit();
	}
	
	function detail($uid){					
		// Title
		joc()->set_file('detail', Module::pathTemplate()."backend".DS."news_statistic_detail.htm");
		Page::setHeader("Chi tiết thông tin bài viết của BTV");
		Page::registerFile('statistic.css'		, 'webskins'.DS.'skins'.DS.'news'.DS.'css'.DS.'statistic.css' , 'header', 'css');
		Page::registerFile('date Js'		 	, Module::pathSystemJS().'date.js' , 'header', 'js');
		Page::registerFile('jquery date Js'		, Module::pathSystemJS().'jquery.datepicker.min.js' , 'header', 'js');
		Page::registerFile('date Css'		 	, Module::pathSystemCSS().'datepicker.css' , 'header', 'css');	
		Page::registerFile('chart.css'		 	, 'webskins'.DS.'skins'.DS.'news'.DS.'css'.DS.'chart.css' , 'header', 'css');	
		Page::registerFile('jquery_002.js'		, 'webskins'.DS.'skins'.DS.'news'.DS . 'js' . DS .'jquery_002.js' , 'header', 'js');	
		Page::registerFile('jquery_chart.js'		, 'webskins'.DS.'skins'.DS.'news'.DS . 'js' . DS .'jquery_chart.js' , 'header', 'js');
		Page::registerFile('jqplot_002.js'		, 'webskins'.DS.'skins'.DS.'news'.DS . 'js' . DS .'jqplot_002.js' , 'header', 'js');

		joc()->set_var('main_title', 'Quản lý thống kê bài viết');

		joc()->set_var('uid', $uid);
		$objUser=new User();
		
		$list_user=$objUser->userIdToName($uid);
		joc()->set_var('main_title', 'Quản lý thống kê bài viết của : ' . $list_user[$uid]['user_name']);
		
		$newsObj=new BackendNews();
		$list_category=$newsObj->getListCategory('cate_id1=0','',100,'id');
		joc()->set_var('option_category',SystemIO::getOption(SystemIO::arrayToOption($list_category,'id','name'),$cate_id));
		$item_per_page=20;
		$page_no=SystemIO::get('page_no','int',1);
		if ($page_no<1) $page_no=1;
		$stt=($page_no-1)*$item_per_page+1;
		$limit=(($page_no-1)*$item_per_page).','.$item_per_page;
		$stt=($page_no-1)*$item_per_page+1;
		/*tim kiem*/
		$type_post=SystemIO::get('type_post','int',10);
		$array_type=array('0'=>'Sưu tầm','1'=>'Thông tấn xã','2'=>'Dịch','3'=>'Tự viết','4'=>'Tổng hợp');
		$check_radio='';
		foreach($array_type as $k=>$v)
		{
			if($type_post==$k)
				$check_radio.='<input type="radio"  name="type_post" value="'.$k.'" checked="checked"/>'.$v;
			else
				$check_radio.='<input type="radio"  name="type_post" value="'.$k.'" />'.$v;
		}
		joc()->set_var('type_post_search',$check_radio);
		
		$wh='user_id='.$uid.' AND time_public < '.time().' AND time_public > 0';
		$date_begin=SystemIO::get('date_begin','def');
		joc()->set_var('date_begin',$date_begin);
		if($type_post < 10)
		$wh.=" AND type_post=".$type_post;
		if($date_begin)
		{
			$date_begin=(int)strtotime(str_replace('/','-',$date_begin));
			$wh.= " AND time_created >= {$date_begin}";
			
		}
		$cate_id=SystemIO::get('cate_id','int',0);
		if($cate_id)
			$wh.=" AND cate_path LIKE '%,{$cate_id},%'";
			
		$date_end=SystemIO::get('date_end','def');
		joc()->set_var('date_end',$date_end);
		if($date_end)
		{
			$date_end=strtotime(str_replace('/','-',$date_end));
			$wh.= " AND time_created <= {$date_end}";
		}
			
		$result=$newsObj->getListStore($wh,'time_public DESC',$limit,'id');
		$news_ids='';
		$cate_ids='';
		foreach($result as $_tmp)
		{
			$news_ids.=$_tmp['id'].',';
			$cate_ids.=$_tmp['cate_id'].',';
		}
		
		$list_hit = $newsObj->getListNewsHit(rtrim($news_ids,','));
		$list_path = $newsObj->getMultiPathNews(rtrim($cate_ids,','));				
		
		$html_region = '';
		joc()->set_block('detail', 'LIST');

		$array_type = array('0'=>'Sưu tầm','1'=>'Thông tấn xã','2'=>'Dịch','3'=>'Tự viết','4'=>'Tổng hợp');

		$check = 0;
		foreach ($result as $reg)
		{									
			// is video
			$is_video = $reg['is_video'] == 1 ? 'Có' : 'Không';	
			// is image
			$is_img = $reg['is_img'] == 1 ? 'Có' : 'Không';
			joc()->set_var('stt',$stt);
			++$stt;
			joc()->set_var('id', $reg['id']);
			joc()->set_var('title', $reg['title']);
			joc()->set_var('date_created', date('d/m/Y', $reg['time_created']));
			joc()->set_var('hit', $list_hit[$reg['id']]['hit']);
			joc()->set_var('description', $reg['description']);
			joc()->set_var('category', $list_path[$reg['cate_id']]);

			joc()->set_var('type_post', $array_type[$reg['type_post']]);

			joc()->set_var('type_post', $array_type[(int)$reg['type_post']]);

			joc()->set_var('is_video', $is_video);				
			joc()->set_var('is_img', $is_img);
			joc()->set_var('tag',$reg['tag']);
			$html_region .= joc()->output('LIST');									
				
		}	
		joc()->set_var('LIST', $html_region);
		joc()->set_var('html_region', $html_region);
		// Render
		global $TOTAL_ROWCOUNT;
		$totalRecord=$TOTAL_ROWCOUNT;
		joc()->set_var('total_rowcount',$totalRecord);
		joc()->set_var('pagging','<li>Tổng số: '.$totalRecord.'</li>'.Paging::paging ($totalRecord,$item_per_page,10));
		/*thong ke*/
		$sql_total_video="SELECT COUNT(*) AS total FROM store WHERE is_video=1 AND ". $wh;
		$sql_total_img="SELECT COUNT(*) AS total FROM store WHERE is_img=1 AND ".$wh;
		$sql_total_other="SELECT COUNT(*) AS total FROM store WHERE is_img!=1 AND is_video!=1 AND ".$wh;
		dbObject()->setProperty('news','store');
		dbObject()->query($sql_total_video);
		$total_video=dbObject()->fetch();
		dbObject()->query($sql_total_img);
		$total_img=dbObject()->fetch();
		dbObject()->query($sql_total_other);
		$total_other=dbObject()->fetch();		
		joc()->set_var('total_record',$totalRecord);
		joc()->set_var('total_type_video',$total_video['total']);
		joc()->set_var('total_type_img',$total_img['total']);
		joc()->set_var('total_type_normal',$total_other['total']);	
		
		$sql_type_ttx="SELECT COUNT(*) AS total FROM store WHERE type_post=1 AND ".$wh;
		dbObject()->query($sql_type_ttx);
		$ttx=dbObject()->fetch();
		$sql_type_tv="SELECT COUNT(*) AS total FROM store WHERE type_post=3 AND ".$wh;
		dbObject()->query($sql_type_tv);		
		$tv=dbObject()->fetch();
		$sql_type_dich="SELECT COUNT(*) AS total FROM store WHERE type_post=2 AND ".$wh;
		dbObject()->query($sql_type_dich);
		$dich=dbObject()->fetch();
		$sql_type_th="SELECT COUNT(*) AS total FROM store WHERE type_post=4 AND ".$wh;
		dbObject()->query($sql_type_th);
		$th=dbObject()->fetch();
		joc()->set_var('total_type_tuviet',$tv['total']);
		joc()->set_var('total_type_dich',$dich['total']);
		joc()->set_var('total_type_tonghop',$th['total']);
		joc()->set_var('total_type_ttx',$ttx['total']);	
		joc()->set_var('total_type_sutam',$totalRecord-($tv['total']+$dich['total']+$th['total']+$ttx['total']));	
		
		$html= joc()->output("detail");
		joc()->reset_var();
		return $html;	
	}	
	
	function listUser()
	{			
		// Search
		joc()->set_file('statistic', Module::pathTemplate()."backend".DS."news_statistic.htm");
		Page::registerFile('statistic.css'		, 'webskins'.DS.'skins'.DS.'news'.DS.'css'.DS.'statistic.css' , 'header', 'css');
		Page::registerFile('date Js'		 	, Module::pathSystemJS().'date.js' , 'header', 'js');
		Page::registerFile('jquery date Js'		, Module::pathSystemJS().'jquery.datepicker.min.js' , 'header', 'js');
		Page::registerFile('date Css'		 	, Module::pathSystemCSS().'datepicker.css' , 'header', 'css');	
		Page::registerFile('chart.css'		 	, 'webskins'.DS.'skins'.DS.'news'.DS.'css'.DS.'chart.css' , 'header', 'css');	
		Page::registerFile('jquery_002.js'		, 'webskins'.DS.'skins'.DS.'news'.DS . 'js' . DS .'jquery_002.js' , 'header', 'js');	
		Page::registerFile('jquery_chart.js'		, 'webskins'.DS.'skins'.DS.'news'.DS . 'js' . DS .'jquery_chart.js' , 'header', 'js');
		Page::registerFile('jqplot_002.js'		, 'webskins'.DS.'skins'.DS.'news'.DS . 'js' . DS .'jqplot_002.js' , 'header', 'js');
		Page::setHeader("Quản lý thống kê bài viết");
		joc()->set_var('main_title', 'Quản lý thống kê bài viết');
		
		$objUser=new User();
		$list_user=$objUser->getList('1=1','','0,50','id');
		// ------------------------------------------
		$list_user_id='';
		foreach($list_user as $_tmp)
		{
			$list_user_id.=$_tmp['id'].',';
		}
		$list_user_id=rtrim($list_user_id,',');
		$sql_list_total="SELECT COUNT(*) AS total, user_id FROM store WHERE user_id IN($list_user_id) AND time_public > 0 AND time_public < ".time()." GROUP  BY user_id";
		dbObject()->setProperty('news','store');
		dbObject()->query($sql_list_total);
		$list_total=dbObject()->fetchAll('user_id');
		
		$totalRecord=0;	
		$html_region = '';
		joc()->set_block('statistic', 'LIST','LIST');
		$count=0;
		foreach ($list_user as $reg)
		{						
			if(isset($list_total[$reg['id']]['total']) && $list_total[$reg['id']]['total'] > 0){
				joc()->set_var('count', ++$count);	
				joc()->set_var('id', $reg['id']);
				joc()->set_var('user_name', $reg['user_name']);	
				joc()->set_var('fullname', $reg['full_name']);				
				joc()->set_var('email', $reg['email']);
				joc()->set_var('total',(int)$list_total[$reg['id']]['total']);
				$totalRecord+=(int)$list_total[$reg['id']]['total'];
				joc()->set_var('phone', isset($reg['mobile_phone']) ? $reg['mobile_phone'] : (isset($reg['phone']) ? $reg['phone'] : ''));
				$html_region .= joc()->output('LIST');
			}
		}
		$sql_total_video="SELECT COUNT(*) AS total FROM store WHERE is_video=1";
		$sql_total_img="SELECT COUNT(*) AS total FROM store WHERE is_img=1";
		
		dbObject()->query($sql_total_video);
		$total_video=dbObject()->fetch();
		
		dbObject()->query($sql_total_img);
		$total_img=dbObject()->fetch();
		
		$total_other=$totalRecord-$total_img['total']-$total_video['total'];
		joc()->set_var('total_record',$totalRecord);
		joc()->set_var('total_type_video',$total_video['total']);
		joc()->set_var('total_type_img',$total_img['total']);
		
		$sql_type_ttx="SELECT COUNT(*) AS total FROM store WHERE type_post=1";
		dbObject()->query($sql_type_ttx);
		$ttx=dbObject()->fetch();
		$sql_type_tv="SELECT COUNT(*) AS total FROM store WHERE type_post=3";
		dbObject()->query($sql_type_tv);		
		$tv=dbObject()->fetch();
		$sql_type_dich="SELECT COUNT(*) AS total FROM store WHERE type_post=2";
		dbObject()->query($sql_type_dich);
		$dich=dbObject()->fetch();
		$sql_type_th="SELECT COUNT(*) AS total FROM store WHERE type_post=4";
		dbObject()->query($sql_type_th);
		$th=dbObject()->fetch();
		joc()->set_var('total_type_tuviet',$tv['total']);
		joc()->set_var('total_type_dich',$dich['total']);
		joc()->set_var('total_type_tonghop',$th['total']);
		joc()->set_var('total_type_ttx',$ttx['total']);	
		joc()->set_var('total_type_sutam',$totalRecord-($tv['total']+$dich['total']+$th['total']+$ttx['total']));	
		joc()->set_var('total_type_normal',$total_other);		
		joc()->set_var('LIST', $html_region);	
		
		// Render
		$html= joc()->output("statistic");
		joc()->reset_var();
		return $html;		
	}
	
	function advan(){	
		// Title
		joc()->set_file('detail', Module::pathTemplate()."backend".DS."news_statistic_advan.htm");
		Page::setHeader("Thống kê nâng cao");
		Page::registerFile('statistic.css'		, 'webskins'.DS.'skins'.DS.'news'.DS.'css'.DS.'statistic.css' , 'header', 'css');
		Page::registerFile('date Js'		 	, Module::pathSystemJS().'date.js' , 'header', 'js');
		Page::registerFile('jquery date Js'		, Module::pathSystemJS().'jquery.datepicker.min.js' , 'header', 'js');
		Page::registerFile('date Css'		 	, Module::pathSystemCSS().'datepicker.css' , 'header', 'css');	
		Page::registerFile('chart.css'		 	, 'webskins'.DS.'skins'.DS.'news'.DS.'css'.DS.'chart.css' , 'header', 'css');	
		Page::registerFile('jquery_002.js'		, 'webskins'.DS.'skins'.DS.'news'.DS . 'js' . DS .'jquery_002.js' , 'header', 'js');	
		Page::registerFile('jquery_chart.js'		, 'webskins'.DS.'skins'.DS.'news'.DS . 'js' . DS .'jquery_chart.js' , 'header', 'js');
		Page::registerFile('jqplot_002.js'		, 'webskins'.DS.'skins'.DS.'news'.DS . 'js' . DS .'jqplot_002.js' , 'header', 'js');
		joc()->set_var('main_title', 'Thống kê nâng cao');
		
		// Search
		$search = SystemIO::get('act','str');
		if($search == 'search'){				
			$cate_id 	= SystemIO::get('cate_id', 'int', 0);	
			$cate_pid 	= SystemIO::get('cate_pid', 'int', 0);
			$uid 		= SystemIO::get('uid', 'int', 0);
			$wh_user	= '';			
			$wh_time 	= '';
			$dateStart	= SystemIO::get('date_begin', 'str');
			$dateEnd	= SystemIO::get('date_end', 'str');
			$dateEnd	= empty($dateStart) && empty($dateEnd) ? date('d/m/Y') : $dateEnd;		
			$dateStart	= str_replace('/','-',urldecode($dateStart));
			$dateEnd	= str_replace('/','-',urldecode($dateEnd));								
			$timeStart 	= (int)strtotime($dateStart . " 00:00:00");
			$timeEnd	= (int)strtotime($dateEnd . " 23:59:59");			
			if(!empty($dateStart) && !empty($dateEnd)){			
				$wh_time .= " AND time_public >= {$timeStart} AND time_public <= {$timeEnd}";			
			}elseif(!empty($dateStart) && empty($dateEnd)){
				$wh_time .= " AND time_public >= {$timeStart}";
			}elseif(empty($dateStart) && !empty($dateEnd)){
				$wh_time .= " AND time_public <= {$timeEnd}";
			}	
						
			$wh_time_total = !empty($wh_time) ? ' WHERE ' . substr(trim($wh_time), 3) : '';		
			
			if($uid > 0){
				$wh_user .= " AND user_id={$uid}";
				$wh_user_total = !empty($wh_time_total) ? ' AND user_id=' . $uid : ' WHERE user_id=' . $uid;
			}	
			
			joc()->set_var('display', 'block');
		}else{
			joc()->set_var('display', 'none');
		}	
		
		dbObject()->setProperty('news','category');
		$sql = 	"
				SELECT 	a.id, a.cate_id1, a.name, b.total, 
						is_video.total 	as total_video,
						is_img.total 	as total_img,
						bth.total		as total_bth,
						btv.total		as total_btv,
						bd.total 		as total_bd,
						bttx.total		as total_bttx,
						bst.total		as total_bst						
				FROM category as a
				LEFT JOIN 
					(SELECT COUNT(*) as total, cate_id FROM store {$wh_time_total} {$wh_user_total} GROUP BY cate_id) as b
				ON a.id = b.cate_id	
				LEFT JOIN
					(SELECT COUNT(*) as total, cate_id FROM store WHERE is_video=1 {$wh_user} {$wh_time} GROUP BY cate_id) as is_video
				ON a.id = is_video.cate_id				
				LEFT JOIN
					(SELECT COUNT(*) as total, cate_id FROM store WHERE is_img=1 {$wh_user} {$wh_time} GROUP BY cate_id) as is_img
				ON a.id = is_video.cate_id				
				LEFT JOIN
					(SELECT COUNT(*) as total, cate_id FROM store WHERE type_post=0 {$wh_user} {$wh_time} GROUP BY cate_id) as bst
				ON a.id = bst.cate_id				
				LEFT JOIN
					(SELECT COUNT(*) as total, cate_id FROM store WHERE type_post=1 {$wh_user} {$wh_time} GROUP BY cate_id) as bttx
				ON a.id = bttx.cate_id				
				LEFT JOIN
					(SELECT COUNT(*) as total, cate_id FROM store WHERE type_post=2 {$wh_user} {$wh_time} GROUP BY cate_id) as bd
				ON a.id = bd.cate_id				
				LEFT JOIN
					(SELECT COUNT(*) as total, cate_id FROM store WHERE type_post=3 {$wh_user} {$wh_time} GROUP BY cate_id) as btv
				ON a.id = btv.cate_id				
				LEFT JOIN
					(SELECT COUNT(*) as total, cate_id FROM store WHERE type_post=4 {$wh_user} {$wh_time} GROUP BY cate_id) as bth
				ON a.id = bth.cate_id	
				ORDER BY a.name ASC				
				";
						
			
		$current_page = (SystemIO::get('page_no', 'int', 0) <= 1) ? 1 : SystemIO::get('page_no', 'int', 0);		
		$item_per_page = 20;
		$limit = 'LIMIT ' . (($current_page-1) * $item_per_page) . ',20';	
		dbObject()->query($sql);
		$result = dbObject()->fetchAll();		
		
		$list_cate_id = '';
		foreach($result as $row){
			$list_cate_id .= $row['id'] . ',';
		}
		$list_cate_id = rtrim($list_cate_id, ',');
		

		if($cate_pid >= 0){
			$list_cate_id = '';
			// lay toan bo id cua danh muc con thuoc cate_id
			$sql = "SELECT id FROM category WHERE cate_id1={$cate_id}";
			dbObject()->query($sql);
			$list_id_cate = dbObject()->fetchAll();
			if(count($list_id_cate) > 0){
				foreach($list_id_cate as $val){
					$list_cate_id .= $val['id'] . ',';
				}
			}else{
				$list_cate_id .= $cate_id;
			}		
			
			$list_cate_id = rtrim($list_cate_id, ',');
			
			
			$sql = "SELECT id, title, description, cate_id, cate_path, is_video, is_img, type_post, time_public, tag
					FROM store WHERE cate_id IN ({$list_cate_id}) {$wh_user} {$wh_time} {$limit}";	
		}else{
			$sql = "SELECT id, title, description, cate_id, cate_path, is_video, is_img, type_post, time_public, tag
					FROM store {$wh_time_total} {$wh_user_total} {$limit}";
		}
		
	
		dbObject()->query($sql);
		$articles = dbObject()->fetchAll();
		
		$modelBE = new BackendNews();
		$news_ids='';
		$cate_ids='';
		foreach($articles as $_tmp)
		{
			$news_ids.=$_tmp['id'].',';
			$cate_ids.=$_tmp['cate_id'].',';
		}
		$list_hit = $modelBE->getListNewsHit(rtrim($news_ids,','));
		$list_path = $modelBE->getMultiPathNews(rtrim($cate_ids,','));
				
		$html_region = '';
		joc()->set_block('detail', 'LIST_ARTICLE');	
		$array_type = array('0'=>'Sưu tầm','1'=>'Thông tấn xã','2'=>'Dịch','3'=>'Tự viết','4'=>'Tổng hợp');
		$stt = ($current_page-1) * $item_per_page + 1;
		
		foreach($articles as $row)
		{			
			joc()->set_var('title', $row['title']);
			joc()->set_var('description', $row['description']);
			joc()->set_var('tag',$row['tag']);
			$is_video = $row['is_video'] == 1 ? 'Có' : 'Không';	
			joc()->set_var('is_video', $is_video);
			$is_img = $row['is_img'] == 1 ? 'Có' : 'Không';
			joc()->set_var('is_img', $is_img);
			joc()->set_var('type_post', $array_type[(int)$row['type_post']]);
			joc()->set_var('date_public', date('d/m/Y', $row['time_public']));
			joc()->set_var('hit', (int)$list_hit[$row['id']]['hit']);
			joc()->set_var('category', $list_path[$row['cate_id']]);
			joc()->set_var('stt',$stt);
			$html_region .= joc()->output('LIST_ARTICLE');		
			++$stt;
		}
		
		joc()->set_var('LIST_ARTICLE', $html_region);
						
		$data = array();
		foreach($result as $row){
			array_push($data, 
				array(
						'id' 			=> $row['id'], 
						'pid' 			=> $row['cate_id1'], 
						'name' 			=> $row['name'], 
						'total' 		=> ($row['total'] > 0) 			? $row['total'] 		: 0, 
						'total_video' 	=> ($row['total_video'] > 0) 	? $row['total_video'] 	: 0,
						'total_img' 	=> ($row['total_img'] > 0) 		? $row['total_img']		: 0,								
						'total_bth' 	=> ($row['total_bth'] > 0)		? $row['total_bth']		: 0,
						'total_btv' 	=> ($row['total_bth'] > 0)		? $row['total_bth']		: 0,
						'total_bd' 		=> ($row['total_bd'] > 0)		? $row['total_bd']		: 0,
						'total_bttx' 	=> ($row['total_bttx'] > 0)		? $row['total_bttx']	: 0,
						'total_bst' 	=> ($row['total_bst'] > 0)		? $row['total_bst']		: 0,
						'total_tt'		=> (($row['total'] - $row['total_video'] - $row['total_img']) > 0) ? $row['total'] - $row['total_video'] - $row['total_img'] : 0
				)
			);
		}	
		
		$root 			= new Node(0, -1, 'Tất cả');
		$arrParent 		= array(0 => $root);
		$arrChildren 	= array();
				
		foreach($data as $val){
			$id 			= $val['id'];
			$name 			= $val['name'];
			$pid 			= $val['pid'];
			$total 			= $val['total'];			
			$total_bst 		= $val['total_bst'];
			$total_btv 		= $val['total_btv'];
			$total_bd 		= $val['total_bd'];
			$total_bth 		= $val['total_bth'];
			$total_bttx 	= $val['total_bttx'];
			$total_video 	= $val['total_video'];
			$total_img 		= $val['total_img'];
			$total_tt		= $total  - $total_video - $total_img;
			
			$newNode = new Node($id, $pid, $name, $total, $total_video, $total_img, $total_tt, $total_bst, $total_btv, $total_bd, $total_bth, $total_bttx);
			
			$arrParent[$id] = $newNode;
			
			$children = &$arrChildren[$pid];
			if(!isset($children)){
				$arrChildren[$pid] = array();
			}		
			$children[$id] = $newNode;
			
			$children = &$arrChildren[$id];
			if(isset($children)){
				$newNode->children = $children;
			}
			
			$parent = &$arrParent[$pid];
			if(isset($parent)){
				if(!isset($parent->children)){
					$parent->children = array();
				}
				
				$parent->children[$id] = $newNode;
			}
		}
		
		$current_root = $root;
		if($cate_id > 0){
			if($cate_pid > 0){
				$current_root = $current_root->children[$cate_pid]->children[$cate_id];
			}else{
				$current_root = $current_root->children[$cate_id];
			}	
		}		
		$this->calculator($current_root);		
	
		$list_category_html = '';
		$list_category_html .= '<select id="cate_id">';
			$list_category_html .= $this->printTree($root, $cate_id);
		$list_category_html .= '</select>';
		
		joc()->set_var('list_category_html', $list_category_html);
		SystemIO::get('date_begin', 'str') 	? joc()->set_var('date_begin', urldecode(SystemIO::get('date_begin', 'str'))) : joc()->set_var('date_begin','01/01/2011');
		SystemIO::get('date_end', 'str') 	? joc()->set_var('date_end', urldecode(SystemIO::get('date_end', 'str'))) : joc()->set_var('date_end', date('d/m/Y'));
		joc()->set_var('total_articles', $this->total_articles);
		joc()->set_var('bai_suu_tam', $this->bai_suu_tam);
		joc()->set_var('bai_tu_viet', $this->bai_tu_viet);
		joc()->set_var('bai_dich', $this->bai_dich);
		joc()->set_var('bai_tong_hop', $this->bai_tong_hop);
		joc()->set_var('bai_thong_tan', $this->bai_thong_tan);
		joc()->set_var('tin_video', $this->tin_video);
		joc()->set_var('tin_img', $this->tin_img);
		joc()->set_var('tin_thuong', $this->tin_thuong);	
		
		joc()->set_var('pagging','<li>Tổng số: '.$this->total_articles.'</li>'.Paging::paging ($this->total_articles,$item_per_page,10));	
		
		// Render
		$html= joc()->output("detail");
		joc()->reset_var();
		return $html;	
	}	
	
	function calculator($root){		
		$this->total_articles 	+= $root->total;
		$this->bai_suu_tam		+= $root->total_bst;
		$this->bai_tu_viet		+= $root->total_btv;
		$this->bai_dich			+= $root->total_bd;
		$this->bai_tong_hop		+= $root->total_bth;
		$this->bai_thong_tan	+= $root->total_bttx;
		$this->tin_video		+= $root->total_video;
		$this->tin_img			+= $root->total_img ;
		$this->tin_thuong		+= $root->total_tt;
				
		if(isset($root->children)>0){
		   foreach($root->children as $child){
				$showTree .= $this->calculator($child);
		   }
		}
	}	
	
	function printTree($root, $id = 0){				
		$font_weight 	= ($root->pid == 0 )		? ' font-weight:bold;' : '';
		$color 			= ($root->id == $id) 		? ' color:#D9251D;' : '';				
		$select 		= ($root->id == $id) 		? ' selected="selected"' : '';
		$split 			= ($root->pid > 0 )			? '|' . str_repeat("__", 1) : '';	
		
		$style = 'style="' . $font_weight . $color . '"';
		
		$showTree = '<option value="' . $root->id . '"' . 'pid="' . $root->pid . '"' . $style . $select . '>' . $split . $root->name ;					
					if(isset($root->children)>0){
					   foreach($root->children as $child){
							$showTree .= $this->printTree($child, $id);
					   }
					}			
		$showTree .= '</option>';		
		return $showTree;
	}
}

class Node{
	public $id 			= NULL;
	public $name 		= NULL;	
	public $children 	= NULL;
	public $pid 		= NULL;
	public $total 		= 0;
	public $total_video = 0;
	public $total_img 	= 0;
	public $total_tt 	= 0;
	public $total_bst 	= 0;
	public $total_btv	= 0;
	public $total_bd	= 0;
	public $total_bth	= 0;
	public $total_bttx	= 0;
	
	public function Node($id=0, $pid=0, $name='root', $total=0, $total_video=0, $total_img=0, $total_tt=0, $total_bst=0, $total_btv=0, $total_bd=0, $total_bth=0, $total_bttx=0)
	{
		$this->id 			= $id;
		$this->name 		= $name;		
		$this->pid			= $pid;
		$this->total		= $total;
		$this->total_video 	= $total_video;
		$this->total_img 	= $total_img;
		$this->total_tt 	= $total_tt;
		$this->total_bst 	= $total_bst;
		$this->total_btv 	= $total_btv;
		$this->total_bd 	= $total_bd;
		$this->total_bth 	= $total_bth;
		$this->total_bttx 	= $total_bttx;
	}
}
?>