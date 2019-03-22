<?php

namespace Pagination\Traits;

use Pagination\Containers\PaginationLink;
use Dashifen\Container\ContainerException;
use WP_Query;

trait PaginationTrait {
	/**
	 * getPagination
	 *
	 * Given a query, returns the necessary information for our templates
	 * to print pagination links.
	 *
	 * @param WP_Query $query
	 *
	 * @return array
	 * @throws ContainerException
	 */
	protected function getPagination(WP_Query $query): array {
		$maximumPage = $query->max_num_pages;
		$currentPage = $this->getCurrentPage();

		return [
			"previous" => $this->getPreviousPaginationLink($currentPage),
			"next"     => $this->getNextPaginationLink($currentPage, $maximumPage),
			"pages"    => array_map(function (int $pageNum) use ($currentPage) {

				// this array_map() call transforms the array of integers we
				// receive from the getPageNumbers() method below into links
				// for the screen.

				return $this->getPaginationLink($pageNum, $currentPage);
			}, $this->getPageNumbers($currentPage, $maximumPage)),
		];
	}

	/**
	 * getCurrentPage
	 *
	 * Returns the current page number.
	 *
	 * @return int
	 */
	protected function getCurrentPage(): int {
		$currentPage = (int) get_query_var("paged", 1);

		// WordPress uses a current page of zero to be the same as a
		// current page of one.  but, our loops below want the page to
		// be between one and our maximum number of pages inclusive.
		// so, if we're at page zero, we just return one; otherwise,
		// we return $currentPage.

		return $currentPage === 0 ? 1 : $currentPage;
	}

	/**
	 * getPreviousPaginationLink
	 *
	 * Returns the pagination link array for the prior page based on the
	 * current one.
	 *
	 * @param int $currentPage
	 *
	 * @return PaginationLink
	 * @throws ContainerException
	 */
	protected function getPreviousPaginationLink(int $currentPage): PaginationLink {

		// the previous page is one less than the current one.  unless, of
		// course, that would be less than one.  this ternary statement makes
		// sure we don't go beneath the lower bound of our pagination, and
		// then we call the method above to get our pagination link array.

		$pageNum = $currentPage === 1 ? 1 : ($currentPage - 1);
		return $this->getPaginationLink($pageNum, $currentPage);
	}

	/**
	 * getNextPaginationLink
	 *
	 * Returns the pagination link array for the next page based on the current
	 * one and the number for the final one.
	 *
	 * @param int $currentPage
	 * @param int $maximumPage
	 *
	 * @return PaginationLink
	 * @throws ContainerException
	 */
	protected function getNextPaginationLink(int $currentPage, int $maximumPage): PaginationLink {

		// the next page is one past the current page, unless that would
		// exceed the maximum page number.  our ternary statement makes sure
		// that we don't going outside the upper bound of our pagination,
		// and then we use the method above to return the pagination link
		// array.

		$pageNum = $currentPage === $maximumPage ? $maximumPage : ($currentPage + 1);
		return $this->getPaginationLink($pageNum, $currentPage);
	}


	/**
	 * getPaginationLink
	 *
	 * Given a page number and the current page, returns an array that will
	 * be used by our template to construct the pagination links.
	 *
	 * @param int $pageNum
	 * @param int $currentPage
	 *
	 * @return PaginationLink
	 * @throws ContainerException
	 */
	protected function getPaginationLink(int $pageNum, int $currentPage): PaginationLink {
		return new PaginationLink([
			"pageNumber" => $pageNum,
			"isCurrent"  => $currentPage === $pageNum,
			"link"       => get_pagenum_link($pageNum),
		]);
	}

	/**
	 * getPageNumbers
	 *
	 * Returns an array of five numbers used for our pagination.
	 *
	 * @param int $currentPage
	 *
	 * @param int $maximumPage
	 *
	 * @return array
	 */
	protected function getPageNumbers(int $currentPage, int $maximumPage): array {

		// our design calls for five pagination links.  that's enough
		// "room" for our current number in the middle and two on either
		// side of it.  if our current page is in the first half of our
		// pages, we start at the bottom and work up; otherwise we need
		// to work backwards to be sure we get the right number of pages
		// when we're at the beginning.

		$pages = $currentPage < ($maximumPage / 2)
			? $this->getPageNumbersFromStart($currentPage, $maximumPage)
			: $this->getPageNumbersFromEnd($currentPage, $maximumPage);

		sort($pages);
		return $pages;
	}

	/**
	 * getPageNumbersFromStart
	 *
	 * Returns a list of page numbers, starting from before our current page,
	 * that can be used for pagination.
	 *
	 * @param int $currentPage
	 * @param int $maximumPage
	 *
	 * @return array
	 */
	protected function getPageNumbersFromStart(int $currentPage, int $maximumPage): array {
		$numbers = [];

		// we start at $currentPage - 2 and we loop until we've added five
		// numbers to our array or we hit the maximum page.  $i represents
		// a page to show, so if it's less than 1 we skip it.  we start
		// from $currentPage - 2 because we want to show 5 numbers which,
		// in a perfect world, is the current page and two on either side.

		for ($i = $currentPage - 2; sizeof($numbers) !== 5 && $i <= $maximumPage; $i++) {
			if ($i >= 1) {
				$numbers[] = $i;
			}
		}

		return $numbers;
	}

	/**
	 * getPageNumbersFromEnd
	 *
	 * Returns a list of page numbers, starting from the maximum page number,
	 * and working backwards.
	 *
	 * @param int $currentPage
	 * @param int $maximumPage
	 *
	 * @return array
	 */
	protected function getPageNumbersFromEnd(int $currentPage, int $maximumPage): array {
		$numbers = [];

		// this is very similar to the prior method.  but, we start at the
		// and go toward one.  otherwise, we might not end up with enough
		// numbers working from the bottom up.  for example, imagine that
		// we're on page 8 of 9.  the prior method would return 6, 7, 8, 9
		// but we actually want 5, 6, 7, 8, 9.  this method makes sure to
		// do that.

		for ($i = $currentPage + 2; sizeof($numbers) !== 5 && $i >= 1; $i--) {
			if ($i <= $maximumPage) {
				$numbers[] = $i;
			}
		}

		return $numbers;
	}
}
