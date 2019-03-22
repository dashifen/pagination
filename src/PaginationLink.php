<?php

namespace Dashifen\Pagination;

use Dashifen\Container\Container;
use Dashifen\Pagination\Exceptions\PaginationLinkException;

/**
 * Class PaginationLink
 * @package Pagination\Containers
 * @property $pageNumber
 * @property $current
 * @property $link
 */
class PaginationLink extends Container {
	/**
	 * @var int
	 */
	protected $pageNumber = 0;

	/**
	 * @var bool
	 */
	protected $current = false;

	/**
	 * @var string
	 */
	protected $link = "";

	/**
	 * setPageNumber
	 *
	 * Sets the page number property that must be a non-zero natural number.
	 *
	 * @param int $pageNumber
	 *
	 * @return void
	 * @throws PaginationLinkException
	 */
	public function setPageNumber(int $pageNumber): void {
		if ($pageNumber < 1) {
			throw new PaginationLinkException("Invalid page number: $pageNumber",
				PaginationLinkException::INVALID_PAGE_NUMBER);
		}

		$this->pageNumber = $pageNumber;
	}

	/**
	 * setCurrent
	 *
	 * Sets the current property.
	 *
	 * @param bool $current
	 *
	 * @return void
	 */
	public function setCurrent(bool $current): void {
		$this->current = $current;
	}

	/**
	 * setLink
	 *
	 * Sets the link property which must be a URL.
	 *
	 * @param string $link
	 *
	 * @return void
	 * @throws PaginationLinkException
	 */
	public function setLink(string $link): void {
		if (!filter_var($link, FILTER_VALIDATE_URL)) {
			throw new PaginationLinkException("Invalid linl $link",
				PaginationLinkException::INVALID_PAGE_LINK);
		}

		$this->link = $link;
	}
}
