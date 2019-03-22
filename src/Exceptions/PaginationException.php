<?php

namespace Dashifen\Pagination\Exceptions;

use Dashifen\Exception\Exception;

class PaginationException extends Exception {
	public const INVALID_PAGINATION_LINK = 1;
}
