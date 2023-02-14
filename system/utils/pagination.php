<?php

class Pagination
{	
	public $li_open        = "<li";
	public $li_close       = "</li>";
	public $open           = "<ul class=\"paging clearfix\">";
	public $close          = "</ul>";
	public $a_open         = "<a href=";
	public $a_close        = "</a>";
	public $portal		   = "main";
	public $pagename	   = "home";
	public $class_current  = "paging-active";
	public $current_li     = false;
	public $preview        = "Trước";
	public $next           = "Sau";
	public $ajax           = true;
    public $page           = 0;
    public $number		   = 2; // số lượng hiển thị độ dài phân trang
	public $total          = 0; // tổng số lượng bản ghi
	public $per_page       = 20; // số lượng hiển thị trên 1 trang
	
	function __construct(){}
    /**
     * phân trang có dạng trước trước 1 2 3 sau
     *
     * @param unknown_type $ext
     * @return unknown
     */
	function create0($ext)
	{
	    $total_page = (int)($this->total%$this->per_page == 0 ? ($this->total/$this->per_page) : ($this->total/$this->per_page) + 1);
	    
	    $paging = "";
	    if($total_page > 1)
	    {
	        $paging .= $this->open;
	        
	        $start = $this->page > $this->number ? $this->page - $this->number : 0;
	        
	        $end = ($this->page + $this->number) < $total_page ? ($this->page + $this->number) + 1 : $total_page;
	        
	        if($this->page > 1 )
	           $paging .= $this->li_open.'>'.$this->a_open.'"">'.$this->preview.$this->a_close.$this->li_close;	           
	        
	        for($i = $start; $i < $end; ++$i)
	        {
	            $paging .= $this->li_open.(($this->current_li && $this->page == $i) ? ' class="'.$this->class_current.'">' : '>');
	            
	            $link = "#";
	            
	            $paging .= $this->a_open.'"'.$link.'" '.((!$this->current_li && $this->page == $i) ? ' class="'.$this->class_current.'">' : '>').($i+1).$this->a_close;
	            
	            $paging .= $this->li_close;
	        }
	        
	        if($this->page < $total_page )
	           $paging .= $this->li_open.'>'.$this->a_open.'"">'.$this->next.$this->a_close.$this->li_close;
	           
	        $paging .= $this->close;
	    }
	    return $paging;
	}	
	/**
     * phân trang có dạng trước trước 1 ... 5 6 7 ... 100 sau
     *
     * @param unknown_type $ext
     * @return unknown
     */
	function create1($ext)
	{
	    $total_page = (int)($this->total%$this->per_page == 0 ? ($this->total/$this->per_page) : ($this->total/$this->per_page) + 1);
	    
	    $paging = "";
	    
	    if($total_page > 1)
	    {
	        $paging .= $this->open;
	        
	        $start = $this->page > $this->number ? $this->page - $this->number : 0;
	        
	        $end = ($this->page + $this->number) < $total_page ? ($this->page + $this->number) + 1 : $total_page;
	        
	        if($this->page > 0 )
	        {
	        	$link = "index.php?portal=".$this->portal.'&page='.$this->pagename."&page_no=".$this->page.$ext;
	            $paging .= $this->li_open.'>'.$this->a_open.'"'.$link.'">'.$this->preview.$this->a_close.$this->li_close;	           
	        }

	        if($this->page > $this->number)   
	        {
	        	$link = "index.php?portal=".$this->portal.'&page='.$this->pagename."&page_no=1".$ext;
	            $paging .= $this->li_open.'>'.$this->a_open.'"'.$link.'"> 1 '.$this->a_close.$this->li_close.$this->li_open.'>'.' ... '.$this->li_close;
	        }

	        for($i = $start; $i < $end; ++$i)
	        {
	            $paging .= $this->li_open.(($this->current_li && $this->page == $i) ? ' class="'.$this->class_current.'">' : '>');
	            
	            $link = "index.php?portal=".$this->portal.'&page='.$this->pagename."&page_no=".($i+1).$ext;
	            
	            $paging .= $this->a_open.'"'.$link.'" '.((!$this->current_li && $this->page == $i) ? ' class="'.$this->class_current.'">' : '>').($i+1).$this->a_close;
	            
	            $paging .= $this->li_close;
	        }

	        if($this->page + $this->number + 1 < $total_page)
	        {
	        	$link = "index.php?portal=".$this->portal.'&page='.$this->pagename."&page_no=".$total_page.$ext;
	            $paging .= $this->li_open.'>'.' ... '.$this->li_close.$this->li_open.'>'.$this->a_open.'"'.$link.'">'.$total_page.$this->a_close.$this->li_close;
	        }   
	        if($this->page < $total_page - 1)
	        {
	        	$link = "index.php?portal=".$this->portal.'&page='.$this->pagename."&page_no=".($this->page+2).$ext;
	            $paging .= $this->li_open.'>'.$this->a_open.'"'.$link.'">'.$this->next.$this->a_close.$this->li_close;
	        }

	        $paging .= $this->close;
	    }
	    return $paging;
	}
	function create1_ajax($ext)
	{
	    $total_page = (int)($this->total%$this->per_page == 0 ? ($this->total/$this->per_page) : ($this->total/$this->per_page) + 1);
	    
	    $paging = "";
	    
	    if($total_page > 1)
	    {
	        $paging .= $this->open;
	        
	        $start = $this->page > $this->number ? $this->page - $this->number : 0;
	        
	        $end = ($this->page + $this->number) < $total_page ? ($this->page + $this->number) + 1 : $total_page;
	        
	        if($this->page > 0 )
	        {
	        	$link = "ajax.php?path=".$this->portal.'&fnc='.$this->pagename."&page_no=".$this->page.$ext;
	            $paging .= $this->li_open.'>'.$this->a_open.'"'.$link.'" class="ajax">'.$this->preview.$this->a_close.$this->li_close;	           
	        }

	        if($this->page > $this->number)   
	        {
	        	$link = "ajax.php?path=".$this->portal.'&fnc='.$this->pagename."&page_no=1".$ext;
	            $paging .= $this->li_open.'>'.$this->a_open.'"'.$link.'" class="ajax"> 1 '.$this->a_close.$this->li_close.$this->li_open.'>'.' ... '.$this->li_close;
	        }

	        for($i = $start; $i < $end; ++$i)
	        {
	            $paging .= $this->li_open.(($this->current_li && $this->page == $i) ? ' class="'.$this->class_current.'">' : '>');
	            
	            $link = "ajax.php?path=".$this->portal.'&fnc='.$this->pagename."&page_no=".($i+1).$ext;
	            
	            $paging .= $this->a_open.'"'.$link.'" '.((!$this->current_li && $this->page == $i) ? ' class="ajax '.$this->class_current.'">' : 'class="ajax">').($i+1).$this->a_close;
	            
	            $paging .= $this->li_close;
	        }

	        if($this->page + $this->number + 1 < $total_page)
	        {
	        	$link = "ajax.php?path=".$this->portal.'&fnc='.$this->pagename."&page_no=".$total_page.$ext;
	            $paging .= $this->li_open.'>'.' ... '.$this->li_close.$this->li_open.'>'.$this->a_open.'"'.$link.'" class="ajax">'.$total_page.$this->a_close.$this->li_close;
	        }   
	        if($this->page < $total_page - 1)
	        {
	        	$link = "ajax.php?path=".$this->portal.'&fnc='.$this->pagename."&page_no=".($this->page+2).$ext;
	            $paging .= $this->li_open.'>'.$this->a_open.'"'.$link.'" class="ajax">'.$this->next.$this->a_close.$this->li_close;
	        }

	        $paging .= $this->close;
	    }
	    return $paging;
	}
	function create1_rewrite($ext)
	{
	    $total_page = (int)($this->total%$this->per_page == 0 ? ($this->total/$this->per_page) : ($this->total/$this->per_page) + 1);
	    
	    $paging = "";
	    
	    if($total_page > 1)
	    {
	        $paging .= $this->open;
	        
	        $start = $this->page > $this->number ? $this->page - $this->number : 0;
	        
	        $end = ($this->page + $this->number) < $total_page ? ($this->page + $this->number) + 1 : $total_page;
	        
	        if($this->page > 0 )
	        {
	        	$link = $ext."/trang-".$this->page."/";
	            $paging .= $this->li_open.'>'.$this->a_open.'"'.$link.'">'.$this->preview.$this->a_close.$this->li_close;	           
	        }

	        if($this->page > $this->number)   
	        {
	        	$link = $ext;
	            $paging .= $this->li_open.'>'.$this->a_open.'"'.$link.'"> 1 '.$this->a_close.$this->li_close.$this->li_open.'>'.' ... '.$this->li_close;
	        }

	        for($i = $start; $i < $end; ++$i)
	        {
	            $paging .= $this->li_open.(($this->current_li && $this->page == $i) ? ' class="'.$this->class_current.'">' : '>');
	            
	            $link = $ext."/trang-".($i+1)."/";
	            
	            $paging .= $this->a_open.'"'.$link.'" '.((!$this->current_li && $this->page == $i) ? ' class="'.$this->class_current.'">' : '>').($i+1).$this->a_close;
	            
	            $paging .= $this->li_close;
	        }

	        if($this->page + $this->number + 1 < $total_page)
	        {
	        	$link = $ext."/trang-".$total_page."/";
	            $paging .= $this->li_open.'>'.' ... '.$this->li_close.$this->li_open.'>'.$this->a_open.'"'.$link.'">'.$total_page.$this->a_close.$this->li_close;
	        }   
	        if($this->page < $total_page - 1)
	        {
	        	$link = $ext."/trang-".($this->page+2)."/";
	            $paging .= $this->li_open.'>'.$this->a_open.'"'.$link.'">'.$this->next.$this->a_close.$this->li_close;
	        }

	        $paging .= $this->close;
	    }
	    return $paging;
	}
	/**
	 * phân trang kiểu select box
	 *
	 * @param unknown_type $ext
	 * @return unknown
	 */
	function create_select($ext)
	{
	    $total_page = (int)($this->total%$this->per_page == 0 ? ($this->total/$this->per_page) : ($this->total/$this->per_page) + 1);
	    
	    $paging = "";
	    if($total_page > 1)
	    {
	        $paging .= $this->open;
	        	        
	        for($i = 0; $i < $total_page; ++$i)	        
	            $paging .= $this->li_open.' value="'.($i+1).'"'.(($this->current_li && $this->page == $i) ? ' selected="selected">' : '>').$this->current_li.($i+1).$this->li_close;
	            
	        $paging .= $this->close.' / '.$total_page. ' Trang';
	    }
	    return $paging;
	}	
	/**
	 * phân trang kiểu input
	 *
	 * @param unknown_type $ext
	 * @return unknown
	 */
	function create_input($ext)
	{
	    $total_page = (int)($this->total%$this->per_page == 0 ? ($this->total/$this->per_page) : ($this->total/$this->per_page) + 1);
	    
	    $paging = "";
	    if($total_page > 1)
	    {
	        $paging .= $this->open;
	        	        
	        for($i = 0; $i < $total_page; ++$i)	        
	            $paging .= $this->li_open.' value="'.($i+1).'"'.(($this->current_li && $this->page == $i) ? ' selected="selected">' : '>').$this->current_li.($i+1).$this->li_close;
	            
	        $paging .= $this->close.' / '.$total_page. ' Trang';
	    }
	    return $paging;
	}
}
/***********************************************************/
//USAGE//
/***********************************************************/
/*$page_no = isset($_GET['page_no']) ? (int)$_GET['page_no'] : 1;

$paging = new Paging();

$paging->total = 101;
$paging->page = $page_no;
$paging->number = 2;

echo $paging->create0("");
echo $paging->create1("");

$paging->open = '<select id="paging">';
$paging->close = '</select>';
$paging->li_close = '</option>';
$paging->li_open = '<option';
$paging->current_li = ' Trang ';

echo $paging->create_select("");*/

?>