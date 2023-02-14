
/*
 * @author: Ken Phan <kenphan19@gmail.com>
 * @copyright: Ken Phan <http://kenphan.com>
 * @license: http://www.opensource.org/licenses/gpl-license.php
 * @description: KFManager object
 */

var KFMskins = new KFMSkins();
var KFM = new KFMObj();
var hash = new Array();
var that;

/*
 * @description: KFManager plugin extend
 */
;(function($)
{
	
	$.fn.KFMtree = function(options)
	{
		var settings = {
			url	: { kfm: '/webskins/richtext/ckeditor/plugins/kfm/', file: 'kfm.php' }, standalone: true,
			popup : { panel: { o: 0.5 }, rename: { w: 500, h: 150 }, folder: { w: 500, h: 150 }, upload: { w: 500, h:300 }, help: { w: 250, h: 115 } },
			upload: { rollover: true, 
				file: {
					desc: 'ai, doc, mdb, pdf, ppt, psd, rar, txt, xls, zip, gzip, chm, ttf, gdf',
					exts: '*.ai;*.doc;*.mdb;*.pdf;*.psd;*.rar;*.txt;*.xls;*.zip;*.gzip;*.chm;*.ttf;*.gdf'
				},
				images: {
					desc: 'jpg, jpeg, gif, bmp, png',
					exts: '*.jpg;*.jpeg;*.gif;*.bmp;*.png'
				},
				media: {
					desc: 'avi, flv, mp3, wma, wmv',
					exts: '*.avi;*.flv;*.mp3;*.wma;*.wmv'
				},
				flash: {
					desc: 'swf, fla',
					exts: '*.swf;*.fla'
				}
			}
		};
		var url = document.URL;
		var location = url.split('#');
		switch (location[1]) {
			case 'file':
				hash['firstTree'] = 'tree_0';
				break;
			case 'flash':
				hash['firstTree'] = 'tree_1';
				break;
			case 'images':
				hash['firstTree'] = 'tree_2';
				break;
			case 'media':
				hash['firstTree'] = 'tree_3';
				break;
			default:
				hash['firstTree'] = 'tree';
		}
		var s = $.extend(settings, options);
		s.popup.panel.w = $('#kfmWrapper').width();
		s.popup.panel.h = $('#kfmWrapper').height();
		s.all = $(this); KFM.s = s;
		KFM.treeCall(false, 'root');
	}
	
	$.fn.constrainInput = function(options)
	{
		var settings = $.extend({
			allowedCharsRegex: ".*"
		}, options);
		var re = new RegExp(settings.allowedCharsRegex);
		$.each(this, function(){
			var input = $(this);
			var keypressEvent = function(e) {
				e= e || window.event;
				var k = e.charCode || e.keyCode || e.which;
				if(e.ctrlKey || e.altKey || k == 8 || k == 13){//Ignore
					return true;
				} else if ((k >= 41 && k <= 122) ||k == 32 || k > 186){//typeable characters
					return (re.test(String.fromCharCode(k)));
				}
			return false;
		}
		input.bind("keypress", keypressEvent);
		});
		return this;
	};
})(jQuery);

/*
 * @description: KFManager object
 */
