import './scss/admin.scss';

jQuery.fn.hasDataAttr = function(name) {
  return $(this)[0].hasAttribute('data-'+ name);
};

jQuery.fn.dataAttr = function(name, def) {
  return $(this)[0].getAttribute('data-'+ name) || def;
};

$(document).ready(function()
{
	var preloader = $('.preloader');

	if (preloader.length)
	{
		var speed = preloader.dataAttr('hide-spped', 600);
		preloader.fadeOut(speed);
	}

	$('.sidebar-navigation').perfectScrollbar();

	$(document).on('click', '.sidebar-toggler', function () {
		sidebar.open();
	});

	$(document).on('click', '.backdrop-sidebar', function () {
		sidebar.close();
	});

	$(document).on('click', '.sidebar .menu-link', function (e) {
		var $submenu = $(this).next('.menu-submenu');
		if ($submenu.length < 1)
			return;

		e.preventDefault();

		if ($submenu.is(":visible"))
		{
			$submenu.slideUp(function () {
				$('.sidebar .menu-item.open').removeClass('open');
			});
			$(this).removeClass('open');
			return;
		}

		$('.sidebar .menu-submenu:visible').slideUp();
		$('.sidebar .menu-link').removeClass('open');
		$submenu.slideToggle(function () {
			$('.sidebar .menu-item.open').removeClass('open');
		});
		$(this).addClass('open');
	});

	$(document).on('click', '.sidebar-toggle-fold', function () {
		sidebar.toggleFold();
	});

	$(document).on('focus', '.form-type-material .form-control:not(.bootstrap-select)', function () {
		materialDoFloat($(this));
	});

	$(document).on('focusout', '.form-type-material .form-control:not(.bootstrap-select)', function ()
	{
		if ($(this).val() === "")
			materialNoFloat($(this));
	});

	$(".form-type-material .form-control").each(function ()
	{
		if ($(this).val().length > 0)
		{
			if ($(this).is('[data-provide~="selectpicker"]'))
				return;

			materialDoFloat($(this));
		}
	});

	// Select picker
	$(document).on('show.bs.select', '.form-type-material [data-provide~="selectpicker"]', function () {
		materialDoFloat($(this));
	});

	$(document).on('hidden.bs.select', '.form-type-material [data-provide~="selectpicker"]', function ()
	{
		if ($(this).selectpicker('val').length === 0)
			materialNoFloat($(this));
	});

	$(document).on('loaded.bs.select', '.form-type-material [data-provide~="selectpicker"]', function ()
	{
		if ($(this).selectpicker('val').length > 0)
			materialDoFloat($(this));
	});

	function materialDoFloat(e)
	{
		if (e.parent('.input-group-input').length)
			e.parent('.input-group-input').addClass('do-float');
		else
			e.closest('.form-group').addClass("do-float");
	}

	function materialNoFloat(e)
	{
		if (e.parent('.input-group-input').length)
			e.parent('.input-group-input').removeClass('do-float');
		else
			e.closest('.form-group').removeClass("do-float");
	}
});

var sidebar = {};

sidebar.toggleFold = function() {
  $('body').toggleClass('sidebar-folded');
  app.toggleState('sidebar.folded');
}

sidebar.fold = function() {
  $('body').addClass('sidebar-folded');
  app.state('sidebar.folded', true);
}

sidebar.unfold = function() {
  $('body').removeClass('sidebar-folded');
  app.state('sidebar.folded', false);
}


sidebar.open = function() {
  $('body').addClass('sidebar-open').prepend('<div class="app-backdrop backdrop-sidebar"></div>');
}

sidebar.close = function() {
  $('body').removeClass('sidebar-open');
  $('.backdrop-sidebar').remove();
}

var app = {}

app.state = function(key, value) {
  if ( localStorage.theadmin === undefined ) {
    localStorage.theadmin = '{}';
  }

  var states = JSON.parse(localStorage.theadmin);
  if (arguments.length === 0) {
    return states;
  }
  else if (arguments.length === 1) {
    return states[key];
  }
  else if (arguments.length === 2 && app.defaults.saveState) {
    states[key] = value;
    localStorage.theadmin = JSON.stringify(states);
  }
}

app.toggleState = function(key) {
    var states = app.state();
    states[key] = !states[key];
    localStorage.theadmin = JSON.stringify(states);
}