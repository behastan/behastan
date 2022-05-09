<?php

declare (strict_types=1);
namespace Symplify\EasyCI\Contract\Application;

use Symplify\EasyCI\ValueObject\FileError;
use EasyCI20220509\Symplify\SmartFileSystem\SmartFileInfo;
interface FileProcessorInterface
{
    /**
     * @param SmartFileInfo[] $fileInfos
     * @return FileError[]
     */
    public function processFileInfos(array $fileInfos) : array;
}
