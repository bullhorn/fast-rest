<?php
namespace Phalcon\FastRest\Api\Models;

class HttpStatusCode {
	const OK = 200;
	const UPDATED = 200;
	const CREATED = 201;
	const DELETED = 204;
	const NOT_CHANGED = 304;
	const INVALID_FIELD = 400;
	const ACCESS_DENIED = 401;
	const NOT_FOUND = 404;
	const NOT_ALLOWED = 405;
	const ILLEGAL_OPERATION = 409;
}