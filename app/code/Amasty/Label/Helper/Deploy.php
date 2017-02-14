<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


namespace Amasty\Label\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;

class Deploy extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write
     */
    protected $rootWrite;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Read
     */
    protected $rootRead;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Filesystem $filesystem
    ) {
        parent::__construct($context);

        $this->filesystem = $filesystem;
        $this->rootWrite = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $this->rootRead = $filesystem->getDirectoryRead(DirectoryList::ROOT);
    }

    public function deployPubFolder()
    {
        $from = $this->getRelativePathToModulePubFolder();
        $to = $this->getMediaRelativePath();
        $this->moveFilesFromTo($from, $to);
    }

    /**
     *
     * @param string $fromPath
     * @param string $toPath
     *
     * @return string
     */
    protected function moveFilesFromTo($fromPath, $toPath)
    {
        $files = $this->rootRead->readRecursively($fromPath);
        foreach ($files as $file) {
            try {
                $newFileName = $this->getNewFilePath($file, $fromPath, $toPath);
                if ($this->rootRead->isFile($file)) {
                    $this->rootWrite->copyFile($file, $newFileName);
                    $this->rootWrite->changePermissions($newFileName, 0660);
                } elseif ($this->rootRead->isDirectory($newFileName)) {
                    $this->rootWrite->changePermissions($newFileName, 0770);
                }
            }
            catch(\Exception $ex) {
                $this->_logger->warning($ex->getMessage());
            }
        }
    }

    protected function getNewFilePath($filePath, $fromPath, $toPath)
    {
        return str_replace($fromPath, $toPath, $filePath);
    }

    public function getAbsolutePathToModulePubFolder()
    {
        return __DIR__.'/../pub';
    }

    public function getMediaRelativePath()
    {
        return DirectoryList::PUB;
    }

    public function getRelativePathToModulePubFolder()
    {
        $absolutePath = $this->getAbsolutePathToModulePubFolder();
        return $this->rootRead->getRelativePath($absolutePath);
    }

}