function KFMObj () {
	this.s = {};
	this.treeCall = function (liObject, path) {
		that = this;
		if(hash['ajax'] == true) {
			return false;
		}
		hash['ajax'] = true;
		if(path == 'root') { path = ''; } else { that.busy('show', liObject); }
		$.get(that.s.url.kfm + that.s.url.file, { type: 'folder', path: path, action: 'view' }, function(db) {
			hash['ajax'] = false;
			if(db.rep == 1) {
				if(path != '') {
					var liID = liObject.attr('id');
					hash[liID] = liID;
					that.treeExpand(liObject, false);
					liObject.append(KFMskins.treeAPPEND(db, liID, path));
				} else {
					KFM.fileCall('root', 'tree');
					KFMskins.start();
					$('#kfmlistroot').html(KFMskins.treeHTML(db));
				}
				that.treeEvent();
			} else {
				alert(KFMlanguage.l19);
				var aObject = liObject.find('.kfmFolder').find('a');
				var aName = aObject.attr('name');
				var aNameArr = aName.split('|');
				aObject.attr('name', aNameArr[0] + '|0')
				liObject.find('.kfmExpand').remove();
			}
			that.busy('hide', liObject);
		}, 'json');
	}
	this.treeEvent = function () {
		that.s.all.find('.kfmExpand').unbind().click(function() {
			if(hash['ajax'] == true) {
				return false;
			}
			var liObject	= $(this).parent();
			var liID		= liObject.attr('id');
			var liIDNeedle	= liObject.find('a').attr('name').split('|');
			// config first tree call
			var index = liID.indexOf(hash['firstTree'], liID);
			if(index < 0) { return false; }
			
			if(liIDNeedle[0] != 'root' && hash[liObject.attr('id')] == undefined) {
				that.treeCall(liObject, liIDNeedle[0]);
			}
			that.treeExpand(liObject, $(this));
		});
		var liObject = that.s.all.children('li').children('ul').children('li').children('ul');
		liObject.find('.kfmFolder').contextMenu('kfmlmenu', {
			bindings: {
				'kfmfmenuDelete': function(e) { that.folderDelete($('#'+e.id)); },
				'kfmfmenuRename': function(e) { that.folderRename($('#'+e.id)); }
			}
		});
		that.s.all.find('a').unbind().click(function() {
			var liObject	= $(this).parent().parent();
			var liID		= liObject.attr('id');
			// config first tree call
			var index = liID.indexOf(hash['firstTree'], liID);
			if(index < 0) { return false; }
			hash['uploadTypeID'] = liID.substr(0, 6);
			
			var aname		= liObject.children('.kfmFolder').find('a').text();
			var liIDNeedle	= $(this).attr('name').split('|');
			hash['liID']	= liID;
			hash['liPath']	= liIDNeedle[0] != 'root' ? liIDNeedle[0] : '';
			hash['stt']		= KFMlanguage.l10;
			that.directory(liIDNeedle[0]);
			$('#kfmstt').html(hash['stt']);
			if(hash['fileList' + hash['liID']] == undefined) {
				that.fileCall(liIDNeedle[0], hash['liID']);
			} else {
				that.fileList(hash['fileList' + hash['liID']]);
			}
			that.treeView(liObject);
		});
	}
	this.treeExpand = function (liObject, aIDObject) {
		if(aIDObject == false) {
			liObject.find('ul').show();
			liObject.find('.kfmExpand').removeClass('kfmExpandPlus').addClass('kfmExpandMinus');
		} else {
			if(that.isClass(aIDObject.attr('class'), 'kfmExpandMinus')) {
				aIDObject.removeClass('kfmExpandMinus').addClass('kfmExpandPlus'); liObject.children('ul').hide();
			} else {
				aIDObject.removeClass('kfmExpandPlus').addClass('kfmExpandMinus'); liObject.children('ul').show();
			}
		}
	}
	this.treeView = function (liObject) {
		that.s.all.find('.kfmFolder').removeClass('kfmFolderOpen').addClass('kfmFolderClose');
		that.s.all.find('a').removeClass('active');
		liObject.children(".kfmFolder").removeClass('kfmFolderClose').addClass('kfmFolderOpen').find('a').addClass('active');
	}
	this.folderDelete = function (fObject) {
		liObject = fObject.parent();
		var liID = liObject.attr('id');
		if(liID == undefined || liID == 'tree_0' || liID == 'tree_1' || liID == 'tree_2') {
			alert(KFMlanguage.l21);
			return false;
		}
		if(!confirm(KFMlanguage.l17)) {
			return false;
		}
		var liPath = liObject.children('.kfmFolder').find('a').attr('name').split('|');
		if(hash['ajax'] == true) {
			return false;
		}
		hash['ajax'] = true;
		$.get(that.s.url.kfm + that.s.url.file, { type: 'folder', action: 'delete', path: liPath[0] }, function(db) {
			hash['ajax'] = false;
			if(db.rep == 1) {
				that.treeRemove(liObject, liObject.attr('id'), false);
			} else {
				alert(KFMlanguage.l18);
			}
		}, 'json');
	}
	this.folderRename = function (fObject) {
		liObject = fObject.parent();
		var liID = liObject.attr('id');
		if(liID == 'tree') {
			alert(KFMlanguage.l22);
			return false;
		}
		var s = { w: (that.s.popup.panel.w - that.s.popup.rename.w) / 2,
				  h: (that.s.popup.panel.h - that.s.popup.rename.h) / 2 };
		that.mark('show');
		var oldname = liObject.children('.kfmFolder').find('a').text();
		var liPath = liObject.children('.kfmFolder').find('a').attr('name').split('|');
		$('#kfmpopuptitle').html(KFMlanguage.l31);
		$('#kfmpopupcontent').html(KFMskins.popupRename(s, oldname, false));
		$('#name').focus();
		$("#name").constrainInput( {allowedCharsRegex: '[a-zA-Z0-9| ]'});
		$('#rename').submit(function(){
			var name = $('#name').val();
			var ext = $('#ext').val();
			if(!that.valid(name)) {
				alert(KFMlanguage.l4);
				return false;
			}
			if(hash['ajax'] == true) {
				return false;
			}
			hash['ajax'] = true;
			$.get(that.s.url.kfm + that.s.url.file, { type: 'folder', action: 'rename', newname: name, oldname: oldname, path: liPath[0] }, function(db){
				hash['ajax'] = false;
				if(db.rep == 1) {
					that.mark('hide');
					liObject.children('.kfmFolder').find('a').text(name);
					that.writeName(liObject, name);
				} else {
					if(db.rep == 2) { alert(KFMlanguage.l5);
					} else {alert(KFMlanguage.l6); }
				}
			}, 'json');
			return false;
		});
	}
	this.writeName = function (liObject, name) {
		var liParentObject = liObject.parent().parent();
		var liParentID = liParentObject.attr('id');
		var liParentName = liParentObject.children('.kfmFolder').find('a').attr('name')
		var liParentNameArr = liParentName.split('|');
		var liName = liObject.children('.kfmFolder').find('a').attr('name');
		var liNameArr = liName.split('|');
		var liNameParent = '';
		if(liParentID != 'tree') {
			liNameParent = liNameArr[0].substr(0, liParentNameArr[0].length + 1);
		}
		var liPathChange = liNameParent + name + '|' + liNameArr[1];
		liObject.children('.kfmFolder').find('a').attr('name', liPathChange);
		that.writeNameChild(liObject);
	}
	this.writeNameChild = function (liObject) {
		duplicate = [];
		liObject.find('li').each(function(){
			var liID = $(this).attr('id');
			if(liID == undefined) {
				return false;
			}
			var aname = $(this).children('.kfmFolder').find('a').text();
			if(duplicate[liID] == undefined) {
				duplicate[liID] = true;
				var liText = $(this).children('.kfmFolder').find('a').text();
				var liNameArr = $(this).children('.kfmFolder').find('a').attr('name').split('|');
				var liParentObject = $(this).parent().parent();
				var liParentID = liParentObject.attr('id');
				var liParentName = liParentObject.children('.kfmFolder').find('a').attr('name');
				var liParentNameArr = liParentName.split('|');
				var pathChange = liParentNameArr[0] + '.' + liText + '|' + liNameArr[1];
				$(this).children('.kfmFolder').find('a').attr('name', pathChange);
			}
			that.writeNameChild($(this));
		});
	}
	this.fileCall = function (liPath, liID) {
		that.busy('show');
		if(liPath == 'root') {
			hash['liID'] = liID; hash[liID] = true; hash['liPath'] = ''; hash['directory'] = ''; liPath = '';
		}
		if(hash['ajax'] == true) {
			return false;
		}
		hash['ajax'] = true;
		$.get(that.s.url.kfm + that.s.url.file, { type: 'file', action: 'view', list: liPath }, function(db) {
			hash['ajax'] = false;
			that.busy('hide');
			if(db.rep == 1) {
				hash['fileList' + hash['liID']] = db;
				that.fileList(db);
				fileComponents();
			} else if(db.rep == 2) {
				that.treeRemove($('#' + liID), liID, true);
			}
		}, 'json');
		function fileComponents () {
			hash['oneAppend'] = undefined;
			$("#kfmFilter").constrainInput( {allowedCharsRegex: '[a-zA-Z0-9| ]'});
			$('#kfmFilter').live("keypress", function(e) {
				var db = hash['fileList' + hash['liID']];
				var letters = $(this).val();
				var count = 0; var stt;
				$.each(db.files, function(i, v) {
					if(v.n.indexOf(letters) >= 0) {
						$('#kfmwrap' + i).fadeIn();
						count++;
					} else {
						$('#kfmwrap' + i).fadeOut();
					}
				});
				if(hash['oneAppend'] == undefined) {
					hash['oneAppend'] = true;
					$('#kfmFilelist').append('<div id="noResultFilted">' + KFMlanguage.l29 + '</div>');
				}
				if(count == 0) {
					stt = '0 file';
					$('#noResultFilted').fadeIn();
				} else {
					$('#noResultFilted').fadeOut();
					stt = count + ' files';
				}
				$('#kfmtt').html(stt);
			});
			$('#kfmButtonRefresh').unbind().click(function(){
				that.fileCall(hash['liPath'], hash['liID']);
				return false;
			});
		}
	}
	this.treeRemove = function (liObject, liID, fileHasDelete) {
		var liUlObject		= liObject.parent().parent();
		var liUlID			= liUlObject.attr('id');
		var liIDNeedle		= liUlObject.children('.kfmFolder').find('a').attr('name').split('|');
		liObject.remove();
		var liHasUlVal		= liUlObject.find('li:first').attr('id');
		hash['liID']		= liUlID;
		hash[liID]			= true;
		hash['liPath']		= liIDNeedle[0];
		that.directory(liIDNeedle[0]);
		if(liHasUlVal == undefined || liHasUlVal == '') {
			var liHasUlVal = liUlObject.find('a').attr('name');
			var liHasUlValName = liHasUlVal.substr(0, liHasUlVal.length-1);
			liUlObject.find('a').attr('name', liHasUlValName + '0');
			liUlObject.find('.kfmExpand').remove();
			liUlObject.find('ul').remove();
			hash[hash['liID']] = undefined;
		}
		that.treeView(liUlObject);
		that.fileCall(liIDNeedle[0], liUlID);
		if(fileHasDelete == true) {
			$('#kfmFilelist').html(KFMlanguage.l7); alert(KFMlanguage.l7); 
		}
	}
	this.fileList = function (db) {
		if(db.total > 0) {
			KFMskins.fileList(db);
			that.fileEvent();
			$('#kfmtt').html(db.total + ' files');
		} else {
			$('#kfmFilelist').html(KFMlanguage.l13);
			$('#kfmtt').html('0 file');
		}
		$('#kfmstt').html(KFMlanguage.l10);
		that.popupFolder(); that.popupUpload(); that.popupHelp();
	}
	this.fileEvent = function () {
		$('.kfmfile').find('a').click(function (e) {
			e.cancelBubble = true; 
			$(this).parent().parent().parent().parent().find('.kfmfile').removeClass('kfmfileactive');
			$(this).parent().parent().parent().addClass('kfmfileactive');
			hash['stt'] = $(this).parent().parent().parent().find('.kfmclipimg').attr('lang');
			$('#kfmstt').html(hash['stt']);
		});
		if(!that.s.standalone) {
			$('.kfmfile').dblclick(function () {
				OpenFile($(this).find('img').attr('src'));
			});
		}
		function getUrlParam(paramName)
		{
		  var reParam = new RegExp('(?:[\?&]|&amp;)' + paramName + '=([^&]+)', 'i') ;
		  var match = window.location.search.match(reParam) ;
		 
		  return (match && match.length > 1) ? match[1] : '' ;
		}


		function OpenFile( fileUrl ) {
			var funcNum = getUrlParam('CKEditorFuncNum');
			window.opener.CKEDITOR.tools.callFunction(funcNum, fileUrl);
			//window.top.opener.SetUrl( encodeURI( fileUrl ).replace( '#', '%23' ) ) ;
			window.top.close() ;
			window.top.opener.focus() ;
		}
		$('.kfmfile').mouseover(function(){
			$(this).removeClass('kfmvisitor').addClass('kfmhover');
			$('#kfmstt').html($(this).find('.kfmclipimg').attr('lang'));
		}).mouseout(function(){
			$(this).removeClass('kfmhover').addClass('kfmvisitor');
			$('#kfmstt').html(hash['stt']);
		});
		$('.kfmfile').contextMenu('kfmrmenu', {
			bindings: {
				'kfmrmenuView': function(e) { that.popupView ( $('#'+e.id ) ); },
				'kfmrmenuDownload': function(e) { that.fileDownload ( $('#'+e.id ) ); },
				'kfmrmenuRename': function (e) { that.popupRename ( $('#'+e.id ) ); },
				'kfmrmenuEdit': function(e) { that.popupEdit ( $('#'+e.id ) ); },
				'kfmrmenuDelete': function(e) { that.fileDelete ( $('#'+e.id ) ); }
			}
		});
	}
	this.popupView = function(liObject) {
		var aVal = liObject.find('a').attr('name').split('|');
		var file = aVal[1].replace(/\./g, '/') + '/' + aVal[0];
		var w = parseInt(aVal[4]) + 50;
		var h = parseInt(aVal[5]) + 50;
		if(w < 200) w = 200; if(h < 200) h = 200;
		var screenLeft = (screen.width - w) / 2;
		var screenTop = (screen.height - h) / 2;
		if(aVal[3] == 1) {
			if(confirm(KFMlanguage.l16 + ' "' + aVal[0] + '" ? ')) {
				window.location = that.s.url.kfm + that.s.url.file + '?type=file&action=download&f=' + hash['liPath'].replace('.', '/') + '/' + aVal[0];
			}
		} else {
			myWindow = window.open('', 'viewpopup', 'location=no, menubar=no, status=no, toolbar=no, scrollbars=yes, resizable=no, width=' + w + ', height=' + h + ', top=' + screenTop + ', left=' + screenLeft);
			myWindow.document.title = aVal[0];
			var html = KFMskins.popupView(aVal[0], '<a href="javascript:window.close();"><img src="' + liObject.find('img').attr('src') + '" border="0" /></a>');
			myWindow.document.write(html);
		}
	}
	this.popupEdit = function (liObject) {
		alert('comming soon ...');
	}
	this.popupUpload = function() {
		$('#kfmButtonUpload').unbind().click(function() {
			var desc, exts;
			switch (hash['uploadTypeID']) {
				case 'tree_0':
					desc = that.s.upload.file.desc; exts = that.s.upload.file.exts;
					break;
				case 'tree_1':
					desc = that.s.upload.flash.desc; exts = that.s.upload.flash.exts;
					break;
				case 'tree_2':
					desc = that.s.upload.images.desc; exts = that.s.upload.images.exts;
					break;
				case 'tree_3':
					desc = that.s.upload.media.desc; exts = that.s.upload.media.exts;
					break;
				default:
					
					return false;
			}
			var s = { w: (that.s.popup.panel.w - that.s.popup.upload.w) / 2,
					  h: (that.s.popup.panel.h - that.s.popup.upload.h) / 2 };
			$('#kfmpopuptitle').html(KFMlanguage.l23);
			$('#kfmpopupcontent').html(KFMskins.popupUpload(s));
			that.mark('show');
			$('#kfmUploadList').html(KFMskins.popupUploadListNull());
			// config first tree call
			
			$('#file').fileUpload({ 
				uploader: that.s.url.kfm + 'clientscript/swf/uploader.swf',
				buttonImg: that.s.url.kfm + 'clientscript/images/choose-files.gif',
				script:	that.s.url.kfm + that.s.url.file + '-|-type=file_|_action=upload',
				rollover: that.s.upload.rollover, folder: hash['liPath'],
				width: 135, height: 27, multi: true, wmode: 'transparent',
				displayData:   'percentage', fileDataName:  'Filedata',
				scriptAccess:  'sameDomain', fileDesc: desc, fileExt: exts,
				onError: function (a, b, c, d) {
					if (d.status == 404)
						alert('Could not find upload script. Use a path relative to: upload.php');
					else if (d.type === "HTTP")
						alert('error '+d.type+": "+d.status);
					else if (d.type ==="File Size")
						alert(c.name+' '+d.type+' Limit: '+Math.round(d.sizeLimit/1024)+'KB');
					else
						alert('error '+d.type+": "+d.text);
				},
				onAllComplete: function() {
					KFM.fileCall(hash['liPath'], hash['liID']);
					that.mark('hide');
				}
			});
			$('#submit').click(function(){
				$('#file').fileUploadStart();
			});
		});
	}
	this.popupFolder = function() {
		$('#kfmButtonFolder').unbind().click(function(){
			var s = { w: (that.s.popup.panel.w - that.s.popup.folder.w) / 2,
					  h: (that.s.popup.panel.h - that.s.popup.folder.h) / 2 };
			$('#kfmpopuptitle').html(KFMlanguage.l9);
			$('#kfmpopupcontent').html(KFMskins.popupFolder(s, hash['directory']));
			that.mark('show');
			$('#name').focus();
			$("#name").constrainInput( {allowedCharsRegex: '[a-zA-Z0-9| ]'});
			$('#newfolder').submit(function() {
				var name = $('#name').val();
				if(!that.valid(name)){
					alert(KFMlanguage.l4);
					return false;
				}
				var liObject = $('#' + hash['liID']);
				// config first tree call
				var index = hash['liID'].indexOf(hash['firstTree'], hash['liID']);
				if(index < 0 || hash['liID'] == 'tree') { return false; }
				
				that.busy('show', liObject);
				that.busy('show', 'form');
				if(hash[hash['liID']] == undefined) {
					var liHasUlVal = liObject.find('a').attr('name').split('|');
					if(liHasUlVal[1] == 1) {
						if(hash['ajax'] == true) {
							return false;
						}
						hash['ajax'] = true;
						$.get(that.s.url.kfm + that.s.url.file, { type: 'folder', action: 'make', hasul: 1, path: hash['liPath'], name: name }, function(db) {
							hash['ajax'] = false;
							if(db.rep == 2) {
								alert(KFMlanguage.l5);
							} else if(db.rep == 1) {
								that.mark('hide');
							} else {
								alert(KFMlanguage.l6);
								that.mark('hide');
							}
							liObject.find('ul').show();
							liObject.find('.kfmExpand').removeClass('kfmExpandPlus').addClass('kfmExpandMinus');
							liObject.append(KFMskins.treeAPPEND(db, hash['liID'], liHasUlVal[0]));
							that.treeEvent();
							that.busy('hide', liObject);
							that.busy('hide', 'form');
						}, 'json' );
					} else {
						if(hash['ajax'] == true) {
							return false;
						}
						hash['ajax'] = true;
						$.get(that.s.url.kfm + that.s.url.file, { type: 'folder', action: 'make', hasul: 1, path: hash['liPath'], name: name }, function(db){
							hash['ajax'] = false;
							if(db.rep == 2) {
								alert(KFMlanguage.l5);
							} else if(db.rep == 1) {
								var html = KFMskins.treeHTMLExpand() + KFMskins.treeAPPEND(db, hash['liID'], hash['liPath'])
								liObject.append(html).addClass('kfmTreeSub');
								var aObject = liObject.children('.kfmFolder').find('a')
								aVal = aObject.attr('name');
								aObject.attr('name', aVal.substr(0, aVal.length-1) + '1');
								that.treeEvent();
								that.mark('hide');
							} else {
								alert(KFMlanguage.l6);
								that.mark('hide');
							}
							that.busy('hide', liObject);
							that.busy('hide', 'form');
						}, 'json');
					}
					hash[hash['liID']] = true;
				} else {
					var liID = liObject.attr('id');
					if(liID != '' && liID != undefined) {
						if(hash['ajax'] == true) {
							return false;
						}
						hash['ajax'] = true;
						$.get(that.s.url.kfm + that.s.url.file, { type: 'folder', action: 'make', path: hash['liPath'], name: name }, function(db){
							hash['ajax'] = false;
							if(db.rep == 2) {
								alert(KFMlanguage.l5);
							} else if(db.rep == 1) {
								var liIDLast = liObject.find('li:last-child').attr('id').split('_');
								var liIDNext = parseInt(liIDLast[liIDLast.length - 1]) + 1;
								var html = KFMskins.treeAPPENDNext(db, hash['liID'], hash['liPath'], liIDNext);
								liObject.children('ul').append(html).addClass('kfmTreeSub');
								that.treeEvent();
								that.mark('hide');
							} else {
								alert(KFMlanguage.l6);
								that.mark('hide');
							}
							that.busy('hide', liObject);
							that.busy('hide', 'form');
						}, 'json');
					} else {
						alert('null');
					}
				}
				return false;
			});
		});
	}
	this.popupHelp = function () {
		$('#kfmButtonHelp').unbind().click(function(){
			var s = { w: (that.s.popup.panel.w - that.s.popup.help.w) / 2,
					  h: (that.s.popup.panel.h - that.s.popup.help.h) / 2 };
			$('#kfmpopuptitle').html(KFMlanguage.l30);
			$('#kfmpopupcontent').html(KFMskins.popupHelp(s));
			$('#kfmpopup').css({ width: that.s.popup.help.w + 'px', height: that.s.popup.help.h + 'px', left: s.w + 'px', top: s.h + 'px' }).show();
			that.mark('show');
		});
	}
	this.fileDelete = function (liObject) {
		var a = liObject.find('a').attr('name').split('|');
		if(!confirm(KFMlanguage.l11 + ' "' + a[0] + '" ?')) {
			return false;
		}
		if(hash['ajax'] == true) {
			return false;
		}
		hash['ajax'] = true;
		$.get(that.s.url.kfm + that.s.url.file, { type: 'file', action: 'delete', file: a[0], path: hash['liPath'] }, function(db) {
			hash['ajax'] = false;
			if(db.rep == 1) {
				liObject.fadeOut();
				var ttVal = $('#kfmtt').text().split(' ');
				if(ttVal[0] > 1) {
					$('#kfmtt').text(ttVal[0] - 1 + ' files');
				} else {
					$('#kfmtt').text('0 file');
					$('#kfmFilelist').html(KFMlanguage.l13)
				}
			} else { alert(KFMlanguage.l12); }
		}, 'json');
	}
	this.fileDownload = function (liObject) {
		var aVal = liObject.find('a').attr('name').split('|');
		var file = that.s.url.kfm + that.s.url.file + '?type=file&action=download&f=' + hash['liPath'].replace(/\./g, '/') + '/' + aVal[0];
		window.location = file;
	}
	this.popupRename = function (liObject) {
		var s = { w: (that.s.popup.panel.w - that.s.popup.rename.w) / 2,
				  h: (that.s.popup.panel.h - that.s.popup.rename.h) / 2,
				  a: liObject.find('a').attr('name').split('|') };
		that.mark('show');
		$('#kfmpopuptitle').html(KFMlanguage.l8);
		$('#kfmpopupcontent').html(KFMskins.popupRename(s, s.a[0], s.a[2]));
		$('#name').focus();
		$("#name").constrainInput( {allowedCharsRegex: '[a-zA-Z0-9| ]'});
		$('#rename').submit(function(){
			var name = $('#name').val();
			var ext = $('#ext').val();
			if(!that.valid(name)) {
				alert(KFMlanguage.l1);
				return false;
			}
			if(hash['ajax'] == true) {
				return false;
			}
			hash['ajax'] = true;
			$.get(that.s.url.kfm + that.s.url.file, { type: 'file', action: 'rename', oldname: s.a[0], newname: name + ext, path: hash['liPath'] }, function(db){
				hash['ajax'] = false;
				if(db.rep == 1) {
					that.mark('hide');
					that.fileCall(hash['liPath'], hash['liID']);
				} else {
					if(db.rep == 2) {
						alert(KFMlanguage.l2);
					} else {
						alert(KFMlanguage.l3);
					}
				}
			}, 'json');
			return false;
		});
	}
	this.directory = function(directory) {
		hash['directory'] = directory.replace(/\./g, '/') + '/';
		if(directory == 'root') {
			hash['directory'] = '';
		}
		$('#kfmdireactory').html(hash['directory']);
	}
	this.busy = function (act, type) {
		if(type == 'file' || !type) {
			if(act == 'show') {
				$('#kfmFilelist').html('<div id="kfmLoadBusy">' + KFMlanguage.l0 + '</div>');
			} else if(act == 'hide') {
				$('#kfmLoadBusyTree').remove();
			}
		} else if(type == 'form') {
			if(act == 'show') {
				$('#kfmInputBusy').show();
			} else if(act == 'hide') {
				$('#kfmInputBusy').hide();
			}
		} else {
			if(act == 'show') {
				type.append(KFMskins.treeBusy());
			} else if(act == 'hide') {
				$('#kfmLoadBusyTree').remove();
			}
		}
	}
	this.mark = function (act) {
		if(act == 'show') {
			$('#kfmoverlay').show();
			hide();
		} else if (act == 'hide') {
			$('#kfmoverlay, #kfmpopup').hide();
		}
		function hide() {
			$('#kfmoverlay, #kfmpopupclose').unbind().click(function(){
				$('#kfmoverlay, #kfmpopup').hide();
			});
		}
	}
	this.valid = function(str) {
		var checked = str.match(/\\|\/|:|\*|\?|\"|\'|\<|\>|\||\.|\!/g); // \ / : * ? " ' < > | .
		if(checked || str == '') {
			return false;
		}
		return true;
	}
	this.isClass = function (input, check) {
		return (input.indexOf(check)  > -1);
	}
}