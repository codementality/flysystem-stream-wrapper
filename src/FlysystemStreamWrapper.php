<?php

namespace Codementality;

use Codementality\Flysystem\Exception\TriggerErrorException;
// @deprecated not part of Flysystem V3
use Codementality\Flysystem\Plugin\ForcedRename;
use Codementality\Flysystem\Plugin\Mkdir;
use Codementality\Flysystem\Plugin\Rmdir;
use Codementality\Flysystem\Plugin\Stat;
use Codementality\Flysystem\Plugin\Touch;
use Codementality\PhpStreamWrapperInterface;
use Codementality\StreamUtil;
use Codementality\StreamWrapperManagerInterface;
use League\Flysystem\AdapterInterface;
// use League\Flysysem\FilesystemAdapter;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
// use League\Flysystem\PathNormalizer;
use League\Flysystem\Util;
use \resource;

/**
 * An adapter for Flysystem to a PHP stream wrapper.
 */
class FlysystemStreamWrapper extends StreamWrapperManagerInterface implements PhpStreamWrapperInterface {

    /**
     * A flag to tell FlysystemStreamWrapper::url_stat() to ignore the size.
     *
     * @var int
     */
    const STREAM_URL_IGNORE_SIZE = 8;

    /**
     * The registered filesystems.
     *
     * @var \League\Flysystem\FilesystemInterface[]
     */
    protected static $filesystems = [];

    /**
     * Optional configuration.
     *
     * @var array
     */
    protected static $config = [];

    /**
     * The default configuration.
     *
     * @var array
     */
    protected static $defaultConfiguration = [
        'permissions' => [
            'dir' => [
                'private' => 0700,
                'public' => 0755,
            ],
            'file' => [
                'private' => 0600,
                'public' => 0644,
            ],
        ],
        'metadata' => ['timestamp', 'size', 'visibility'],
        'public_mask' => 0044,
    ];

    /**
     * The number of bytes that have been written since the last flush.
     *
     * @var int
     */
    protected $bytesWritten = 0;

    /**
     * The filesystem of the current stream wrapper.
     *
     * @var \League\Flysystem\FilesystemInterface
     */
    protected $filesystem;

    /**
     * A generic resource handle.
     *
     * @var mixed
     */
    protected $handle;

    /**
     * Whether the handle is in append mode.
     *
     * @var bool
     */
    protected $isAppendMode = false;

    /**
     * Whether the handle is read-only.
     *
     * The stream returned from Flysystem may not actually be read-only, This
     * ensures read-only behavior.
     *
     * @var bool
     */
    protected $isReadOnly = false;

    /**
     * Whether the handle is write-only.
     *
     * @var bool
     */
    protected $isWriteOnly = false;

    /**
     * A directory listing.
     *
     * @var array
     */
    protected $listing;

    /**
     * Whether this handle has been verified writable.
     *
     * @var bool
     */
    protected $needsCowCheck = false;

    /**
     * Whether the handle should be flushed.
     *
     * @var bool
     */
    protected $needsFlush = false;

    /**
     * The handle used for calls to stream_lock.
     *
     * @var resource
     */
    protected $lockHandle;

    /**
     * If stream_set_write_buffer() is called, the arguments.
     *
     * @var int
     */
    protected $streamWriteBuffer;

    /**
     * Instance URI (stream).
     *
     * A stream is referenced as "schema://target".
     *
     * @var string
     */
    protected $uri;

    /**
     * @var string
     */
    public $context;

  /**
   * {@inheritdoc}
   */
  public static function register($schema, FilesystemInterface $filesystem, array $configuration = null, $flags = 0) {
    if (static::streamWrapperExists($schema)) {
        return false;
    }

    static::$config[$schema] = $configuration ?: static::$defaultConfiguration;
    static::registerPlugins($schema, $filesystem);
    static::$filesystems[$schema] = $filesystem;

    return stream_wrapper_register($schema, __CLASS__, $flags);
  }

  /**
   * {@inheritdoc}
   */
  public static function unregister($schema) {
    if ( ! static::streamWrapperExists($schema)) {
        return false;
    }
    unset(static::$filesystems[$schema]);

    return stream_wrapper_unregister($schema);
  }

