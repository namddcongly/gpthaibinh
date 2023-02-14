<?php
$action=SystemIO::post('action','def','');
switch($action)
{
	case 'refresh_captcha':
		require_once UTILS_PATH.'captchar.php';
		$captcha= new Captcha(4);
		$type = SystemIO::post('type','def', 'VerifyImage');
		$src=$captcha ->getCaptcha(false,false, $type);
		echo '<img src="'.$src.'" alt="captchar" class="img-capcha" />';
		break;
	case 'post_question':
		require_once UTILS_PATH.'captchar.php';
		require 'application/news/includes/question_of_user_model.php';
		$questionObj=new QuestionOfUser();
		$captcha= new Captcha(4);
		$captcha_code=SystemIO::post('captcha_code','def');

		if($captcha->checkCaptcha($captcha_code,'post_question'))
		{
			$title=SystemIO::post('title','def');
			$email=SystemIO::post('email','def');
			$content=SystemIO::post('content','def');
			if($title=='' || $email =='' || $content =='')
			{
				echo -1;
				exit();
			}
			else
			{
				$arrayNewsData=array('title'=>$title,'email'=>$email,'content'=>$content,'time_created'=>time(),'user_name'=>$email);
				if($questionObj->insertData($arrayNewsData))
				echo 1;
				else
				echo -1;
			}
		}
		else
		echo 0;
		break;
			
}