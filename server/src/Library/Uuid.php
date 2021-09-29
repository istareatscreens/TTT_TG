<?php
/*
author: jchook 
source: https://github.com/jchook/uuid-v4/blob/master/lib/Uuid.php
*/

namespace Game\Library;

/**
 * UUID v4 Generator
 */
class Uuid
{
	// Buffering random_bytes() speeds up generation of many uuids at once
	const BUFFER_SIZE = 512;

	protected static $buf;
	protected static $bufIdx = self::BUFFER_SIZE;

	public static function v4(): string
	{
		$b = self::randomBytes(16);
		$b[6] = chr((ord($b[6]) & 0x0f) | 0x40);
		$b[8] = chr((ord($b[8]) & 0x3f) | 0x80);
		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($b), 4));
	}

	public static function v4bin(): string
	{
		$b = self::randomBytes(16);
		$b[6] = chr((ord($b[6]) & 0x0f) | 0x40);
		$b[8] = chr((ord($b[8]) & 0x3f) | 0x80);
		return $b;
	}

	protected static function randomBytes(int $n): string
	{
		if (self::$bufIdx + $n >= self::BUFFER_SIZE) {
			self::$buf = random_bytes(self::BUFFER_SIZE);
			self::$bufIdx = 0;
		}
		$idx = self::$bufIdx;
		self::$bufIdx += $n;
		return substr(self::$buf, $idx, $n);
	}

	public static function isUuid(string $uuid): bool
	{
		return is_string($uuid) && (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $uuid) !== 0);
	}
}
