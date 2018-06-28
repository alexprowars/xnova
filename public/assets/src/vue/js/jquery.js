import store from '../store'
import app from 'app'
import { $post } from 'api'

const tooltip = () =>
{
	$('body').on('mouseenter', '.tooltip', function()
	{
		if (store.state.mobile)
			return;

		let _this = $(this);

		let status = false;

		try {
			status = _this.tooltipster('status');
		} catch (err) {}

		if (status)
			return;

		let maxWidth = null;

		if (_this.data('width') !== undefined)
			maxWidth = parseInt(_this.data('width'));

		_this.tooltipster({
			delay: 100,
			distance: 0,
			maxWidth: maxWidth,
			contentAsHTML: true,
			interactive: _this.hasClass('sticky'),
			functionInit: function(instance)
			{
				if (_this.hasClass('script'))
					instance.content(eval(_this.data('content')));
				else if (typeof _this.data('content') === "undefined")
					instance.content(_this.find('.tooltip-content'));
				else
					instance.content(_this.data('content'));
			}
		}).tooltipster('open');
	})
	.on('click', '.tooltip', function()
	{
		if (!store.state.mobile)
			return;

		let _this = $(this);

		let status = false;

		try {
			status = _this.tooltipster('status');
		} catch (err) {}

		if (!_this.hasClass('sticky') && status)
		{
			if (status.open)
				_this.tooltipster('close');
			else
				_this.tooltipster('open');

			return;
		}

		if (typeof _this.data('tooltipster-ns') !== 'undefined')
		{
			_this.tooltipster('open');
			return;
		}

		let maxWidth = null;

		if (_this.data('width') !== undefined)
			maxWidth = parseInt(_this.data('width'));

		_this.tooltipster({
			delay: 100,
			distance: 0,
			maxWidth: maxWidth,
			contentAsHTML: true,
			interactive: _this.hasClass('sticky'),
			functionInit: function(instance)
			{
				if (_this.hasClass('script'))
					instance.content(eval(_this.data('content')));
				else if (typeof _this.data('content') === "undefined")
					instance.content(_this.find('.tooltip-content'));
				else
					instance.content(_this.data('content'));
			}
		}).tooltipster('open');
	})
}

const swipe = () =>
{
	if (typeof swipe !== 'undefined' && !navigator.userAgent.match(/(\(iPod|\(iPhone|\(iPad)/))
	{
		$('body').swipe(
		{
			swipeLeft: function()
			{
				if ($('.menu-sidebar').hasClass('active'))
					$('.menu-toggle').click();
				else
					$('.planet-toggle').click();
			},
			swipeRight: function()
			{
				if ($('.planet-sidebar').hasClass('active'))
					$('.planet-toggle').click();
				else
					$('.menu-toggle').click();
			},
			threshold: 100,
			excludedElements: ".table-responsive",
			fallbackToMouseEvents: false,
			allowPageScroll: "auto"
		});
	}
}

const loaders = () =>
{
	$('body .main-content')
	.on('click', '.page-html a', function(e)
	{
		let el = $(this);
		let url = el.attr('href');

		if (!url || el.hasClass('skip') || url.indexOf('#') === 0)
			return false;

		if (url.indexOf('javascript') === 0 || url.indexOf('mailto') === 0 || url.indexOf('#') >= 0 || el.attr('target') === '_blank')
			return true;
		else
		{
			e.preventDefault();

			app.loader = true;
			app.$router.push(url);
		}

		return false;
	})
	.on('click', 'form:not(.noajax) input[type=submit], form[class!=noajax] button[type=submit]', function(e)
	{
		e.preventDefault();

		let button = $(this);
		let form = button.closest('form');

		form.append($('<input/>', {type: 'hidden', name: button.attr('name'), value: button.attr('value')}));
		form.submit();
	})
	.on('submit', 'form[class!=noajax]', function(e)
	{
		e.preventDefault();

		let form = $(this);

		app.loader = true;

		let formData = new FormData(this);

		$post(form.attr('action'), formData)
		.then((result) =>
		{
			store.commit('PAGE_LOAD', result)
			app.$router.replace(result['url'])
		}, () => {
			alert('Что-то пошло не так!? Попробуйте еще раз');
		})
		.then(() => {
			app.loader = false;
		})
	});
}

export {
	tooltip,
	swipe,
	loaders
}