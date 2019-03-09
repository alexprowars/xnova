const tooltip = () =>
{
	$('body').on('mouseenter', '.tooltip', function ()
	{
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
	if (typeof swipe === 'undefined')
		return;

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

export {
	tooltip,
	swipe,
}