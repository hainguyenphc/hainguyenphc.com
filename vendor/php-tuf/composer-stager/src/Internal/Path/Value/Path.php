<?php declare(strict_types=1);

namespace PhpTuf\ComposerStager\Internal\Path\Value;

use PhpTuf\ComposerStager\API\Path\Value\PathInterface;
use PhpTuf\ComposerStager\Internal\Helper\PathHelper;
use Symfony\Component\Filesystem\Path as SymfonyPath;
use Throwable;

/**
 * @package Path
 *
 * @internal Don't depend directly on this class. It may be changed or removed at any time without notice.
 */
final class Path implements PathInterface
{
    private string $basePath;

    /**
     * @param string $path
     *   The path string may be absolute or relative to the current working
     *   directory as returned by `getcwd()` at runtime, e.g., "/var/www/example"
     *   or "example". Nothing needs to actually exist at the path.
     * @param \PhpTuf\ComposerStager\API\Path\Value\PathInterface|null $basePath
     *   Optionally override the base path used for relative paths.
     *   Nothing needs to actually exist at the path. Therefore, it is simply
     *   assumed to represent a directory, as opposed to a file--even if
     *   it has an extension, which is no guarantee of type.
     */
    public function __construct(protected readonly string $path, ?PathInterface $basePath = null)
    {
        // Especially since it accepts relative paths, an immutable path value
        // object should be immune to environmental details like the current
        // working directory. Cache the CWD at time of creation.
        $this->basePath = $basePath instanceof PathInterface
            ? $basePath->absolute()
            : $this->getcwd();
    }

    public function absolute(): string
    {
        return $this->doAbsolute($this->basePath);
    }

    public function isAbsolute(): bool
    {
        return PathHelper::isAbsolute($this->path);
    }

    public function relative(PathInterface $basePath): string
    {
        $basePathAbsolute = $basePath->absolute();

        return $this->doAbsolute($basePathAbsolute);
    }

    /**
     * In order to avoid class dependencies, PHP's internal getcwd() function is
     * called directly here.
     */
    private function getcwd(): string
    {
        // It is technically possible for getcwd() to fail and return false. (For
        // example, on some Unix variants, this check will fail if any one of the
        // parent directories does not have the readable or search mode set, even
        // if the current directory does.) But the likelihood is probably so slight
        // that it hardly seems worth cluttering up client code handling theoretical
        // IO exceptions. Cast the return value to a string for the purpose of
        // static analysis and move on.
        return (string) getcwd();
    }

    private function doAbsolute(string $basePath): string
    {
        try {
            $absolute = SymfonyPath::makeAbsolute($this->path, $basePath);
        } catch (Throwable) {
            // SymfonyPath throws an exception if the base path isn't absolute. That
            // shouldn't be possible here because, in order to get to this point in the
            // code, the base path must necessarily have been set to an absolute path in
            // the constructor--either explicitly or via `getcwd()`. Nevertheless, as a
            // matter of defensive programming, use an assertion and return the normalized
            // path, whatever it is, in case of the "impossible" in production.
            //
            // @todo It's probably better to throw an InvalidArgumentException, but that will
            //   have a widespread effect on error-handling, so come back to it in a follow-up.
            assert(false, sprintf('Base paths must be absolute. Got %s.', $basePath));

            /** @noinspection PhpUnreachableStatementInspection */
            return PathHelper::canonicalize($this->path);
        }

        return PathHelper::canonicalize($absolute);
    }
}
