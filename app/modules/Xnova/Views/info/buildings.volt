<table class="table">
	<? if (!$isPopup): ?>
	<tr>
		<td class="c" colspan="2"><?=$parse['name'] ?></td>
	</tr>
	<? endif; ?>
	<tr>
		<th>
			<table class="margin5">
				<tr>
					<td valign="top"><img src="<?=$this->url->getBaseUri() ?>assets/images/gebaeude/<?=$parse['image'] ?>.gif" class="info" align="top" height="120" width="120" alt=""></td>
					<td valign="top" class="text-xs-left"><?=$parse['description'] ?></td>
				</tr>
			</table>
		</th>
	</tr>
</table>