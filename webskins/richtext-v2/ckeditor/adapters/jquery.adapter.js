/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

(function(){CKEDITOR.config.jqueryOverrideVal=typeof CKEDITOR.config.jqueryOverrideVal=='undefined'?true:CKEDITOR.config.jqueryOverrideVal;var a=window.jQuery;if(typeof a=='undefined')return;a.extend(a.fn,{ckeditorGet:function(){var b=this.eq(0).data('ckeditorInstance');if(!b)throw 'CKEditor not yet initialized, use ckeditor() with callback.';return b;},ckeditor:function(b,c){if(!a.isFunction(b)){var d=c;c=b;b=d;}c=c||{};this.filter('textarea, div, p').each(function(){var e=a(this),f=e.data('ckeditorInstance'),g=e.data('_ckeditorInstanceLock'),h=this;if(f&&!g){if(b)b.apply(f,[this]);}else if(!g){if(c.autoUpdateElement||typeof c.autoUpdateElement=='undefined'&&CKEDITOR.config.autoUpdateElement)c.autoUpdateElementJquery=true;c.autoUpdateElement=false;e.data('_ckeditorInstanceLock',true);f=CKEDITOR.replace(h,c);e.data('ckeditorInstance',f);f.on('instanceReady',function(i){var j=i.editor;setTimeout(function(){if(!j.element){setTimeout(arguments.callee,100);return;}i.removeListener('instanceReady',this.callee);j.on('dataReady',function(){e.trigger('setData.ckeditor',[j]);});j.on('getData',function(l){e.trigger('getData.ckeditor',[j,l.data]);},999);j.on('destroy',function(){e.trigger('destroy.ckeditor',[j]);});if(j.config.autoUpdateElementJquery&&e.is('textarea')&&e.parents('form').length){var k=function(){e.ckeditor(function(){j.updateElement();});};e.parents('form').submit(k);e.parents('form').bind('form-pre-serialize',k);e.bind('destroy.ckeditor',function(){e.parents('form').unbind('submit',k);e.parents('form').unbind('form-pre-serialize',k);});}j.on('destroy',function(){e.data('ckeditorInstance',null);});e.data('_ckeditorInstanceLock',null);e.trigger('instanceReady.ckeditor',[j]);if(b)b.apply(j,[h]);},0);},null,null,9999);}else CKEDITOR.on('instanceReady',function(i){var j=i.editor;setTimeout(function(){if(!j.element){setTimeout(arguments.callee,100);return;}if(j.element.$==h)if(b)b.apply(j,[h]);},0);},null,null,9999);});return this;}});if(CKEDITOR.config.jqueryOverrideVal)a.fn.val=CKEDITOR.tools.override(a.fn.val,function(b){return function(c,d){var e=typeof c!='undefined',f;this.each(function(){var g=a(this),h=g.data('ckeditorInstance');if(!d&&g.is('textarea')&&h){if(e)h.setData(c);else{f=h.getData();return null;}}else if(e)b.call(g,c);else{f=b.call(g);return null;}return true;});return e?this:f;};});})();

//tool bar fulle
$(document).ready(function(){
	$('textarea.editor_tiny').ckeditor({ skin : 'v2', toolbar : 'TinyToolBar' ,
	});	
	$('textarea.editor_basic').ckeditor({ skin : 'v2', toolbar : 'BasicToolBar' ,
	});
	$('textarea.footballconfig').ckeditor({ skin : 'v2', toolbar : 'BasicToolBar'});
	$('textarea.editor_full').ckeditor({ skin : 'v2', toolbar : 'FullToolBar' ,
		filebrowserBrowseUrl : '/webskins/richtext/ckeditor/plugins/kfm/index.html',
		filebrowserImageUploadUrl : '/webskins/richtext/ckeditor/plugins/uploader/upload.php?type=Images'


	});
	$('textarea.news_editor_full').ckeditor({ skin : 'v2', toolbar : 'FullToolBar' , width : 587 , height : 350 ,scayt_autoStartup : false , resize_minHeight : 350 , resize_minWidth : 530 ,resize_maxWidth : 530,
		filebrowserBrowseUrl : 'webskins/richtext/ckeditor/plugins/kfm/index.html',
		filebrowserImageUploadUrl : 'webskins/richtext/ckeditor/plugins/uploader/upload.php?type=Images'
		

	});
});

function open_KCFinderMulti() {

	var oEditor = CKEDITOR.instances.content;
	
	
	window.KCFinder = {
		callBackMultiple: function(files) {
			window.KCFinder = null;
			var multiurl = '';
			var textarea;
			for (var i = 0; i < files.length; i++) {
            	if ( i == 0 )
                	multiurl += '<img src="' + files[i] + '" alt="" />';
                else
                	multiurl += "\n" + '<img src="' + files[i] + '" alt="" />';
            }
			
			if ( oEditor.mode == 'wysiwyg' )
			{
				// Insert HTML code.
				// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.editor.html#insertHtml
				oEditor.insertHtml( multiurl );
			}
			else
				alert( 'You must be in WYSIWYG mode!' );	
		}
	};
	window.open('http://cms.congly.com.vn/webskins/richtext/kcfinder/browse.php?type=images',
		'kcfinder_multiple', 'status=0, toolbar=0, location=0, menubar=0, ' +
		'directories=0, resizable=1, scrollbars=0, width=800, height=600'
	);
}
function open_FKCFinderMulti() {

	var oEditor = CKEDITOR.instances.content;
	
	
	window.KCFinder = {
		callBackMultiple: function(files) {
			window.KCFinder = null;
			var multiurl = '';
			var textarea;
			for (var i = 0; i < files.length; i++) {
            	if ( i == 0 )
                	multiurl += '<a href="' + files[i] + '">' + files[i] + '</a>';
                else
                	multiurl += "\n" + '<a href="' + files[i] + '">' + files[i] + '</a>';
            }
			
			if ( oEditor.mode == 'wysiwyg' )
			{
				// Insert HTML code.
				// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.editor.html#insertHtml
				oEditor.insertHtml( multiurl );
			}
			else
				alert( 'You must be in WYSIWYG mode!' );	
		}
	};
	window.open('http://cms.congly.com.vn/webskins/richtext/kcfinder/browse.php?type=file',
		'kcfinder_multiple', 'status=0, toolbar=0, location=0, menubar=0, ' +
		'directories=0, resizable=1, scrollbars=0, width=800, height=600'
	);
}