<?php
/*
 * This file is part of the flysystem-stream-wrapper package.
 *
 * (c) 2021-2023 m2m server software gmbh <tech@m2m.at>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Codementality\FlysystemStreamWrapper\Flysystem;

use League\Flysystem\FilesystemException;
use Codementality\FlysystemStreamWrapper\Flysystem\StreamCommand\ExceptionHandler;
use Codementality\FlysystemStreamWrapper\Flysystem\StreamCommand\StreamWriteCommand;
use Drupal\Core\StreamWrapper\PhpStreamWrapperInterface;

class StreamWrapper implements PhpStreamWrapperInterface {
  use ExceptionHandler;

  /** @var FileData */
  private $current;

  /** @var resource */
  public $context;

  public function __construct(FileData $current = null) {
    $thisCurrent = $current ?? new FileData();
    $this->current = $thisCurrent;
  }

  /**
   * @param array<int|string> $args
   *
   * @return array<int|string,int|string>|string|bool
   */
  public function __call(string $method, array $args) {
    $class = __NAMESPACE__.'\\StreamCommand\\'.str_replace('_', '', ucwords($method, '_')).'Command';
    if (class_exists($class)) {
      return $class::run($this->current, ...$args);
    }

    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function dir_closedir(): bool {
    unset($this->current->dirListing);

    return true;
  }

  /**
   * {@inheritdoc}
   */
  public function dir_opendir($path, $options): bool {
    return $this->__call('dir_opendir', [$path, $options]);
  }
    
  /**
   * {@inheritdoc}
   */
  public function dir_readdir(): string|false {
    return $this->__call('dir_readdir', []);
  }
  
  /**
   * {@inheritdoc}
   */
  public function dir_rewinddir(): bool {
    return $this->__call('dir_rewinddir', []);
  }
  
  /**
   * {@inheritdoc}
   */
  public function mkdir($path, $mode, $options): bool {
    return $this->__call('mkdir', [$path, $mode, $options]);
  }
  
  /**
   * {@inheritdoc}
   */
  public function rename($path_from, $path_to): bool {
    return $this->__call('rename', [$path_from, $path_to]);
  }

  /**
   * {@inheritdoc}
   */
  public function rmdir($path, $options): bool {
    return $this->__call('rmdir', [$path, $options]);
  }
  
  /**
   * {@inheritdoc}
   */
  public function stream_cast($cast_as): bool {
      return $this->__call('stream_cast', [$cast_as]);
  }
 
  /**
   * {@inheritdoc}
   */
  public function stream_close() {
    if (!is_resource($this->current->handle)) {
      return;
    }

    if ($this->current->workOnLocalCopy) {
      fflush($this->current->handle);
      rewind($this->current->handle);

      try {
        $this->current->filesystem->writeStream($this->current->file, $this->current->handle);
      } catch (FilesystemException $e) {
        trigger_error(
          'stream_close('.$this->current->path.') Unable to sync file : '.self::collectErrorMessage($e),
            E_USER_WARNING
          );
      }
    }

    fclose($this->current->handle);
  }

  /**
   * {@inheritdoc}
   */
  public function stream_eof(): bool {
    return $this->__call('stream_eof', []);
  }

  /**
   * {@inheritdoc}
   */
  public function stream_flush(): bool {
    if (!is_resource($this->current->handle)) {
      trigger_error(
        'stream_flush(): Supplied resource is not a valid stream resource', E_USER_WARNING
      );
      return false;
    }

    $success = fflush($this->current->handle);

    if ($this->current->workOnLocalCopy) {
      fflush($this->current->handle);
      $currentPosition = ftell($this->current->handle);
      rewind($this->current->handle);

      try {
        $this->current->filesystem->writeStream($this->current->file, $this->current->handle);
      } catch (FilesystemException $e) {
        trigger_error(
          'stream_flush('.$this->current->path.') Unable to sync file : '.self::collectErrorMessage($e), E_USER_WARNING);
        $success = false;
      }

      if (false !== $currentPosition) {
        if (is_resource($this->current->handle)) {
          fseek($this->current->handle, $currentPosition);
        }
      }
    }

    $this->current->bytesWritten = 0;

    return $success;
  }

  /**
   * {@inheritdoc}
   */
  public function stream_lock($operation): bool {
    return $this->__call('stream_lock', [$operation]);
  }

  /**
   * {@inheritdoc}
   */
  public function stream_metadata($path, $option, $value): bool {
    return $this->__call('stream_metadata', [$path, $option, $value]);
  }

  /**
   * {@inheritdoc}
   */
  public function stream_open($path, $mode, $options, &$opened_path): bool {
    return $this->__call('stream_open', [$path, $mode, $options, &$opened_path]);
  }

  /**
   * {@inheritdoc}
   */
  public function stream_read($count): string|false {
    return $this->__call('stream_read', [$count]);
  }

  /**
   * {@inheritdoc}
   */
  public function stream_seek($offset, $whence = SEEK_SET): bool {
    return $this->__call('stream_seek', [$offset, $whence]);
  }

  /**
   * {@inheritdoc}
   */
  public function stream_set_option($option, $arg1, $arg2): bool {
    return $this->__call('stream_set_option', [$option, $arg1, $arg2]);
  }

  /**
   * {@inheritdoc}
   */    
  public function stream_stat(): array|false {
    return $this->url_stat($this->current->path, 0);
  }

  /**
   * {@inheritdoc}
   */
  public function stream_tell(): int {
    return $this->__call('stream_tell', []);
  }

  /**
   * {@inheritdoc}
   */
  public function stream_truncate($new_size): bool {
    return $this->__call('stream_truncate', [$new_size]);
  }

  /**
   * {@inheritdoc}
   */
  public function stream_write($data): int {
    $size = StreamWriteCommand::run($this->current, $data);

    if ($this->current->writeBufferSize && $this->current->bytesWritten >= $this->current->writeBufferSize) {
      $this->stream_flush();
    }

    return (int) $size;
  }

  /**
   * {@inheritdoc}
   */
  public function unlink($path): bool {
    return $this->__call('unlink', [$path]);
  }

  /**
   * {@inheritdoc}
   */
  public function url_stat($path, $flags): array|false {
    return $this->__call('url_stat', [$path, $flags]);
  }

}
