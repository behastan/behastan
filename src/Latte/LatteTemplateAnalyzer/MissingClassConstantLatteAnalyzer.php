<?php

declare (strict_types=1);
namespace Symplify\EasyCI\Latte\LatteTemplateAnalyzer;

use EasyCI20220509\Nette\Utils\Strings;
use Symplify\EasyCI\Contract\ValueObject\FileErrorInterface;
use Symplify\EasyCI\Latte\Contract\LatteTemplateAnalyzerInterface;
use Symplify\EasyCI\ValueObject\FileError;
use EasyCI20220509\Symplify\SmartFileSystem\SmartFileInfo;
/**
 * @see \Symplify\EasyCI\Tests\Latte\LatteTemplateAnalyzer\MissingClassConstantLatteAnalyzer\MissingClassConstantLatteAnalyzerTest
 */
final class MissingClassConstantLatteAnalyzer implements \Symplify\EasyCI\Latte\Contract\LatteTemplateAnalyzerInterface
{
    /**
     * @see https://regex101.com/r/Wrfff2/9
     * @var string
     */
    private const CLASS_CONSTANT_REGEX = '#\\b(?<' . self::CLASS_CONSTANT_NAME_PART . '>[A-Z][\\w\\\\]+::[A-Z0-9_]+)\\b#m';
    /**
     * @var string
     */
    private const CLASS_CONSTANT_NAME_PART = 'class_constant_name';
    /**
     * @param SmartFileInfo[] $fileInfos
     * @return FileErrorInterface[]
     */
    public function analyze(array $fileInfos) : array
    {
        $templateErrors = [];
        foreach ($fileInfos as $fileInfo) {
            $matches = \EasyCI20220509\Nette\Utils\Strings::matchAll($fileInfo->getContents(), self::CLASS_CONSTANT_REGEX);
            if ($matches === []) {
                continue;
            }
            foreach ($matches as $match) {
                $classConstantName = (string) $match[self::CLASS_CONSTANT_NAME_PART];
                if (\defined($classConstantName)) {
                    continue;
                }
                $errorMessage = \sprintf('Class constant "%s" not found', $classConstantName);
                $templateErrors[] = new \Symplify\EasyCI\ValueObject\FileError($errorMessage, $fileInfo);
            }
        }
        return $templateErrors;
    }
}
