<?php

namespace Dashifen\Pagination;

use Dashifen\Container\Container;
use Dashifen\Pagination\Exceptions\PaginationException;

/**
 * Class Pagination
 * @package Dashifen\Pagination
 * @property $previous
 * @property $next
 * @property $pages
 */
class Pagination extends Container {
	/**
	 * @var PaginationLink
	 */
	protected $previous;

	/**
	 * @var PaginationLink
	 */
	protected $next;

	/**
	 * @var PaginationLink[]
	 */
	protected $pages;

	/**
	 * setPrevious
	 *
	 * Sets the previous property.
	 *
	 * @param PaginationLink $previous
	 *
	 * @return void
	 */
	public function setPrevious(PaginationLink $previous): void {
		$this->previous = $previous;
	}

	/**
	 * setNext
	 *
	 * Sets the next property.
	 *
	 * @param PaginationLink $next
	 *
	 * @return void
	 */
	public function setNext(PaginationLink $next): void {
		$this->next = $next;
	}

	/**
	 * setPages
	 *
	 * Sets the pages property, which must be an array of PaginationLink
	 * objects.
	 *
	 * @param PaginationLink[] $pages
	 *
	 * @return void
	 * @throws PaginationException
	 */
	public function setPages(array $pages): void {
		foreach ($pages as $page) {
			if (!($page instanceof PaginationLink)) {
				throw new PaginationException("Invalid pagination link.",
					PaginationException::INVALID_PAGINATION_LINK);
			}
		}

		$this->pages = $pages;
	}
}