  /**
   * {@inheritdoc}
   */
  public static function unregisterAll() {
    foreach (static::getRegisteredSchemas() as $schema) {
        static::unregister($schema);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getRegisteredSchemas() {
    return array_keys(static::$filesystems);
  }

  /**
   * {@inheritdoc}
   */
  protected static function streamWrapperExists($schema) {
    return in_array($schema, stream_get_wrappers(), true);
  }

  /**
   * {@inheritdoc}
   */
  protected static function registerPlugins($schema, FilesystemInterface $filesystem) {
    //@deprecated and removed ForcedRename
    $filesystem->addPlugin(new ForcedRename());
    $filesystem->addPlugin(new Mkdir());
    $filesystem->addPlugin(new Rmdir());

    $stat = new Stat(
        static::$config[$schema]['permissions'],
        static::$config[$schema]['metadata']
    );

    $filesystem->addPlugin($stat);
    $filesystem->addPlugin(new Touch());
  }


  /**
   * {@inheritdoc}
   */
  public function dir_closedir(): bool {
    unset($this->listing);
    return true;
  }

  /**
   * {@inheritdoc}
   */
  public function dir_opendir($uri, $options): bool {
    $this->uri = $uri;
    // @deprecated, replace util with PathNormalizer, make this a
    // non-static call.
    $path = Util::normalizePath($this->getTarget());

    $this->listing = $this->invoke($this->getFilesystem(), 'listContents', [$path], 'opendir');

    if ($this->listing === false) {
        return false;
    }

    if ( ! $dirlen = strlen($path)) {
        return true;
    }

    // Remove the separator /.
    $dirlen++;

    // Remove directory prefix.
    foreach ($this->listing as $delta => $item) {
        $this->listing[$delta]['path'] = substr($item['path'], $dirlen);
    }

    reset($this->listing);

    return true;
  }

  /**
   * {@inheritdoc}
   */
  public function dir_readdir(): string|bool {
    $current = current($this->listing);
    next($this->listing);

    return $current ? $current['path'] : false;
  }

  /**
   * {@inheritdoc}
   */
  public function dir_rewinddir(): bool {
    reset($this->listing);
    return true;
  }

  /**
   * {@inheritdoc}
   */
  public function mkdir($uri, $mode, $options): bool {
    $this->uri = $uri;

    return $this->invoke($this->getFilesystem(), 'mkdir', [$this->getTarget(), $mode, $options]);
  }

  /**
   * {@inheritdoc}
   */
  public function rename($uri_from, $uri_to): bool {
    $this->uri = $uri_from;
    $args = [$this->getTarget($uri_from), $this->getTarget($uri_to)];
    // @deprecated replace forcedRename with move.  For v3.0 all rename is forced.
    // @deprecated replace rename with move.
    return $this->invoke($this->getFilesystem(), 'forcedRename', $args, 'rename');
  }

  /**
   * {@inheritdoc}
   */
  public function rmdir($uri, $options): bool {
    $this->uri = $uri;
    return $this->invoke($this->getFilesystem(), 'rmdir', [$this->getTarget(), $options]);
  }

  /**
   * {@inheritdoc}
   */
  public function stream_cast($cast_as): resource {
    return $this->handle;
  }

  /**
   * {@inheritdoc}
   */
  public function stream_close(): void {
    // PHP 7 doesn't call flush automatically anymore for truncate() or when
    // writing an empty file. We need to ensure that the handle gets pushed
    // as needed in that case.
    $this->stream_flush();
    if (is_resource($this->handle)) {
      fclose($this->handle);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function stream_eof(): bool {
    return feof($this->handle);
  }

  /**
   * {@inheritdoc}
   */
  public function stream_flush(): bool {
    if ( ! $this->needsFlush) {
        return true;
    }

    $this->needsFlush = false;
    $this->bytesWritten = 0;

    // Calling putStream() will rewind our handle. flush() shouldn't change
    // the position of the file.
    $pos = ftell($this->handle);

    $args = [$this->getTarget(), $this->handle];
    // @deprecated replace putStream with writeStream.
    $success = $this->invoke($this->getFilesystem(), 'putStream', $args, 'fflush');

    if (is_resource($this->handle)) {
        fseek($this->handle, $pos);
    }

    return $success;
  }

  /**
   * {@inheritdoc}
   */
  public function stream_lock($operation): bool {
    $operation = (int) $operation;

    if (($operation & \LOCK_UN) === \LOCK_UN) {
        return $this->releaseLock($operation);
    }

    // If the caller calls flock() twice, there's no reason to re-create the
    // lock handle.
    if (is_resource($this->lockHandle)) {
        return flock($this->lockHandle, $operation);
    }

    $this->lockHandle = $this->openLockHandle();

    return is_resource($this->lockHandle) && flock($this->lockHandle, $operation);
  }

  /**
   * {@inheritdoc}
   */
  public function stream_metadata($uri, $option, $value): bool {
    $this->uri = $uri;

    switch ($option) {
      case STREAM_META_ACCESS:
        $permissions = octdec(substr(decoct($value), -4));
        $is_public = $permissions & $this->getConfiguration('public_mask');
        // @AdapterInterface is deprecated, use PathNormalizer for v3
        $visibility =  $is_public ? AdapterInterface::VISIBILITY_PUBLIC : AdapterInterface::VISIBILITY_PRIVATE;

        try {
          return $this->getFilesystem()->setVisibility($this->getTarget(), $visibility);
        } catch (\LogicException $e) {
          // The adapter doesn't support visibility.
        } catch (\Exception $e) {
          $this->triggerError('chmod', $e);
          return false;
        }
        return true;

      case STREAM_META_TOUCH:
        return $this->invoke($this->getFilesystem(), 'touch', [$this->getTarget()]);

      default:
        return false;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function stream_open($uri, $mode, $options, &$opened_path): bool {
    $this->uri = $uri;
    $path = $this->getTarget();
    //@deprecated, replace.
    $this->isReadOnly = StreamUtil::modeIsReadOnly($mode);
    $this->isWriteOnly = StreamUtil::modeIsWriteOnly($mode);
    $this->isAppendMode = StreamUtil::modeIsAppendable($mode);

    $this->handle = $this->invoke($this, 'getStream', [$path, $mode], 'fopen');

    if ($this->handle && $options & STREAM_USE_PATH) {
        $opened_path = $path;
    }

    return is_resource($this->handle);
  }

  /**
   * {@inheritdoc}
   */
  public function stream_read($count): string {
    if ($this->isWriteOnly) {
      return '';
    }
    return fread($this->handle, $count);
  }

  /**
   * {@inheritdoc}
   */
  public function stream_seek($offset, $whence = SEEK_SET): bool {
    return fseek($this->handle, $offset, $whence) === 0;
  }

  /**
   * {@inheritdoc}
   */
  public function stream_set_option($option, $arg1, $arg2): bool {
    switch ($option) {
      case STREAM_OPTION_BLOCKING:
        // This works for the local adapter. It doesn't do anything for
        // memory streams.
        return stream_set_blocking($this->handle, $arg1);

      case STREAM_OPTION_READ_TIMEOUT:
        return  stream_set_timeout($this->handle, $arg1, $arg2);

      case STREAM_OPTION_READ_BUFFER:
        if ($arg1 === STREAM_BUFFER_NONE) {
          return stream_set_read_buffer($this->handle, 0) === 0;
        }

        return stream_set_read_buffer($this->handle, $arg2) === 0;

      case STREAM_OPTION_WRITE_BUFFER:
        $this->streamWriteBuffer = $arg1 === STREAM_BUFFER_NONE ? 0 : $arg2;

        return true;
    }

    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function stream_stat(): array {
    // Get metadata from original file.
    $stat = $this->url_stat($this->uri, static::STREAM_URL_IGNORE_SIZE | STREAM_URL_STAT_QUIET) ?: [];

    // Newly created file.
    if (empty($stat['mode'])) {
      $stat['mode'] = 0100000 + $this->getConfiguration('permissions')['file']['public'];
      $stat[2] = $stat['mode'];
    }

    // Use the size of our handle, since it could have been written to or
    // truncated.
    // @deprecated replace getSize with fileSize.
    $stat['size'] = $stat[7] = StreamUtil::getSize($this->handle);

    return $stat;
  }

  /**
   * {@inheritdoc}
   */
  public function stream_tell(): int {
    if ($this->isAppendMode) {
      return 0;
    }
    return ftell($this->handle);
  }

  /**
   * {@inheritdoc}
   */
  public function stream_truncate($new_size): bool {
    if ($this->isReadOnly) {
      return false;
    }
    $this->needsFlush = true;
    $this->ensureWritableHandle();

    return ftruncate($this->handle, $new_size);
  }

  /**
   * {@inheritdoc}
   */
  public function stream_write($data): int {
    if ($this->isReadOnly) {
        return 0;
    }
    $this->needsFlush = true;
    $this->ensureWritableHandle();

    // Enforce append semantics.
    if ($this->isAppendMode) {
        StreamUtil::trySeek($this->handle, 0, SEEK_END);
    }

    $written = fwrite($this->handle, $data);
    $this->bytesWritten += $written;

    if (isset($this->streamWriteBuffer) && $this->bytesWritten >= $this->streamWriteBuffer) {
        $this->stream_flush();
    }

    return $written;
  }

  /**
   * {@inheritdoc}
   */
  public function unlink($uri): bool {
    $this->uri = $uri;

    return $this->invoke($this->getFilesystem(), 'delete', [$this->getTarget()], 'unlink');
  }

  /**
   * {@inheritdoc}
   */
  public function url_stat($uri, $flags): array|false {
    $this->uri = $uri;

    try {
      return $this->getFilesystem()->stat($this->getTarget(), $flags);
    } catch (FileNotFoundException $e) {
      // File doesn't exist.
      if ( ! ($flags & STREAM_URL_STAT_QUIET)) {
        $this->triggerError('stat', $e);
      }
    } catch (\Exception $e) {
      $this->triggerError('stat', $e);
    }

    return false;
  }

  /**
   * Returns a stream for a given path and mode.
   *
   * @param string $path
   *   The path to open.
   * @param string $mode
   *   The mode to open the stream in.
   *
   * @throws \League\Flysystem\FileNotFoundException
   */
  protected function getStream($path, $mode): mixed {
    switch ($mode[0]) {
      case 'r':
        $this->needsCowCheck = true;
        return $this->getFilesystem()->readStream($path);
      case 'w':
        $this->needsFlush = true;
        return fopen('php://temp', 'w+b');
      case 'a':
        return $this->getAppendStream($path);
      case 'x':
        return $this->getXStream($path);
      case 'c':
        return $this->getWritableStream($path);
      }
      return false;
  }

  /**
   * Returns a writable stream for a given path and mode.
   *
   * @param string $path
   *   The path to open.
   */
  protected function getWritableStream($path): mixed {
    try {
      $handle = $this->getFilesystem()->readStream($path);
      $this->needsCowCheck = true;
    } catch (FileNotFoundException $e) {
      $handle = fopen('php://temp', 'w+b');
      $this->needsFlush = true;
    }

    return $handle;
  }

  /**
   * Returns an appendable stream for a given path and mode.
   *
   * @param string $path
   *   The path to open.
   */
  protected function getAppendStream($path): mixed {

    if ($handle = $this->getWritableStream($path)) {
        StreamUtil::trySeek($handle, 0, SEEK_END);
    }

    return $handle;
  }

  /**
   * Returns a writable stream for a given path and mode.
   *
   * Triggers a warning if the file exists.
   *
   * @param string $path
   *   The path to open.
   */
  protected function getXStream($path): mixed {
    // @deprecated replace has with fileExists.
    if ($this->getFilesystem()->has($path)) {
        trigger_error('fopen(): failed to open stream: File exists', E_USER_WARNING);

        return false;
    }

    $this->needsFlush = true;

    return fopen('php://temp', 'w+b');
  }

  /**
   * Guarantees that the handle is writable.
   */
  protected function ensureWritableHandle(): void {
    if ( ! $this->needsCowCheck) {
      return;
    }

    $this->needsCowCheck = false;

    if (StreamUtil::isWritable($this->handle)) {
      return;
    }

    $this->handle = StreamUtil::copy($this->handle);
  }

  /**
   * Returns the schema from the internal URI.
   */
  protected function getSchema(): string {
    return substr($this->uri, 0, strpos($this->uri, '://'));
  }

  /**
   * Returns the local writable target of the resource within the stream.
   *
   * @param string|null $uri
   *   The URI.
   */
  protected function getTarget($uri = null): string {
    if ( ! isset($uri)) {
      $uri = $this->uri;
    }

    $target = substr($uri, strpos($uri, '://') + 3);

    return $target === false ? '' : $target;
  }

  /**
   * Returns the configuration.
   *
   * @param string|null $key 
   *   The optional configuration key.
   */
  protected function getConfiguration($key = null): array|int {
    return $key ? static::$config[$this->getSchema()][$key] : static::$config[$this->getSchema()];
  }

  /**
   * Returns the filesystem.
   */
  protected function getFilesystem(): FilesystemInterface {
    if (isset($this->filesystem)) {
        return $this->filesystem;
    }

    $this->filesystem = static::$filesystems[$this->getSchema()];

    return $this->filesystem;
  }

  /**
   * Calls a method on an object, catching any exceptions.
   *
   * @param object $object
   *   The object to call the method on.
   * @param string $method
   *   The method name.
   * @param array $args
   *   The arguments to the method.
   * @param string|null $errorname
   *   The name of the calling function.
   */
  protected function invoke($object, $method, array $args, $errorname = null): mixed {
    try {
      return call_user_func_array([$object, $method], $args);
    } catch (\Exception $e) {
      $errorname = $errorname ?: $method;
      $this->triggerError($errorname, $e);
    }

    return false;
}

  /**
   * Calls trigger_error(), printing the appropriate message.
   *
   * @param string $function
   *   Callback function.
   * @param \Exception $e
   *   Exception to throw.
   */
  protected function triggerError($function, \Exception $e): void {
    if ($e instanceof TriggerErrorException) {
      trigger_error($e->formatMessage($function), E_USER_WARNING);

      return;
    }

    switch (get_class($e)) {
      case 'League\Flysystem\FileNotFoundException':
        trigger_error(sprintf('%s(): No such file or directory', $function), E_USER_WARNING);
        return;

      case 'League\Flysystem\RootViolationException':
        trigger_error(sprintf('%s(): Cannot remove the root directory', $function), E_USER_WARNING);
        return;
    }

    // Don't allow any exceptions to leak.
    trigger_error($e->getMessage(), E_USER_WARNING);
  }

  /**
   * Creates an advisory lock handle.
   */
  protected function openLockHandle(): mixed {
    // PHP allows periods, '.', to be scheme names. Normalize the scheme
    // name to something that won't cause problems. Also, avoid problems
    // with case-insensitive filesystems. We use bin2hex() rather than a
    // hashing function since most scheme names are small, and bin2hex()
    // only doubles the string length.
    $sub_dir = bin2hex($this->getSchema());

    // Since we're flattening out whole filesystems, at least create a
    // sub-directory for each scheme to attempt to reduce the number of
    // files per directory.
    $temp_dir = sys_get_temp_dir() . '/flysystem-stream-wrapper/' . $sub_dir;

    // Race free directory creation. If @mkdir() fails, fopen() will fail
    // later, so there's no reason to test again.
    ! is_dir($temp_dir) && @mkdir($temp_dir, 0777, true);

    // Normalize paths so that locks are consistent.
    // We are using sha1() to avoid the file name limits, and case
    // insensitivity on Windows. This is not security sensitive.
    // @deprecated, replace.
    $lock_key = sha1(Util::normalizePath($this->getTarget()));

    // Relay the lock to a real filesystem lock.
    return fopen($temp_dir . '/' . $lock_key, 'c');
  }

  /**
   * Releases the advisory lock.
   *
   * @param int $operation
   *   Operation to perform.
   *
   * @see FlysystemStreamWrapper::stream_lock()
   */
  protected function releaseLock($operation): bool {
    $exists = is_resource($this->lockHandle);

    $success = $exists && flock($this->lockHandle, $operation);

    $exists && fclose($this->lockHandle);
    $this->lockHandle = null;

    return $success;
  }
}
