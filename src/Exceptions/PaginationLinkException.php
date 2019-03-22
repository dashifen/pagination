<?php

namespace Dashifen\Pagination\Exceptions;

use Dashifen\Exception\Exception;

class PaginationLinkException extends Exception {
	public const INVALID_PAGE_NUMBER = 1;
	public const INVALID_PAGE_LINK = 2;
}
