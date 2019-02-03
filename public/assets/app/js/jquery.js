import store from '../store'
import app from 'app'
import Vue from 'vue'
import { $get } from 'api'

const tooltip = () =>
{
	$('body').on('mouseenter', '.tooltip', function ()
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
			functionInit (instance)
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
	.on('click', '.tooltip', function ()
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
			functionInit (instance)
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
	if (typeof swipe !== 'undefined' && !store.state.mobile)
	{
		$('body').swipe(
		{
			swipeLeft ()
			{
				if ($('.menu-sidebar').hasClass('active'))
					$('.menu-toggle').click();
				else
					$('.planet-toggle').click();
			},
			swipeRight ()
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

const popup = (title, url, width) =>
{
	if (store.state.mobile)
		return window.location.href = url.split('ajax').join('').split('popup').join('');

	if (width === undefined)
		width = 600;

	$.dialog({
		title: title,
		theme: 'dialog',
		useBootstrap: false,
		boxWidth: width,
		backgroundDismiss: true,
		animation: 'opacity',
		closeAnimation: 'opacity',
		animateFromElement: false,
		draggable: false,
		content ()
		{
			let promise = new $.Deferred();

			$get(url, {
				'popup': 'Y'
			})
			.then(result => {
				promise.resolve(result);
			})
			.catch((error) => {
				promise.reject(error)
			})

			promise.then((result) =>
			{
				if (title === '')
					this.setTitle(result.title);

				let component = app.$router.getMatchedComponents(url)

				if (component.length)
				{
					if (typeof component[0] === 'object')
					{
						let com = new (Vue.extend(Object.assign(component[0], {parent: app})))().$mount()

						if (com && com.$data.page !== undefined)
						{
							com.setPageData(result.page)

							if (typeof com.afterLoad === 'function')
							{
								com.$nextTick(() => {
									com.afterLoad()
								})
							}
						}

						this.setContent(com.$el, true);
					}
					else
					{
						component[0]().then((r) =>
						{
							let com = new (Vue.extend(Object.assign(r.default, {parent: app})))().$mount()

							if (com && com.$data.page !== undefined)
							{
								com.setPageData(result.page)

								if (typeof com.afterLoad === 'function')
								{
									com.$nextTick(() => {
										com.afterLoad()
									})
								}
							}

							this.setContent(com.$el, true);
						});
					}
				}
			});

			return promise.promise();
		}
	});
}

export {
	tooltip,
	swipe,
	popup
}