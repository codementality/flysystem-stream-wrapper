<?php

namespace Codementality\FlysystemStreamWrapper;

class PosixUid extends Uid
{
    public function getUid()
    {
        if (\function_exists('posix_getuid')) {
            return (int) \posix_getuid();
        }

        if (\function_exists('getmyuid')) {
            return (int) \getmyuid();
        }

        // Some PHP-FPM environments expose neither POSIX nor getmy*()
        // functions. Fall back to a neutral value rather than terminating
        // the request with a fatal error.
        return 0;
    }

    public function getGid()
    {
        if (\function_exists('posix_getgid')) {
            return (int) \posix_getgid();
        }

        if (\function_exists('getmygid')) {
            return (int) \getmygid();
        }

        // Some PHP-FPM environments expose neither POSIX nor getmy*()
        // functions. Fall back to a neutral value rather than terminating
        // the request with a fatal error.
        return 0;
    }
}
