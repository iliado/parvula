<?php

namespace Parvula\Model\Mapper;

use Parvula\Model\Page;
use Parvula\Model\Section;
use Parvula\Exception\IOException;
use Parvula\Exception\PageException;
use Parvula\PageRenderer\PageRendererInterface;

/**
 * Mongo pages mapper
 *
 * @package Parvula
 * @version 0.7.0
 * @since 0.7.0
 * @author psych0pat
 * @license MIT License
 */
class PagesMongo extends Pages
{

	/**
	 * Constructor
	 *
	 * @param PageRendererInterface $pageRenderer Page renderer
	 * @param string $folder Pages folder
	 * @param string $fileExtension File extension
	 * @param
	 */
	function __construct(PageRendererInterface $pageRenderer, $collection) {
		parent::__construct($pageRenderer);
		$this->collection = $collection;
	}

	public function getCollection() {
		return $this->collection;
	}

	/**
	 * Get a page object in html string
	 *
	 * @param string $pageUID Page unique ID
	 * @throws IOException If the page does not exists
	 * @return Page|bool Return the selected page if exists, false if not
	 */
	public function read($slug) {
		$page = $this->collection->findOne(['meta.slug' => $slug]);
		if (empty($page)) {
			return false;
		}

		return $this->renderer->parse($page);
	}

	/**
	 *
	 * @param string $pageUID Page unique ID
	 *
	 */
	private function exists($slug) {
		if ($this->read($slug)) {
			return true;
		}

		return false;
	}

	/**
	 * Create page object in "pageUID" file
	 *
	 * @param Page $page Page object
	 * @throws IOException If the destination folder is not writable
	 * @throws PageException If the page does not exists
	 * @return bool
	 */
	public function create($page) {
		if (!isset($page->slug)) {
			throw new IOException('Page cannot be created. It must have a slug');
		}

		if ($this->exists($page->slug)) {
			return false;
		}

		$page = [
			'meta' => $page->getMeta(),
			'content' => $page->content,
			'sections' => $page->sections
		];

		try {
			return $this->collection->insertOne($page)->getInsertedCount() > 0 ? true : false;
		} catch (Exception $e) {
			throw new IOException('Page cannot be created');
		}
	}

	/**
	 * Update page object
	 *
	 * @param string $pageUID Page unique ID
	 * @param Page $page Page object
	 * @throws PageException If the page is not valid
	 * @throws PageException If the page already exists
	 * @throws PageException If the page does not exists
	 * @return bool Return true if page updated
	 */
	public function update($pageUID, $page) {
		if (!$this->exists($pageUID)) {
			throw new PageException('Page `' . $pageUID . '` does not exists');
		}

		if (!isset($page->title, $page->slug)) {
			throw new PageException('Page not valid. Must have at least a `title` and a `slug`');
		}

		try {
			$res = $this->collection->replaceOne(
				['meta.slug' => $pageUID],
				[
					'content' => $page->content,
					'sections' => $page->sections,
					'meta' => $page->getMeta()
				]
			);

			if ($res->getModifiedCount() > 0) {
				return true;
			}
			return false;

		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Patch page
	 *
	 * @param string $pageUID
	 * @param array $infos Patch infos
	 * @return boolean True if the page was correctly patched
	 */
	public function patch($slug, array $infos) {
		if (!$this->exists($slug)) {
			throw new PageException('Page `' . $slug . '` does not exists');
		}

		$prototype = [];
		foreach ($infos as $key => $value) {
			if (in_array($key, ['content', 'sections'])) {
				$prototype[$key] = $value;
			} else {
				$prototype['meta.'.$key] = $value;
			}
		}

		try {
			$res = $this->collection->updateOne(
				['meta.slug' => $slug],
				['$set' => $prototype]
			);

			if ($res->getModifiedCount() > 0) {
				return true;
			}
			return false;

		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Delete a page
	 *
	 * @param string $pageUID
	 * @throws IOException If the page does not exists
	 * @return boolean If page is deleted
	 */
	public function delete($pageUID) {
		if (!is_null($this->collection->findOneAndDelete(['meta.slug' => $pageUID]))) {
			return true;
		}
		return false;
	}

	/**
	 * Index pages and get an array of pages slug
	 *
	 * @param boolean ($listHidden) List hidden files & folders
	 * @throws IOException If the pages directory does not exists
	 * @return array Array of pages paths
	 */
	public function index($listHidden = false) {
		$exceptions = [true];
		if ($listHidden) {
			$exceptions = [];
		}
		return $this->collection->distinct('meta.slug', ['meta.hidden' => ['$nin' => $exceptions]]);
	}
}
