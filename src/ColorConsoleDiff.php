<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat;

use const PHP_EOL;

/**
 * Inspired by https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/master/src/Differ/DiffConsoleFormatter.php.
 */
final readonly class ColorConsoleDiff
{
    /**
     * @var string
     *
     * @see https://regex101.com/r/CMlwa8/1
     */
    private const string AT_START_REGEX = '#^(@.*)#';

    /**
     * @var string
     *
     * @see https://regex101.com/r/xwywpa/1
     */
    private const string MINUT_START_REGEX = '#^(\\-.*)#';

    /**
     * @var string
     *
     * @see https://regex101.com/r/qduj2O/1
     */
    private const string NEWLINES_REGEX = "#\n\r|\n#";

    /**
     * @var string
     *
     * @see https://regex101.com/r/ovLMDF/1
     */
    private const string PLUS_START_REGEX = '#^(\\+.*)#';

    private string $template;

    public function __construct()
    {
        $this->template = \sprintf('%s%%s%s' . PHP_EOL, PHP_EOL, PHP_EOL);
    }

    public function format(string $diff): string
    {
        return $this->formatWithTemplate($diff, $this->template);
    }

    private function formatWithTemplate(string $diff, string $template): string
    {
        $escapedDiff = self::escape(\rtrim($diff));
        $escapedDiffLines = \preg_split(self::NEWLINES_REGEX, $escapedDiff);
        // remove description of added + remove; obvious on diffs
        //        foreach ($escapedDiffLines as $key => $escapedDiffLine) {
        //            if ($escapedDiffLine === '--- Original') {
        //                unset($escapedDiffLines[$key]);
        //            }
        //            if ($escapedDiffLine === '+++ New') {
        //                unset($escapedDiffLines[$key]);
        //            }
        //        }
        return \sprintf($template, \implode(PHP_EOL, \array_map(function (string $string): string {
            $string = $this->makePlusLinesGreen($string);
            $string = $this->makeMinusLinesRed($string);
            $string = $this->makeAtNoteCyan($string);
            if ($string === ' ') {
                return '';
            }

            return $string;
        }, $escapedDiffLines)));
    }

    private function makeAtNoteCyan(string $string): string
    {
        return \preg_replace(self::AT_START_REGEX, '<fg=cyan>$1</fg=cyan>', $string);
    }

    private function makeMinusLinesRed(string $string): string
    {
        return \preg_replace(self::MINUT_START_REGEX, '<fg=red>$1</fg=red>', $string);
    }

    private function makePlusLinesGreen(string $string): string
    {
        return \preg_replace(self::PLUS_START_REGEX, '<fg=green>$1</fg=green>', $string);
    }

    /**
     * Escapes "<" and ">" special chars in given text.
     */
    public static function escape(string $text): string
    {
        $text = \preg_replace('/([^\\\\]|^)([<>])/', '$1\\\\$2', $text);

        return self::escapeTrailingBackslash($text);
    }

    /**
     * Escapes trailing "\" in given text.
     *
     * @internal
     */
    public static function escapeTrailingBackslash(string $text): string
    {
        if (\str_ends_with($text, '\\')) {
            $len = \mb_strlen($text);
            $text = \rtrim($text, '\\');
            $text = \str_replace("\0", '', $text);
            $text .= \str_repeat("\0", $len - \mb_strlen($text));
        }

        return $text;
    }
}
