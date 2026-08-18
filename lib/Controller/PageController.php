<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Alexandre Luvizotti Lopes
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\HomeScreen\Controller;

use OCA\HomeScreen\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\FrontpageRoute;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\OpenAPI;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IL10N;
use OCP\INavigationManager;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\Util;

#[OpenAPI(scope: OpenAPI::SCOPE_IGNORE)]
class PageController extends Controller {
	public function __construct(
		IRequest $request,
		private INavigationManager $navigationManager,
		private IURLGenerator $urlGenerator,
		private IL10N $l10n,
	) {
		parent::__construct(Application::APP_ID, $request);
	}

	#[NoCSRFRequired]
	#[NoAdminRequired]
	#[FrontpageRoute(verb: 'GET', url: '/')]
	public function index(): TemplateResponse {
		Util::addStyle(Application::APP_ID, 'homescreen');

		return new TemplateResponse(Application::APP_ID, 'index', [
			'apps' => $this->apps(),
			'id-app-content' => '#app-homescreen',
			'id-app-navigation' => null,
			'pageTitle' => $this->l10n->t('Home'),
		]);
	}

	/**
	 * Same entries as the header app menu, without this app.
	 *
	 * @return list<array{id: string, name: string, href: string, icon: string, unread: int, external: bool}>
	 */
	private function apps(): array {
		$ownHost = parse_url($this->urlGenerator->getAbsoluteURL('/'), PHP_URL_HOST);
		$apps = [];

		foreach ($this->navigationManager->getAll(INavigationManager::TYPE_APPS) as $entry) {
			if (($entry['id'] ?? '') === Application::APP_ID) {
				continue;
			}

			$href = (string)($entry['href'] ?? '');
			$host = parse_url($href, PHP_URL_HOST);

			$apps[] = [
				'id' => (string)($entry['id'] ?? ''),
				'name' => (string)($entry['name'] ?? ''),
				'href' => $href,
				'icon' => (string)($entry['icon'] ?? ''),
				'unread' => (int)($entry['unread'] ?? 0),
				'external' => is_string($host) && $host !== '' && $host !== $ownHost,
			];
		}

		return $apps;
	}
}
