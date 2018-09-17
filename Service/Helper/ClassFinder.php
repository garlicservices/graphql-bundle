<?php

namespace Garlic\GraphQL\Service\Helper;


use Metadata\Driver\FileLocator;

class ClassFinder
{
    /** @var string */
    private $rootPath;

    /**
     * ClassFinder constructor.
     * @param $rootPath
     */
    public function __construct(string $rootPath)
    {
        $this->rootPath = $rootPath;
    }

    /**
     * Get classes by path
     *
     * @param $path
     * @return array
     */
    public function getClassesByPath($path)
    {
        $allClasses = [];
        foreach ($this->findAllClasses([$this->rootPath."/$path"], 'php') as $class){
            $allClasses[] = trim($class,"0\\");
        }

        return $allClasses;
    }

    /**
     * Find all classes by path and extension
     *
     * @param array $dirs
     * @param string $extension
     * @return array
     */
    public function findAllClasses(array $dirs, string $extension)
    {
        $classes = array();
        foreach ($dirs as $prefix => $dir) {
            /** @var $iterator \RecursiveIteratorIterator|\SplFileInfo[] */
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );
            $nsPrefix = $prefix !== '' ? $prefix.'\\' : '';
            foreach ($iterator as $file) {
                if (($fileName = $file->getBasename('.'.$extension)) == $file->getBasename()) {
                    continue;
                }

                $classes[] = $nsPrefix.str_replace('.', '\\', $fileName);
            }
        }

        return $classes;
    }
}