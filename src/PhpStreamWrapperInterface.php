<?php

namespace Codementality;

use \resource;

/**
 * Interface definition consistent with php's StreamWrapper prototype.
 *
 * Defines the non-StreamWrapperManagerInterface contract for Flysystem
 * Stream Wrappers.
 */

interface PhpStreamWrapperInterface {

  /**
   * Closes the directory handle.
   */
  public function dir_closedir(): bool;

  /**
   * Opens a directory handle.
   *
   * @param string $uri
   *   The URL that was passed to opendir().
   * @param int $options
   *   Whether or not to enforce safe_mode (0x04).
   */
  public function dir_opendir($uri, $options): bool;

  /**
   * Reads an entry from directory handle.
   * 
   * Returns the next filename, or false if there is no next file.
   */
  public function dir_readdir(): string|bool;

  /**
   * Rewinds the directory handle.
   */
  public function dir_rewinddir(): bool;

  /**
   * Creates a directory.
   *
   * @param string $uri
   *   Directory to create.
   * @param int $mode
   *   Octal permissions to set on directory.
   * @param int $options
   *   Bitwise mask of values, @see https://www.php.net/manual/en/streamwrapper.mkdir.php
   */
  public function mkdir($uri, $mode, $options): bool;

  /**
   * Renames a file or directory.
   *
   * @param string $uri_from
   *   Url to the current file.
   * @param string $uri_to
   *   Url new name.
   */
  public function rename($uri_from, $uri_to): bool;

  /**
   * Removes a directory.
   *
   * @param string $uri
   *   The directory to be removed.
   * @param int $options
   *   Bitwise mask of values, @see https://www.php.net/manual/en/streamwrapper.rmdir.php
   *   
   */
  public function rmdir($uri, $options): bool;

  /**
   * Retrieves the underlying resource.
   *
   * @param int $cast_as
   *   Bitwise mask of values to pass, @see https://www.php.net/manual/en/streamwrapper.stream-cast.php
   *
   * Returns the underlying stream resource used by the wrapper, or false.
   */
  public function stream_cast($cast_as): resource;

  /**
   * Closes the resource.
   */
  public function stream_close(): void;

  /**
   * Tests for end-of-file on a file pointer.
   */
  public function stream_eof(): bool;

  /**
   * Flushes the output.
   */
  public function stream_flush(): bool;

  /**
   * Advisory file locking.
   *
   * @param int $operation
   *   One of the defined values, @see https://www.php.net/manual/en/streamwrapper.stream-lock.php
   */
  public function stream_lock($operation): bool;

  /**
   * Changes stream options.
   *
   * @param string $uri
   *   File path or URL to set metadata.
   * @param int $option
   *   One of the defined values, @see https://www.php.net/manual/en/streamwrapper.stream-metadata.php
   * @param mixed $value
   *   One of the defined value types,  https://www.php.net/manual/en/streamwrapper.stream-metadata.php
   *   Note:  If $option is not implemented should return FALSE.
   */
  public function stream_metadata($uri, $option, $value): bool;

  /**
   * Opens file or URL.
   *
   * @param string $uri
   *   URL passed to the original function.
   * @param string $mode
   *   Mode used to open the file, @see https://www.php.net/manual/en/function.fopen.php
   * @param int $options
   *   Additional flags set by the streams API. @see https://www.php.net/manual/en/streamwrapper.stream-
   * @param string &$opened_path
   *   The resource that was opened.
   */
  public function stream_open($uri, $mode, $options, &$opened_path): bool;

  /**
   * Reads from stream.
   *
   * @param int $count
   *   Number of bytes from the current position that should be returned.
   *
   * Returns the bytes read.
   */
  public function stream_read($count): string;

  /**
   * Seeks to specific location in a stream.
   *
   * @param int $offset
   *   The stream offset to seek.
   * @param int $whence
   *   Possible values to set, @see https://www.php.net/manual/en/streamwrapper.stream-seek.php
   */
  public function stream_seek($offset, $whence = SEEK_SET): bool;

  /**
   * Changes stream options.
   *
   * @param int $option
   *   One of the possible defined values.
   * @param int $arg1
   *   One of the defined values based on $option.
   * @param int $arg2
   *   One of the defined values based on $option.
   *
   * @see https://www.php.net/manual/en/streamwrapper.stream-set-option.php
   *   for documentation of the above values.
   */
  public function stream_set_option($option, $arg1, $arg2): bool;

  /**
   * Retrieves information about a file resource.
   *
   * @see fstat()
   */
  public function stream_stat(): array;

  /**
   * Retrieves the current position of a stream.
   */
  public function stream_tell(): int;

  /**
   * Truncates the stream.
   *
   * @param int $new_size
   *   The new size.
   */
  public function stream_truncate($new_size): bool;

  /**
   * Writes to the stream.
   *
   * @param string $data
   *   Data to be stored in the stream.
   * 
   * Returns the number of bytes that were successfully stored.
   */
  public function stream_write($data): int;

  /**
   * Deletes a file.
   *
   * @param string $uri
   *   The file URI to delete.
   */
  public function unlink($uri): bool;

  /**
   * Retrieves information about a file.
   *
   * @param string $uri
   *   The file path or URL to stat.
   * @param int $flags
   *  Additional flags set by the streams API, @see https://www.php.net/manual/en/streamwrapper.url-stat.php
   *
   * @see stat()
   */
  public function url_stat($uri, $flags): array|false;

}