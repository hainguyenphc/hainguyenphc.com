<?php declare(strict_types=1);

namespace PhpTuf\ComposerStager\Internal\Path\Value;

use PhpTuf\ComposerStager\API\Path\Value\PathListInterface;
use Symfony\Component\Filesystem\Path as SymfonyPath;

/**
 * @package Path
 *
 * @internal Don't depend directly on this class. It may be changed or removed at any time without notice.
 */
final class PathList implements PathListInterface
{
    /** @var array<string> */
    private array $paths;

    public function __construct(string ...$paths)
    {
        $this->paths = $paths;
    }

    /** @return array<string> */
    public function getAll(): array
    {
        return array_map(
            static fn ($path): string => SymfonyPath::canonicalize($path),
            $this->paths,
        );
    }

    public function add(string ...$paths): void
    {
        $this->paths = array_merge($this->paths, $paths);
    }
}
