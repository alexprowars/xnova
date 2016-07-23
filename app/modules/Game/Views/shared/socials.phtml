<? if ($_SERVER['SERVER_NAME'] == 'vk.xnova.su'): ?>
	<script src="//vk.com/js/api/xd_connection.js" type="text/javascript"></script>
	<script type="application/javascript">
		$(window).load(function()
		{
			  VK.init(function() { console.log('vk init success'); }, function() {}, '5.24');
		});
	</script>
<? endif; ?>

<?
	if ($this->request->has('apiconnection') && (!$this->session->has('OKAPI') || !isset($this->session->get('OKAPI')['apiconnection'])))
	{
		$_SESSION['OKAPI'] = Array
		(
			'api_server' 			=> $this->request->get('api_server'),
			'apiconnection' 		=> $this->request->get('apiconnection'),
			'session_secret_key' 	=> $this->request->get('session_secret_key'),
			'session_key' 			=> $this->request->get('session_key'),
			'logged_user_id' 		=> $this->request->get('logged_user_id'),
			'sig' 					=> $this->request->get('sig')
		);
	}
?>
<? if ((!$this->cookies->has($this->config->cookie->prefix.'_full') || $this->cookies->get($this->config->cookie->prefix.'_full') == 'N') && $this->session->has('OKAPI') && is_array($this->session->get('OKAPI'))): ?>
	<script src="<?=$this->session->get('OKAPI')['api_server'] ?>js/fapi5.js" type="text/javascript"></script>
	<script type="text/javascript">
		FAPI.init('<?=$this->session->get('OKAPI')['api_server'] ?>', '<?=$this->session->get('OKAPI')['apiconnection'] ?>',
			function()
			{
				//FAPI.UI.setWindowSize(800, 700);
			}
			, function()
			{
				alert("API initialization failed");
			}
		);
	</script>
<? endif; ?>

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
