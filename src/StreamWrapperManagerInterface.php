<?php

namespace Codementality;

use League\Flysystem\FilesystemInterface;
//use League\Flysystem\Visibility;

/**
 * Contract for the StreamWrapperManager implementation.
 *
 * These methods govern the registration, retrieval and
 * discard (unregister) processes for managing stream wrappers.
 *
 * Note: because of the presence of static methods, we are using
 * an abstract class to otherwise define this interface.  We may
 * refactor this later, or we may not.
 */
abstract class StreamWrapperManagerInterface {

  public const LOCK_STORE = 'lock_store';
  public const LOCK_TTL = 'lock_ttl';

  public const IGNORE_VISIBILITY_ERRORS = 'ignore_visibility_errors';

  public const EMULATE_DIRECTORY_LAST_MODIFIED = 'emulate_directory_last_modified';

  public const UID = 'uid';
  public const GID = 'gid';

  public const VISIBILITY_FILE_PUBLIC = 'visibility_file_public';
  public const VISIBILITY_FILE_PRIVATE = 'visibility_file_private';
  public const VISIBILITY_DIRECTORY_PUBLIC = 'visibility_directory_public';
  public const VISIBILITY_DIRECTORY_PRIVATE = 'visibility_directory_private';
  public const VISIBILITY_DEFAULT_FOR_DIRECTORIES = 'visibility_default_for_directories';

  public const DEFAULT_CONFIGURATION = [
    self::LOCK_STORE => 'flock:///tmp',
    self::LOCK_TTL => 300,

    self::IGNORE_VISIBILITY_ERRORS => false,
    self::EMULATE_DIRECTORY_LAST_MODIFIED => false,

    self::UID => null,
    self::GID => null,

    self::VISIBILITY_FILE_PUBLIC => 0644,
    self::VISIBILITY_FILE_PRIVATE => 0600,
    self::VISIBILITY_DIRECTORY_PUBLIC => 0755,
    self::VISIBILITY_DIRECTORY_PRIVATE => 0700,
    // @todo replace with Visibility::PRIVATE for v3.0
    // @see https://github.com/m2mtech/flysystem-stream-wrapper/blob/main/src/FlysystemStreamWrapper.php#L52
    self::VISIBILITY_DEFAULT_FOR_DIRECTORIES => 'private',
  ];

  /**
   * Registers the stream wrapper schema if not already registered.
   *
   * @param string $schema
   *   The schema.
   * @param \League\Flysystem\FilesystemInterface $filesystem
   *   The League/Flysystem filesystem object.
   * @param array|null $configuration
   *   Optional configuration.
   * @param int $flags 
   *   Should be set to STREAM_IS_URL if schema is a URL schema. 
   *   Default is local stream.
   *
   * @return bool
   *   True if the schema was registered, false if not.
   */
  abstract public static function register($schema, FilesystemInterface $filesystem, array $configuration = null, $flags = 0);

  /**
   * Unregisters a stream wrapper.
   *
   * @param string $schema The schema.
   *
   * @return bool True if the schema was unregistered, false if not.
   */
  abstract public static function unregister($schema);

  /**
   * Unregisters all controlled stream wrappers.
   */
  abstract public static function unregisterAll();

  /**
   * Get registered schemas.
   *
   * @return array The list of registered schemas.
   */
  abstract public static function getRegisteredSchemas();

  /**
   * Determines if a schema is registered.
   *
   * @param string $schema The schema to check.
   *
   * @return bool True if it is registered, false if not.
   */
  abstract protected static function streamWrapperExists($schema);

  /**
   * Registers plugins on the filesystem.
   *
   * @param string $schema
   *   Schema for the streamwrapper.
   * @param League\Flysystem\FilesystemInterface $filesystem
   *   League\Flysystem Filesystem object.
   */
  abstract protected static function registerPlugins($schema, FilesystemInterface $filesystem);

}