
<ul>
<?php while ($rsItems->fetch()) : ?>
	<li><a href="<?php echo html::escapeHTML($rsItems->url) ?>"><?php echo html::escapeHTML($rsItems->title) ?></a></li>
<?php endwhile; ?>
</ul>