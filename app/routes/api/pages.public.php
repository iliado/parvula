<?php

namespace Parvula;

use Exception;
use Parvula\Exceptions\IOException;

$pages = $app['pages'];

/**
 * @api {get} /pages Get all pages
 * @apiName Get all pages
 * @apiGroup Page
 *
 * @apiParam {string} [index] Optional You can pass `?index` to url to just have the slugs
 * @apiParam {string} [all] Optional You can pass `?all` to url to list all pages (parents and children)
 *
 * @apiSuccess (200) {array} pages An array of pages
 *
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *     [
 *       {"title": "home", "slug": "home", "content": "<h1>My home page</h1>..."},
 *       {"title": "about me", "slug": "about", "content": "..."}
 *     ]
 */
$this->get('', function ($req, $res) use ($pages) {
	// List of pages. Array<string> of slugs
	if (isset($req->getQueryParams()['index'])) {
		return $this->api->json($res, $pages->index());
	}

	$allPages = $pages
		->all()
		->sortBy('slug');
		// ->sortBy(function ($p) {
		// 	return $p->slug;
		// });

	// List all pages (with or without a parent)
	if (isset($req->getQueryParams()['all'])) {
		return $this->api->json($res, $allPages->toArray());
	}

	// List root pages, pages without a parent
	// $jwt = $this->encodeJWT($jwt);
	// return $this->api->json($res, $jwt);
	return $this->api->json($res, $allPages->withoutParent()->toArray());
})->setName('pages.index');

/**
 * @api {get} /pages/:slug Get a specific page
 * @apiName Get page
 * @apiGroup Page
 *
 * @apiParam {string} slug The slug of the page
 * @apiParam {string} [raw] Optional Query `?raw` to not parse the content.
 *
 * @apiSuccess (200) {Object} page A Page
 * @apiError (404) PageDoesNotExists This page does not exists
 *
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "title": "Home page",
 *       "slug": "home",
 *       "content": "&lt;h1>Home page<\/h1>"
 *     }
 *
 * @apiErrorExample {json} Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "error": "PageDoesNotExists",
 *       "message": "This page does not exists"
 *     }
 */
$this->get('/{slug:.+}', function ($req, $res, $args) use ($app, $pages) {
	if (isset($req->getQueryParams()['raw'])) {
		$pages->setRenderer($app['pageRendererRAW']);
	}

	if (false === $result = $pages->find($args['slug'])) {
		return $this->api->json($res, [
			'error' => 'PageDoesNotExists',
			'message' => 'This page does not exists'
		], 404);
	}

	return $this->api->json($res, $result->toArray());
})->setName('pages.show');
