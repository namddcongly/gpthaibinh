function getChecked(name){
	var o=document.getElementsByName(name);
	var i,s,v;
	for(i=0,s='';i < o.length; ++i){
		v=o.item(i);
		if(v.type == "checkbox"){
			if(v.checked) s += ',' + String(v.value);
		}
	}
	return s.substr(1);//remove leading ,
}
function showJQ(id){
	$('#'+id).slideDown('slow', function() {});
}
function hideJQ(id){
	$('#'+id).slideUp('fast', function() {});
}
function showJS(id){	
	var o = document.getElementById(id);	
	if (o.style.visibility =='hidden' || o.style.visibility ==''){
		o.style.visibility='visible';
		o.style.display='block';
	}
	else{
		o.style.visibility='hidden';
	}
	if (o.clientWidth<154) o.style.width= '154px';
}
function hideJS(id){
	var o = document.getElementById(id);	
	o.style.visibility ='hidden';
}
function singleCheckbox(obj){ 
	var o = document.getElementsByName(obj.name);
	var l = o.length;
	for (var i=0;i<l;i++){
		if ((o[i].id!=obj.id)&&o[i].checked) o[i].checked=false; 
	} 
}
function getProperty(n){ // n = 0/1 
	var table=document.getElementById('table-property');
	var o = table.getElementsByTagName('input');
	var l = o.length; var v=0;
	for(var i=0;i<l;i++){ 
		if (o[i].type=='checkbox'){ 
			if ((Number(o[i].id)%2==Number(n))&&(Number(o[i].id)!=0)){ 
				if (o[i].checked) v = v | parseInt(o[i].value); 
			} 
		} 
	} 
	return v; 
}
$(document).ready(function(){
	 $("#checkboxall").click(function()
	  {	  
		   var checked_status = this.checked;
		   
		   $("input[name=checkall]").each(function()
		   {
			this.checked = checked_status;
		   });
	  });	 
});