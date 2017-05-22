{% if request.getServer('SERVER_NAME') == 'vk.xnova.su' %}
	<script src="//vk.com/js/api/xd_connection.js" type="text/javascript"></script>
	<script type="application/javascript">
		$(window).load(function()
		{
			  VK.init(function() { console.log('vk init success'); }, function() {}, '5.24');
		});
	</script>
{% endif %}

<!-- Yandex.Metrika counter -->
<script type="text/javascript">
    (function (d, w, c) {
        (w[c] = w[c] || []).push(function() {
            try {
                w.yaCounter25961143 = new Ya.Metrika({
                    id:25961143,
                    clickmap:true,
                    trackLinks:true,
                    accurateTrackBounce:true,
					webvisor:true
                });
            } catch(e) { }
        });

        var n = d.getElementsByTagName("script")[0],
            s = d.createElement("script"),
            f = function () { n.parentNode.insertBefore(s, n); };
        s.type = "text/javascript";
        s.async = true;
        s.src = "https://mc.yandex.ru/metrika/watch.js";

        if (w.opera == "[object Opera]") {
            d.addEventListener("DOMContentLoaded", f, false);
        } else { f(); }
    })(document, window, "yandex_metrika_callbacks");
</script>
<!-- /Yandex.Metrika counter -->
