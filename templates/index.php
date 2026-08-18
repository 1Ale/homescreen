<?php

/**
 * SPDX-FileCopyrightText: 2026 Alexandre Luvizotti Lopes
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

/** @var array $_ */
/** @var \OCP\IL10N $l */
?>
<main id="app-homescreen" class="homescreen">
	<?php if ($_['apps'] === []) { ?>
		<div class="emptycontent">
			<h2><?php p($l->t('No apps to show')); ?></h2>
			<p><?php p($l->t('Apps that appear in the Nextcloud app menu will show up here.')); ?></p>
		</div>
	<?php } else { ?>
		<ul class="homescreen__grid">
			<?php foreach ($_['apps'] as $app) { ?>
				<li>
					<a class="homescreen__app"
					   href="<?php p($app['href']); ?>"
					   <?php if (!empty($app['external'])) { ?>target="_blank" rel="noopener noreferrer"<?php } ?>
					   title="<?php p($app['name']); ?>">
						<span class="homescreen__circle">
							<?php if ($app['icon'] !== '') { ?>
								<img class="homescreen__icon"
									 src="<?php p($app['icon']); ?>"
									 alt=""
									 aria-hidden="true">
							<?php } ?>
							<?php if ($app['unread'] > 0) { ?>
								<span class="homescreen__unread" aria-hidden="true"></span>
							<?php } ?>
						</span>
						<span class="homescreen__label">
							<?php p($app['name']); ?>
							<?php if ($app['unread'] > 0) { ?>
								<span class="hidden-visually"><?php p($l->n('%n notification', '%n notifications', $app['unread'])); ?></span>
							<?php } ?>
						</span>
					</a>
				</li>
			<?php } ?>
		</ul>
	<?php } ?>
</main>
