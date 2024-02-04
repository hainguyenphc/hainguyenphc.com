<?php declare(strict_types=1);

namespace PhpTuf\ComposerStager\Internal\Finder\Service;

use FilesystemIterator;
use PhpTuf\ComposerStager\API\Exception\IOException;
use PhpTuf\ComposerStager\API\Finder\Service\FileFinderInterface;
use PhpTuf\ComposerStager\API\Path\Factory\PathFactoryInterface;
use PhpTuf\ComposerStager\API\Path\Value\PathInterface;
use PhpTuf\ComposerStager\API\Path\Value\PathListInterface;
use PhpTuf\ComposerStager\API\Translation\Factory\TranslatableFactoryInterface;
use PhpTuf\ComposerStager\Internal\Path\Value\PathList;
use PhpTuf\ComposerStager\Internal\Translation\Factory\TranslatableAwareTrait;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use UnexpectedValueException as PhpUnexpectedValueException;

/**
 * @package Finder
 *
 * @internal Don't depend directly on this class. It may be changed or removed at any time without notice.
 */
final class FileFinder implements FileFinderInterface
{
    use TranslatableAwareTrait;

    public function __construct(
        private readonly PathFactoryInterface $pathFactory,
        TranslatableFactoryInterface $translatableFactory,
    ) {
        $this->setTranslatableFactory($translatableFactory);
    }

    public function find(PathInterface $directory, ?PathListInterface $exclusions = null): array
    {
        $exclusions ??= new PathList();

        $directoryIterator = $this->getRecursiveDirectoryIterator($directory->absolute());

        // Resolve the exclusions relative to the search directory.
        $exclusions = array_map(fn ($path): string => $this->pathFactory->create($path)
            ->relative($directory), $exclusions->getAll());

        // Apply exclusions. On the surface, it may look like individual descendants
        // of an excluded directory, i.e., files "underneath" or "inside" it, won't
        // be excluded because they aren't individually in the array in order to be
        // matched. But because the directory iterator is recursive, their excluded
        // ancestor WILL BE found, and they will be excluded by extension.
        $filterIterator = new RecursiveCallbackFilterIterator($directoryIterator, static fn (
            string $foundPathname,
        ): bool => !in_array($foundPathname, $exclusions, true));

        /** @var \Traversable<string> $iterator */
        $iterator = new RecursiveIteratorIterator($filterIterator);

        // The iterator must be converted to a flat list of pathnames rather
        // than returned whole because its element order is uncertain, leading to
        // unpredictable behavior in some use cases. For example, a client that
        // uses the result for file deletion would fail when it sometimes tried
        // to delete files after their ancestors had already been deleted.
        $files = iterator_to_array($iterator);

        sort($files);

        return $files;
    }

    /**
     * @throws \PhpTuf\ComposerStager\API\Exception\IOException
     *   If the directory cannot be found or is not actually a directory.
     *
     * @codeCoverageIgnore It's theoretically possible for RecursiveDirectoryIterator
     *   to throw an exception here (because the given directory has disappeared)
     *   but extremely unlikely, and it's infeasible to simulate in automated
     *   tests--at least without way more trouble than it's worth.
     */
    private function getRecursiveDirectoryIterator(string $directory): RecursiveDirectoryIterator
    {
        try {
            return new RecursiveDirectoryIterator(
                $directory,
                FilesystemIterator::CURRENT_AS_PATHNAME | FilesystemIterator::SKIP_DOTS,
            );
        } catch (PhpUnexpectedValueException $e) {
            // @todo Find a way to add test coverage for this.
            throw new IOException($this->t(
                'The directory cannot be found or is not a directory at %path.',
                $this->p(['%path' => $directory]),
                $this->d()->exceptions(),
            ), 0, $e);
        }
    }
}
