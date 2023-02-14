
/*
 * @author: Ken Phan <kenphan19@gmail.com>
 * @copyright: Ken Phan <http://kenphan.com>
 * @license: http://www.opensource.org/licenses/gpl-license.php
 * @description: KFManager skins
 */

function KFMSkins () {
	this.start = function () {
		 var html = 
		'<div id="kfmrmenu">' +
			'<ul>' +
				'<li id="kfmrmenuView"><img src="'+ that.s.url.kfm +'clientscript/images/file-view.gif" style="display:none" />' +
					'<a href="javascript:void(0);">View</a></li>' +
				'<li id="kfmrmenuDownload"><img src="'+ that.s.url.kfm +'clientscript/images/file-download.gif" style="display:none" />' +
					'<a href="javascript:void(0);">Download</a></li>' +
				'<li id="kfmrmenuRename"><img src="'+ that.s.url.kfm +'clientscript/images/none.gif" style="display:none" />' +
					'<a href="javascript:void(0);">Rename</a></li>' +
				'<li id="kfmrmenuEdit"><img src="'+ that.s.url.kfm +'clientscript/images/none.gif" style="display:none" />' +
					'<a href="javascript:void(0);">Edit</a></li>' +
				'<li id="kfmrmenuDelete" class="kfmlast"><img src="'+ that.s.url.kfm +'clientscript/images/file-delete.gif" style="display:none" />' +
					'<a href="javascript:void(0);">Delete</a></li>' +
			'</ul>' +
		'</div>' +
		'<div id="kfmlmenu">' +
			'<ul>' +
				'<li id="kfmfmenuRename"><img src="'+ that.s.url.kfm +'clientscript/images/none.gif" style="display:none" />' +
					'<a href="javascript:void(0);">Rename</a></li>' +
				'<li id="kfmfmenuDelete"><img src="'+ that.s.url.kfm +'clientscript/images/folder-delete.gif" style="display:none" />' +
					'<a href="javascript:void(0);">Delete</a></li>' +
			'</ul>' +
		'</div>' +
		'<div id="kfmpopup">' +
			'<table border="0" width="100%" cellpadding="0" cellspacing="0"><tr bgcolor="#afccfb">' +
				'<td><div id="kfmpopuptitle"></div></td>' +
				'<td width="25"><div id="kfmpopupclose"></div></td>' +
			'</tr></table>' +
			'<div id="kfmpopupcontent">' + KFMlanguage.l0 + '</div>' +
		'</div>' +
		'<div id="kfmoverlay"></div>';
		$('#kfmWrapper').prepend(html);
		$('#kfmstt').html(KFMlanguage.l10);
		$('#kfmtt, #kfmLoadBusy').html(KFMlanguage.l0);
		$('#kfmLabelFolder').html(KFMlanguage.l14);
		$('#kfmLabelAddress').html(KFMlanguage.l15);
		$('#kfmoverlay').css({ height: that.s.popup.panel.h + 'px', opacity: that.s.popup.panel.o });
		if($.browser.msie && $.browser.version == '6.0') {
			$('#kfmoverlay').css({ height: that.s.popup.panel.h + 3 + 'px' });
		}
	}
	this.treeHTML = function (db) {
		var html = '';
		$.each(db.dirs, function(i,v) {
			if(v.h == 1) {
				html += '<li class="kfmTreeSub" id="tree_' + i + '">' +
							'<div class="kfmExpand kfmExpandPlus"></div>' +
							'<div class="kfmFolder kfmFolderClose" id="ftree_' + i + '">';
			} else {
				html += '<li id="tree_' + i + '">' +
							'<div class="kfmFolder kfmFolderClose" id="ftree_' + i + '">';
			}
				html  += 		'<a href="javascript:void(0);" name="' + v.n + '|' + v.h + '">' + v.n + '</a>' + 
							'</div>' + 
						'</li>';
		});
		return html;
	}
	this.treeHTMLExpand = function() {
		return '<div class="kfmExpand kfmExpandMinus"></div>';
	}
	this.treeAPPEND = function (db, liID, path){
		var html = '';
		var auchorID;
		$.each(db.dirs, function(i,v) {
			if(v.h == 1) {
				html += '<li class="kfmTreeSub" id="' + liID + '_' + i + '">' +
							'<div class="kfmExpand kfmExpandPlus"></div>' +
							'<div class="kfmFolder kfmFolderClose" id="f' + liID + '_' + i + '">';
			} else {
				html +=	'<li id="' + liID + '_' + i + '">' +
							'<div class="kfmFolder kfmFolderClose" id="f' + liID + '_' + i + '">';
			}
				html +=			'<a href="javascript:void(0);" name="' + path + '.' + v.n + '|' + v.h + '">' + v.n + '</a>' + 
							'</div>' + 
						'</li>';
		});
		return html = '<ul>' + html + '</ul>';
	}
	this.treeAPPENDNext = function (db, liID, path, nextID) {
		if(path != '') {
			path = path + '.';
		}
		html =	'<li id="' + liID + '_' + nextID + '">' +
					'<div class="kfmFolder kfmFolderClose" id="f' + liID + '_' + nextID + '">' +
					'<a href="javascript:void(0);" name="' + path + db.n + '|' + db.h + '">' + db.n + '</a>' + 
					'</div>' + 
				'</li>';
		return html;
	}
	this.treeBusy = function () {
		return '<ul id="kfmLoadBusyTree"><li><div class="kfmFolder kfmBusy">&nbsp;</div></li></ul>';
	}
	this.fileList = function (db) {
		var html = new String();
		$.each(db.files, function(i, v) {
			var w = (100 - v.w)/2;
			var h = (70 - v.h)/2;
			classed = v.w > 100 ? ' width="100"' : 'style="margin-left:'+ w +'px; margin-top:'+ h +'px;"';
			html += 
			'<div class="kfmfile kfmvisitor" id="kfmwrap' + i + '"> ' +
				'<div class="kfmwrap"> ' +
					'<div class="kfmclipimg" lang="<b>' + v.n + '</b> ('+ v.s +', '+ v.t +')">' +
						'<a href="javascript:void(0);" ' +
						'name="' + v.n + '|' + v.p + '|' + v.e + '|' + v.d + '|' + v.w + '|' + v.h + '">' +
							'<img src=" ' + v.f + ' " ' + classed + ' border="0" />' +
						'</a>' +
					'</div>' +
				'</div>' +
			'</div>';
		});
		$('#kfmFilelist').html(html);
	}
	this.popupView = function(title, content){
		var html = 
		'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' +
		'<html xmlns="http://www.w3.org/1999/xhtml">' +
			'<head>' +
				'<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' +
				'<title>' + title + '</title>' +
			'</head>' +
			'<body>' +
			'<table width="100%" border="0"><tr><td align="center">' +
				content +
			'</td></tr></table>' +
			'</body>' +
		'</html>';
		return html;
	}
	this.popupFolder = function (s, directory) {
		var html = 
		'<form name="newfolder" id="newfolder">' +
			'<div id="kfmnamefile" style="overflow:hidden"><b>' + KFMlanguage.l15 + '</b> /root/' + directory + '</div>' +
			'<div><input type="text" class="inputText" id="name" name="name" style="width:448px;" /></div>' +
			'<div class="inputButtonWrapper">' +
				'<input type="image" src="'+ that.s.url.kfm +'clientscript/images/ok.gif" name="submit" id="submit" />' +
				'<div class="inputBusy" id="kfmInputBusy"></div>' +
			'</div>' +
		'</form>';
		$('#kfmpopup').css({ width: that.s.popup.folder.w + 'px', height: that.s.popup.folder.h + 'px', left: s.w + 'px', top: s.h + 'px' }).show();
		return html;
	 }
	 this.popupUpload = function (s) {
		var html =
		'<div style="border:1px solid #9fc1f8;margin-bottom:10px;">' +
			'<table border="0" width="100%" cellpadding="0" cellspacing="1" class="kfmUploadListTitle"> ' +
				'<tr bgcolor="#afccfb">' +
					'<td width="250">' + KFMlanguage.l25 + '</td>' +
					'<td width="100">' + KFMlanguage.l26 + '</td>' +
					'<td>' + KFMlanguage.l27 + '</td>' +
				'</tr>' +
			'</table>' +
			'<div class="kfmUploadListWrapper">' +
				'<table border="0" width="100%" cellpadding="0" cellspacing="1" id="kfmUploadList"> ' +
				'</table>' +
			'</div>' +
		'</div>' +
		'<form name="upload" id="upload">' +
			'<div id="kfmnamefile" style="overflow:hidden">' + KFMlanguage.l24 + '</div>' +
			'<div class="inputButtonWrapper clearfix">' +
				'<div style="float:left;margin-right:5px;">' +
					'<input type="file" id="file" name="file" />' +
				'</div> ' +
				'<div style="float:left;">' +
					'<a href="javascript:void(0);" id="submit"><img src="'+ that.s.url.kfm +'clientscript/images/upload.gif" border="0"></a>' +
				'</div>'+
			'</div>' +
		'</form>';
		$('#kfmpopup').css({ width: that.s.popup.upload.w + 'px', height: that.s.popup.upload.h + 'px', left: s.w + 'px', top: s.h + 'px' }).show();
		return html;
	}
	this.popupUploadAppendList = function(id, queueID, fileName, byteSize, suffix){
		var html = 
		'<tr id="' + id + queueID + '">' +
			'<td width="250">' +
				'<span class="fileName">' + fileName + '</span> <span class="percentage">&nbsp;</span>' +
				'<div class="kfmFileUploadProgress">' +
					'<div id="' + id + queueID + 'ProgressBar" class="kfmFileUploadProgressBar" style="width:1px;height:1px;"></div>' +
				'</div>' +
			'</td>' +
			'<td width="100">' + byteSize + suffix + '</td>' +
			'<td><a href="javascript:$(\'#' + id + '\').fileUploadCancel(\'' + queueID + '\')">remove</a></td>' +
		'</tr>';
		return html;
	}
	this.popupHelp = function(s){
		var html = 
		'<table border="0" width="100%" cellpadding="0" cellspacing="0"> ' +
			'<tr>' +
				'<td align="center" style="font-size:11px">' +
					'<div style="margin-bottom:5px;font-weight:bold">User\' guide comming soon ...</div>' +
					'Copyright © 2009 KFManager. <br /> ' +
					'Co-developed &amp; design by <a href="http://kenphan.com" target="_blank">Ken Phan</a>' +
				'</td>' +
			'</tr>' +
		'</table>';
		return html;
	}
	this.popupUploadListNull = function(){
		return '<tr id="kfmUploadListNull"><td>' + KFMlanguage.l28 + '!</td></tr>'
	}
	this.popupRename = function(s, name, ext) {
		var style;
		if(!ext) { style = ' style="text-transform:capitalize;"'; }
		var html = 
		'<form name="rename" id="rename">' +
			'<input type="hidden" name="ext" id="ext" value=".' + ext + '" />' +
			'<div id="kfmnamefile" style="overflow:hidden"><b>' + KFMlanguage.l20 + '</b> <span'+style +'>' + name + '</span></div>' +
			'<div><input type="text" class="inputText" id="name" name="name" style="width:448px;" /></div>' +
			'<div class="inputButtonWrapper">' +
				'<input type="image" src="'+ that.s.url.kfm +'clientscript/images/ok.gif" name="submit" id="submit" />' +
				'<div class="inputBusy" id="kfmInputBusy"></div>' +
			'</div>' +
		'</form>';
		$('#kfmpopup').css({ width: that.s.popup.rename.w + 'px', height: that.s.popup.rename.h + 'px', left: s.w + 'px', top: s.h + 'px' }).show();
		return html;
	}
}