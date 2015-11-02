<?php

namespace Parvula\Core\Router;

/**
 * Request
 *
 * @package Parvula
 * @version 0.5.0
 * @since 0.5.0
 * @author Fabien Sa
 * @license MIT License
 */
class Request
{
	public $body;

	/**
	 * @var string IP address
	 */
	public $ip;

	public $query;

	public $params;

	/**
	 * @var string
	 */
	public $uri;

	/**
	 * @var string Scheme (without the `://`)
	 */
	public $scheme;

	/**
	 * @var bool If server use a cryptographic protocol (like TLS/SSL)
	 */
	public $secureLayer;

	/**
	 * @var string User agent
	 */
	public $userAgent;


	/**
	 * Constructor
	 *
	 * @param array $server
	 * @param array $get
	 * @param array $post
	 * @param array $cookie
	 * @param array $files
	 */
	public function __construct(
		array $server,
		array $get,
		array $post,
		array $cookie,
		array $files) {

		// IP
		$this->ip = isset($server['REMOTE_ADDR']) ? $server['REMOTE_ADDR'] : '';

		// Query
		isset($server['QUERY_STRING']) ? parse_str($server['QUERY_STRING'], $this->query) : '';
		$this->query = (object) $this->query;

		$this->host = isset($server['HTTP_HOST']) ? $server['HTTP_HOST'] : '';

		$this->uri = isset($server['REQUEST_URI']) ? $server['REQUEST_URI'] : '';

		// http, https, ...
		$this->scheme = isset($server['REQUEST_SCHEME']) ? $server['REQUEST_SCHEME'] : '';

		$this->secureLayer = isset($server['HTTPS']) ? $server['HTTPS'] === 'on' : false;

		$this->userAgent = isset($server['HTTP_USER_AGENT']) ? $server['HTTP_USER_AGENT'] : '';

		// Body
		$this->body = (object) $post;
	}

}
