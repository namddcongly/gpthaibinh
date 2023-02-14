<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
class StatisticModel extends DatabaseObject 
{
	function getListUser($wh = '', $offset = 0, $limit = 15)
	{	
		$this->setProperty('db', 'user');	
		$list_fields = 'id, user_name, full_name, phone, mobile_phone, email';	
		
		$sql = "SELECT SQL_CALC_FOUND_ROWS {$list_fields} FROM user {$wh} ORDER BY full_name ASC limit $offset, $limit";	
		
		
		$this->query($sql);			
		$list_user = $this->fetchAll();	
		
		if(count($list_user) > 0){		
			// Get total article by user
			$this->unsetProperty();
			$this->setProperty('news', 'store');
			
			
			$result 	= array();
			$list_uid 	= '';
			foreach($list_user as $user){
				$list_uid .= $user['id'] . ",";
				$result[$user['id']] = $user;
			}
			
			$list_uid = rtrim($list_uid, ',');
			// Get total article by user
			$this->unsetProperty();
			$this->setProperty('news', 'store');
			
			$sql="SELECT COUNT(*) as total, user_id FROM store WHERE user_id IN ({$list_uid}) GROUP BY user_id";		
			$this->query($sql);
			$list_article = $this->fetchAll();
			
			foreach($list_article as $article){
				$result[$article['user_id']]['total'] = $article['total'];
			}
		}else{
			$result = array();
		}	
		
		return $result;		
	}
	
	function get_total_user($wh = ''){
		$this->setProperty('db', 'user');		
		$sql = "SELECT SQL_CALC_FOUND_ROWS id FROM user {$wh}";	
		$this->query($sql);			
		$list_user = $this->fetchAll();
		return count($list_user);
	}
	

	/*
	$sql = 	"
			SELECT SQL_CACHE CONCAT(f.name, '#', f.cate_name1, '#', cate_name2, '#', cate_name3, '#', cate_name4) as cate_name, g.*
			FROM category as f
			INNER JOIN	(
						SELECT d.hit, e.*
						FROM store_hit as d
						INNER JOIN	(
									SELECT a.id, cate_id, title, type, time_public, time_created, b.content
									FROM store as a
									INNER JOIN store_content as b
									ON a.id = b.nw_id
									WHERE {$wh} {$limited}
									) as e
						ON d.nw_id = e.id				
						) as g
			ON f.id = g.cate_id			
			";	
	*/
	
	function getArticleByUser($wh = '', $offset = 0, $limit = 15){
		$this->setProperty('news', 'store');
		
		// GET list article
		$limited = ' LIMIT ' . $offset . ', ' . $limit;			
		$sql = "
				SELECT id, cate_id, title, description, type, time_public, time_created, type_post, is_video, is_img
				FROM store 
				{$wh} 
				ORDER BY time_created DESC
				{$limited}
				";			
						
		$this->query($sql);
		$list_articles = $this->fetchAll();	
		
		/*
		echo "<pre>";
			print_r($list_articles);
		echo "</pre>";
		exit();
		*/
		
		
		return $list_articles;					
	}
	
	function get_total_article_by_user($wh = ''){		
		$this->setProperty('news', 'store');
		$sql = "SELECT id, is_video, is_img, type_post as type_post FROM store {$wh}";
		$this->query($sql);	
		return $this->fetchAll();
	}
	
		
	function getUserInfo($uid = 0, $wh = ''){
		$dbName 	= 'db';
		$tblName 	= 'user';
		$this->setProperty($dbName, $tblName);
		if((int)$uid > 0){		
			$result = $this->select('full_name', 'id=' . $uid);
			return $result[0]['full_name'];
		}else{
			$sql = "SELECT id FROM user {$wh}";
			$this->query($sql);
			$result = $this->fetchAll();
			return $result;
		}	
	}
	
}





