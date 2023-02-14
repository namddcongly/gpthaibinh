/*
 * ContextMenu - jQuery plugin for right-click context menus
 *
 * Author: Chris Domigan
 * Contributors: Dan G. Switzer, II
 * Parts of this plugin are inspired by Joern Zaefferer's Tooltip plugin
 *
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 *
 * Version: r2
 * Date: 16 July 2007
 *
 * For documentation visit http://www.trendskitchens.co.nz/jquery/contextmenu/
 *
 */

(function($) {
	var menu, shadow, trigger, content, hash, currentTarget;
	var defaults = {
		menuStyle: {
			margin: '0px',
			width: '90px',
			fontSize: '11px',
			listStyle: 'none',
			backgroundColor: '#fff',
			border: '1px solid #9fc1f8',
			fontFamily: 'Tahoma, Helvetica, sans-serif'
		},
		itemStyle: {
			margin: '0',
			color: '#066',
			padding: '5px',
			display: 'block',
			cursor: 'default',
			paddingLeft: '30px',
			marginBottom: '1px',
			backgroundColor: 'transparent',
			backgroundRepeat: 'no-repeat',
			backgroundPosition: 'left center'
		},
		itemHoverStyle: {
			backgroundPosition: 'right center'
    	},
		eventPosX: 'pageX',
		eventPosY: 'pageY',
		shadow : true,
		onContextMenu: null,
		onShowMenu: null
	};
	function display(index, trigger, e, options) {
		var cur = hash[index];
		var thisall = $('#'+cur.id);
		content = thisall.find('ul:first').clone(true);
		content.css(cur.menuStyle).find('li').css(cur.itemStyle).hover (
			function() {
				$(this).css(cur.itemHoverStyle);
			},
			function(){
				$(this).css(cur.itemStyle);
			}
		);
		
		menu.html(content);
		if (!!cur.onShowMenu) {
			menu = cur.onShowMenu(e, menu);
		}
		$.each(cur.bindings, function(id, func) {
			$('#'+id, menu).bind('click', function(e) {
				hide();
				func(trigger, currentTarget);
			});
		});
		menu.css({'left':e[cur.eventPosX],'top':e[cur.eventPosY]}).show();
		
		if (cur.shadow) {
			shadow.css({width:menu.width(),height:menu.height(),left:e.pageX+2,top:e.pageY+2}).show();
		}
		$(document).one('click', hide);
	}
	function hide() {
		menu.hide();
		shadow.hide();
	}
	$.contextMenu = {
		defaults : function(userDefaults) {
			$.each(userDefaults, function(i, val) {
				if (typeof val == 'object' && defaults[i]) {
					$.extend(defaults[i], val);
				}
				else {
					defaults[i] = val;
				}
			});
		}
	}
	$.fn.contextMenu = function(id, options) {
		if(!menu) {
			menu = $('<div id="jqContextMenu"></div>')
					.hide()
					.css({position:'absolute', zIndex:'500'})
					.appendTo('body')
					.bind('click', function(e) {
						e.stopPropagation();
			});
		}
		if(!shadow) {
			shadow = $('<div></div>')
					.css({backgroundColor:'#e0eafa', position:'absolute', opacity:0.2 ,zIndex:499})
					.appendTo('body')
					.hide();
		}
		$('#' + id).find('li').each(function(){
			var thisit = $(this);
			var thisid = $('#' + thisit.attr('id'));
			var thisimg = thisid.find('img');
			if(thisimg.attr('src') != undefined) {
				thisid.css({ backgroundImage: 'url('+ thisimg.attr('src') +')' });
			}
		});
		hash = hash || [];
		hash.push({
			id : id,
			menuStyle: $.extend({}, defaults.menuStyle, options.menuStyle || {}),
			itemStyle: $.extend({}, defaults.itemStyle, options.itemStyle || {}),
			itemHoverStyle: $.extend({}, defaults.itemHoverStyle, options.itemHoverStyle || {}),
			bindings: options.bindings || {},
			shadow: options.shadow || options.shadow === false ? options.shadow : defaults.shadow,
			onContextMenu: options.onContextMenu || defaults.onContextMenu,
			onShowMenu: options.onShowMenu || defaults.onShowMenu,
			eventPosX: options.eventPosX || defaults.eventPosX,
			eventPosY: options.eventPosY || defaults.eventPosY
		});
		var index = hash.length - 1;
		$(this).bind('contextmenu', function(e) {
			var bShowContext = (!!hash[index].onContextMenu) ? hash[index].onContextMenu(e) : true;
			if (bShowContext) {
				display(index, this, e, options);
			}
			return false;
		});
		return this;
	};
})(jQuery);
$(function() {
	$('div.contextMenu').hide();
});