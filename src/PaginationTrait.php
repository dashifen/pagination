<?php

namespace Dashifen\Pagination;

use Dashifen\Repository\RepositoryException;
use WP_Query;

trait PaginationTrait {
	/**
	 * @var int
	 */
	protected $maximumPage = 0;

	/**
	 * @var int
	 */
	protected $currentPage = 0;

	/**
	 * @var int
	 */
	protected $linkCount = 0;

	/**
	 * @var int
	 */
	protected $halfCount = 0;

	/**
	 * getPagination
	 *
	 * Given a query, returns the necessary information for our templates
	 * to print pagination links.
	 *
	 * @param WP_Query $query
	 * @param int      $linkCount
	 *
	 * @return Pagination
	 * @throws RepositoryException
	 */
	protected function getPagination(WP_Query $query, int $linkCount = 5): Pagination {
		$this->maximumPage = $query->max_num_pages;
		$this->currentPage = $this->getCurrentPage();
		$this->linkCount = $this->getLinkCount($linkCount);

		// our half count is the floor of our link count's midpoint.  this
		// determines the ideal number of page links before and after the
		// current one assuming the current one is in the middle.  so, if
		// our link count is five, this calculates two (floor(5/2) = 2) as
		// the number of links to put before and after the current one.

		$this->halfCount = floor($this->linkCount / 2);

		// now, we're ready to return our pagination links.  we pass the
		// necessary data right to the Pagination Repository's constructor
		// as follows.  these data are all produced by the methods below.

		return new Pagination([
			"previous" => $this->getPreviousPaginationLink(),
			"next"     => $this->getNextPaginationLink(),
			"pages"    => array_map(function (int $pageNum) {

				// this array_map() transforms the array of integers we
				// receive from the getPageNumbers() method below into
				// links for the screen.

				return $this->getPaginationLink($pageNum);
			}, $this->getPageNumbers()),
		]);
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
	 * getLinkCount
	 *
	 * Returns a modified link count that is guaranteed to be odd.
	 *
	 * @param int $linkCount
	 *
	 * @return int
	 */
	protected function getLinkCount(int $linkCount): int {

		// we want our link count to be odd so that the same number of
		// pagination links can appear before and after it.  for example,
		// if link count is five, we show two prior pages, the current one,
		// and two upcoming ones (ideally).  if link count is even, then
		// we'll add one to it here so that we can enforce this symmetry,
		// but we'll raise a notice that we've done so.

		if ($linkCount % 2 === 0) {

			// trigger error raises a notice by default.  so, we just
			// construct our message (notice the pre-incremented $linkCount)
			// and we can use that function to put a note in the logs for
			// a programmer to find and fix one day.

			$msg = "Link count should be odd; increasing to %d.";
			$msg = sprintf($msg, ++$linkCount);
			trigger_error($msg);
		}

		return $linkCount;
	}

	/**
	 * getPreviousPaginationLink
	 *
	 * Returns the pagination link array for the prior page based on the
	 * current one.
	 *
	 * @return PaginationLink
	 * @throws RepositoryException
	 */
	protected function getPreviousPaginationLink(): PaginationLink {

		// the previous page is one less than the current one.  unless, of
		// course, that would be less than one.  this ternary statement makes
		// sure we don't go beneath the lower bound of our pagination, and
		// then we call the method above to get our pagination link array.

		$pageNum = $this->currentPage !== 1
			? ($this->currentPage - 1)
			: 1;

		return $this->getPaginationLink($pageNum);
	}

	/**
	 * getPaginationLink
	 *
	 * Given a page number and the current page, returns an object that will
	 * be used by our template to construct the pagination links.
	 *
	 * @param int $pageNum
	 *
	 * @return PaginationLink
	 * @throws RepositoryException
	 */
	protected function getPaginationLink(int $pageNum): PaginationLink {
		return new PaginationLink([
			"pageNumber" => $pageNum,
			"current"  => $this->currentPage === $pageNum,
			"link"       => get_pagenum_link($pageNum),
		]);
	}

	/**
	 * getNextPaginationLink
	 *
	 * Returns the pagination link array for the next page based on the current
	 * one and the number for the final one.
	 *
	 * @return PaginationLink
	 * @throws RepositoryException
	 */
	protected function getNextPaginationLink(): PaginationLink {

		// the next page is one past the current page, unless that would
		// exceed the maximum page number.  our ternary statement makes sure
		// that we don't going outside the upper bound of our pagination,
		// and then we use the method above to return the pagination link
		// array.

		$pageNum = $this->currentPage !== $this->maximumPage
			? $this->currentPage + 1
			: $this->maximumPage;

		return $this->getPaginationLink($pageNum);
	}

	/**
	 * getPageNumbers
	 *
	 * Returns an array of $this->linkCount numbers used for our pagination.
	 *
	 * @return array
	 */
	protected function getPageNumbers(): array {

		// to get our page numbers, we want to see if we should start from
		// one or start from our maximum page number.  we do this by seeing
		// if our current page is in the first half of [1 ... maximum page]
		// or the latter.  if it's in the first half, we start from one.

		$pages = $this->currentPage < ($this->maximumPage / 2)
			? $this->getPageNumbersFromStart()
			: $this->getPageNumbersFromEnd();

		sort($pages);
		return $pages;
	}

	/**
	 * getPageNumbersFromStart
	 *
	 * Returns a list of page numbers, starting from before our current page,
	 * that can be used for pagination.
	 *
	 * @return array
	 */
	protected function getPageNumbersFromStart(): array {
		$pages = [];

		for (

			// we start the required number of pages prior to the current
			// one.  this is how we make sure to put the current page in the
			// middle with a symmetrical count of pages on either side.  we
			// continue looping as long as we having added enough pages to
			// our array and we haven't hit the maximum page.

			$page = $this->currentPage - $this->halfCount;
			sizeof($pages) !== $this->linkCount && $page <= $this->maximumPage;
			$page++
		) {
			if ($page >= 1) {

				// as long as $page is a non-zero natural number, it
				// represents a page in this archive.  the if-block means
				// we skip any thing less than or equal to zero, which
				// wouldn't work anyway.

				$pages[] = $page;
			}
		}

		return $pages;
	}

	/**
	 * getPageNumbersFromEnd
	 *
	 * Returns a list of page numbers, starting from the maximum page number,
	 * and working backwards.
	 *
	 * @return array
	 */
	protected function getPageNumbersFromEnd(): array {
		$pages = [];

		// this is very similar to the prior method.  but, we start at the
		// end and go toward one.  otherwise, we might not end up with enough
		// numbers working from the bottom up.  for example, imagine that
		// we're on page 8 of 9.  the prior method would return 6, 7, 8, 9
		// but we actually want 5, 6, 7, 8, 9.  this method makes sure to
		// do that.

		for (

			// this time, we start after the current page and go backwards.
			// we add to the current page the number of pages we want in our
			// pagination array and decrement $page as we loop.  like above,
			// the loop continues as long as we haven't added enough to $pages
			// yet and we remain in the set of non-zero natural numbers.

			$page = $this->currentPage + $this->halfCount;
			sizeof($pages) !== $this->linkCount && $page >= 1; $page--) {
			if ($page <= $this->maximumPage) {

				// this time, we want ot make sure that we don't add page
				// numbers that are outside the upper boundary of our query.
				// therefore, we only add numbers that are less than or
				// equal to the maximum page number.

				$pages[] = $page;
			}
		}

		return $pages;
	}
}
