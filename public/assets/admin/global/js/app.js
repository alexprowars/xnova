var App = function()
{
	var isRTL = false;

	var assetsPath = '/assets/';
	var globalImgPath = 'global/img/';
	var globalPluginsPath = 'global/plugins/';
	var globalCssPath = 'global/css/';

	// Set proper height for sidebar and content. The content and sidebar height must be synced always.
	var handleSidebarAndContentHeight = function () {
		var content = $('.page-content');
		var sidebar = $('.page-sidebar');
		var body = $('body');
		var height;

		var resBreakpointMd = App.getResponsiveBreakpoint('md');

		if (body.hasClass("page-footer-fixed") === true && body.hasClass("page-sidebar-fixed") === false) {
			var available_height = App.getViewPort().height - $('.page-footer').outerHeight() - $('.page-header').outerHeight();
			var sidebar_height = sidebar.outerHeight();
			if (sidebar_height > available_height) {
				available_height = sidebar_height + $('.page-footer').outerHeight();
			}
			if (content.height() < available_height) {
				content.attr('style', 'min-height:' + available_height + 'px');
			}
		} else {
			if (body.hasClass('page-sidebar-fixed')) {
				height = _calculateFixedSidebarViewportHeight();
				if (body.hasClass('page-footer-fixed') === false) {
					height = height - $('.page-footer').outerHeight();
				}
			} else {
				var headerHeight = $('.page-header').outerHeight();
				var footerHeight = $('.page-footer').outerHeight();

				if (App.getViewPort().width < resBreakpointMd) {
					height = App.getViewPort().height - headerHeight - footerHeight;
				} else {
					height = sidebar.height() + 20;
				}

				if ((height + headerHeight + footerHeight) <= App.getViewPort().height) {
					height = App.getViewPort().height - headerHeight - footerHeight;
				}
			}
			content.attr('style', 'min-height:' + height + 'px');
		}
	};

	// Handles Bootstrap switches
	var handleBootstrapSwitch = function () {
		if (!$().bootstrapSwitch) {
			return;
		}
		$('.make-switch').bootstrapSwitch();
	};

	var handleScrollers = function() {
		App.initSlimScroll('.scroller');
	};

	// Handles custom checkboxes & radios using jQuery Uniform plugin
	var handleUniform = function () {
		if (!$().uniform) {
			return;
		}
		var test = $("input[type=checkbox]:not(.toggle, .md-check, .md-radiobtn, .make-switch, .icheck), input[type=radio]:not(.toggle, .md-check, .md-radiobtn, .star, .make-switch, .icheck)");
		if (test.size() > 0) {
			test.each(function () {
				if ($(this).parents(".checker").size() === 0) {
					$(this).show();
					$(this).uniform();
				}
			});
		}
	};

	// Hanles sidebar toggler
	var handleSidebarToggler = function () {
		var body = $('body');
		if ($.cookie && $.cookie('sidebar_closed') === '1' && App.getViewPort().width >= resBreakpointMd) {
			$('body').addClass('page-sidebar-closed');
			$('.page-sidebar-menu').addClass('page-sidebar-menu-closed');
		}

		// handle sidebar show/hide
		body.on('click', '.sidebar-toggler', function (e) {
			var sidebar = $('.page-sidebar');
			var sidebarMenu = $('.page-sidebar-menu');
			$(".sidebar-search", sidebar).removeClass("open");

			if (body.hasClass("page-sidebar-closed")) {
				body.removeClass("page-sidebar-closed");
				sidebarMenu.removeClass("page-sidebar-menu-closed");
				if ($.cookie) {
					$.cookie('sidebar_closed', '0');
				}
			} else {
				body.addClass("page-sidebar-closed");
				sidebarMenu.addClass("page-sidebar-menu-closed");
				if (body.hasClass("page-sidebar-fixed")) {
					sidebarMenu.trigger("mouseleave");
				}
				if ($.cookie) {
					$.cookie('sidebar_closed', '1');
				}
			}

			$(window).trigger('resize');
		});
	};

	var handleBootstrapSelect = function () {
		$('.bs-select').selectpicker({
			iconBase: 'fa',
			tickIcon: 'fa-check'
		});
	};

	var handleSidebarMenu = function () {

		var resBreakpointMd = App.getResponsiveBreakpoint('md');

		// offcanvas mobile menu
		$('.page-sidebar-mobile-offcanvas .responsive-toggler').click(function () {
			$('body').toggleClass('page-sidebar-mobile-offcanvas-open');
			e.preventDefault();
			e.stopPropagation();
		});

		if ($('body').hasClass('page-sidebar-mobile-offcanvas')) {
			$(document).on('click', function (e) {
				if ($('body').hasClass('page-sidebar-mobile-offcanvas-open')) {
					if ($(e.target).closest('.page-sidebar-mobile-offcanvas .responsive-toggler').length === 0 &&
						$(e.target).closest('.page-sidebar-wrapper').length === 0) {
						$('body').removeClass('page-sidebar-mobile-offcanvas-open');
						e.preventDefault();
						e.stopPropagation();
					}
				}
			});
		}

		// handle sidebar link click
		$('.page-sidebar-menu').on('click', 'li > a.nav-toggle, li > a > span.nav-toggle', function (e) {
			var that = $(this).closest('.nav-item').children('.nav-link');

			if (App.getViewPort().width >= resBreakpointMd && !$('.page-sidebar-menu').attr("data-initialized") && $('body').hasClass('page-sidebar-closed') && that.parent('li').parent('.page-sidebar-menu').size() === 1) {
				return;
			}

			var hasSubMenu = that.next().hasClass('sub-menu');

			if (App.getViewPort().width >= resBreakpointMd && that.parents('.page-sidebar-menu-hover-submenu').size() === 1) { // exit of hover sidebar menu
				return;
			}

			if (hasSubMenu === false) {
				if (App.getViewPort().width < resBreakpointMd && $('.page-sidebar').hasClass("in")) { // close the menu on mobile view while laoding a page
					$('.page-header .responsive-toggler').click();
				}
				return;
			}

			var parent = that.parent().parent();
			var the = that;
			var menu = $('.page-sidebar-menu');
			var sub = that.next();

			var autoScroll = menu.data("auto-scroll");
			var slideSpeed = parseInt(menu.data("slide-speed"));
			var keepExpand = menu.data("keep-expanded");

			if (!keepExpand) {
				parent.children('li.open').children('a').children('.arrow').removeClass('open');
				parent.children('li.open').children('.sub-menu:not(.always-open)').slideUp(slideSpeed);
				parent.children('li.open').removeClass('open');
			}

			var slideOffeset = -200;

			if (sub.is(":visible")) {
				$('.arrow', the).removeClass("open");
				the.parent().removeClass("open");
				sub.slideUp(slideSpeed, function () {
					if (autoScroll === true && $('body').hasClass('page-sidebar-closed') === false) {
						if ($('body').hasClass('page-sidebar-fixed')) {
							menu.slimScroll({
								'scrollTo': (the.position()).top
							});
						} else {
							App.scrollTo(the, slideOffeset);
						}
					}
					handleSidebarAndContentHeight();
				});
			} else if (hasSubMenu) {
				$('.arrow', the).addClass("open");
				the.parent().addClass("open");
				sub.slideDown(slideSpeed, function () {
					if (autoScroll === true && $('body').hasClass('page-sidebar-closed') === false) {
						if ($('body').hasClass('page-sidebar-fixed')) {
							menu.slimScroll({
								'scrollTo': (the.position()).top
							});
						} else {
							App.scrollTo(the, slideOffeset);
						}
					}
					handleSidebarAndContentHeight();
				});
			}

			e.preventDefault();
		});
	};

	return {
     	init: function ()
	 	{
			handleUniform();
			handleBootstrapSwitch();
			handleSidebarToggler();
			handleBootstrapSelect();
			handleScrollers();
			handleSidebarMenu();
     	},

		// wrApper function to  block element(indicate loading)
		blockUI: function (options) {
			options = $.extend(true, {}, options);
			var html = '';
			if (options.animate) {
				html = '<div class="loading-message ' + (options.boxed ? 'loading-message-boxed' : '') + '">' + '<div class="block-spinner-bar"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div>' + '</div>';
			} else if (options.iconOnly) {
				html = '<div class="loading-message ' + (options.boxed ? 'loading-message-boxed' : '') + '"><img src="' + this.getGlobalImgPath() + 'loading-spinner-grey.gif" align=""></div>';
			} else if (options.textOnly) {
				html = '<div class="loading-message ' + (options.boxed ? 'loading-message-boxed' : '') + '"><span>&nbsp;&nbsp;' + (options.message ? options.message : 'LOADING...') + '</span></div>';
			} else {
				html = '<div class="loading-message ' + (options.boxed ? 'loading-message-boxed' : '') + '"><img src="' + this.getGlobalImgPath() + 'loading-spinner-grey.gif" align=""><span>&nbsp;&nbsp;' + (options.message ? options.message : 'LOADING...') + '</span></div>';
			}

			if (options.target) { // element blocking
				var el = $(options.target);
				if (el.height() <= ($(window).height())) {
					options.cenrerY = true;
				}
				el.block({
					message: html,
					baseZ: options.zIndex ? options.zIndex : 1000,
					centerY: options.cenrerY !== undefined ? options.cenrerY : false,
					css: {
						top: '10%',
						border: '0',
						padding: '0',
						backgroundColor: 'none'
					},
					overlayCSS: {
						backgroundColor: options.overlayColor ? options.overlayColor : '#555',
						opacity: options.boxed ? 0.05 : 0.1,
						cursor: 'wait'
					}
				});
			} else { // page blocking
				$.blockUI({
					message: html,
					baseZ: options.zIndex ? options.zIndex : 1000,
					css: {
						border: '0',
						padding: '0',
						backgroundColor: 'none'
					},
					overlayCSS: {
						backgroundColor: options.overlayColor ? options.overlayColor : '#555',
						opacity: options.boxed ? 0.05 : 0.1,
						cursor: 'wait'
					}
				});
			}
		},

		// wrApper function to  un-block element(finish loading)
		unblockUI: function (target) {
			if (target) {
				$(target).unblock({
					onUnblock: function () {
						$(target).css('position', '');
						$(target).css('zoom', '');
					}
				});
			} else {
				$.unblockUI();
			}
		},

		alert: function (options) {

			options = $.extend(true, {
				container: "", // alerts parent container(by default placed after the page breadcrumbs)
				place: "append", // "append" or "prepend" in container
				type: 'success', // alert's type
				message: "", // alert's message
				close: true, // make alert closable
				reset: true, // close all previouse alerts first
				focus: true, // auto scroll to the alert after shown
				closeInSeconds: 0, // auto close after defined seconds
				icon: "" // put icon before the message
			}, options);

			var id = App.getUniqueID("App_alert");

			var html = '<div id="' + id + '" class="custom-alerts alert alert-' + options.type + ' fade in">' + (options.close ? '<button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>' : '') + (options.icon !== "" ? '<i class="fa-lg fa fa-' + options.icon + '"></i>  ' : '') + options.message + '</div>';

			if (options.reset) {
				$('.custom-alerts').remove();
			}

			if (!options.container) {
				if ($('.page-fixed-main-content').size() === 1) {
					$('.page-fixed-main-content').prepend(html);
				} else if (($('body').hasClass("page-container-bg-solid") || $('body').hasClass("page-content-white")) && $('.page-head').size() === 0) {
					$('.page-title').after(html);
				} else {
					if ($('.page-bar').size() > 0) {
						$('.page-bar').after(html);
					} else {
						$('.page-breadcrumb, .breadcrumbs').after(html);
					}
				}
			} else {
				if (options.place == "append") {
					$(options.container).append(html);
				} else {
					$(options.container).prepend(html);
				}
			}

			if (options.focus) {
				App.scrollTo($('#' + id));
			}

			if (options.closeInSeconds > 0) {
				setTimeout(function () {
					$('#' + id).remove();
				}, options.closeInSeconds * 1000);
			}

			return id;
		},

		//public function to get a paremeter by name from URL
		getURLParameter: function (paramName) {
			var searchString = window.location.search.substring(1),
				i, val, params = searchString.split("&");

			for (i = 0; i < params.length; i++) {
				val = params[i].split("=");
				if (val[0] == paramName) {
					return unescape(val[1]);
				}
			}
			return null;
		},

		getGlobalImgPath: function () {
			return assetsPath + globalImgPath;
		},

		getUniqueID: function (prefix) {
			return 'prefix_' + Math.floor(Math.random() * (new Date()).getTime());
		},

		// wrApper function to scroll(focus) to an element
		scrollTo: function (el, offeset) {
			var pos = (el && el.size() > 0) ? el.offset().top : 0;

			if (el) {
				if ($('body').hasClass('page-header-fixed')) {
					pos = pos - $('.page-header').height();
				} else if ($('body').hasClass('page-header-top-fixed')) {
					pos = pos - $('.page-header-top').height();
				} else if ($('body').hasClass('page-header-menu-fixed')) {
					pos = pos - $('.page-header-menu').height();
				}
				pos = pos + (offeset ? offeset : -1 * el.height());
			}

			$('html,body').animate({
				scrollTop: pos
			}, 'slow');
		},

		// initializes uniform elements
		initUniform: function (els) {
			if (els) {
				$(els).each(function () {
					if ($(this).parents(".checker").size() === 0) {
						$(this).show();
						$(this).uniform();
					}
				});
			} else {
				handleUniform();
			}
		},

		getViewPort: function () {
			var e = window,
				a = 'inner';
			if (!('innerWidth' in window)) {
				a = 'client';
				e = document.documentElement || document.body;
			}

			return {
				width: e[a + 'Width'],
				height: e[a + 'Height']
			};
		},

		generatePassword: function (plength)
		{
			var passwd = '';
			var chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
			for (var i = 1; i <= plength; i++) {
				var c = Math.floor(Math.random() * chars.length + 1);
				passwd += chars.charAt(c)
			}

			return passwd;
		},

		initSlimScroll: function (el) {
			if (!$().slimScroll) {
				return;
			}

			$(el).each(function () {
				if ($(this).attr("data-initialized")) {
					return; // exit
				}

				var height;

				if ($(this).attr("data-height")) {
					height = $(this).attr("data-height");
				} else {
					height = $(this).css('height');
				}

				$(this).slimScroll({
					allowPageScroll: true, // allow page scroll when the element scroll is ended
					size: '7px',
					color: ($(this).attr("data-handle-color") ? $(this).attr("data-handle-color") : '#bbb'),
					wrapperClass: ($(this).attr("data-wrapper-class") ? $(this).attr("data-wrapper-class") : 'slimScrollDiv'),
					railColor: ($(this).attr("data-rail-color") ? $(this).attr("data-rail-color") : '#eaeaea'),
					position: isRTL ? 'left' : 'right',
					height: height,
					alwaysVisible: ($(this).attr("data-always-visible") == "1" ? true : false),
					railVisible: ($(this).attr("data-rail-visible") == "1" ? true : false),
					disableFadeOut: true
				});

				$(this).attr("data-initialized", "1");
			});
		},

		destroySlimScroll: function (el) {
			if (!$().slimScroll) {
				return;
			}

			$(el).each(function () {
				if ($(this).attr("data-initialized") === "1") { // destroy existing instance before updating the height
					$(this).removeAttr("data-initialized");
					$(this).removeAttr("style");

					var attrList = {};

					// store the custom attribures so later we will reassign.
					if ($(this).attr("data-handle-color")) {
						attrList["data-handle-color"] = $(this).attr("data-handle-color");
					}
					if ($(this).attr("data-wrapper-class")) {
						attrList["data-wrapper-class"] = $(this).attr("data-wrapper-class");
					}
					if ($(this).attr("data-rail-color")) {
						attrList["data-rail-color"] = $(this).attr("data-rail-color");
					}
					if ($(this).attr("data-always-visible")) {
						attrList["data-always-visible"] = $(this).attr("data-always-visible");
					}
					if ($(this).attr("data-rail-visible")) {
						attrList["data-rail-visible"] = $(this).attr("data-rail-visible");
					}

					$(this).slimScroll({
						wrapperClass: ($(this).attr("data-wrapper-class") ? $(this).attr("data-wrapper-class") : 'slimScrollDiv'),
						destroy: true
					});

					var the = $(this);

					// reassign custom attributes
					$.each(attrList, function (key, value) {
						the.attr(key, value);
					});

				}
			});
		},

		getResponsiveBreakpoint: function (size) {
			// bootstrap responsive breakpoints
			var sizes = {
				'xs': 480,     // extra small
				'sm': 768,     // small
				'md': 992,     // medium
				'lg': 1200     // large
			};

			return sizes[size] ? sizes[size] : 0;
		}
 	};
}();

jQuery(document).ready(function()
{
   App.init();
});