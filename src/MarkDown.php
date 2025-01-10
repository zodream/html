<?php
declare(strict_types=1);
namespace Zodream\Html;
#
#
# Parsedown
# http://parsedown.org
#
# (c) Emanuil Rusev
# http://erusev.com
#
# For the full license information, view the LICENSE file that was distributed
# with this source code.
#
#

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

class MarkDown {

    public static function parse(string $text, bool $safeMode = true, array $lazyLoad = []): string {
        return (new static)->setSafeMode($safeMode)->setLazyLoad($lazyLoad)->text($text);
    }

    public function text(string $text): string {
        $elements = $this->textElements($text);
        # convert to markup
        $markup = $this->elements($elements);

        # trim line breaks
        return trim($markup, "\n");
    }

    protected function textElements(string $text): array {
        # make sure no definitions are set
        $this->definitionData = [];

        # standardize line breaks
        $text = str_replace(array("\r\n", "\r"), "\n", $text);

        # remove surrounding line breaks
        $text = trim($text, "\n");

        # split text into lines
        $lines = explode("\n", $text);

        # iterate through lines to identify blocks
        return $this->linesElements($lines);
    }

    #
    # Setters
    #
    public function setBreaksEnabled(bool $breaksEnabled) {
        $this->breaksEnabled = $breaksEnabled;
        return $this;
    }

    protected bool $breaksEnabled = false;

    public function setMarkupEscaped(bool $markupEscaped)
    {
        $this->markupEscaped = $markupEscaped;

        return $this;
    }

    protected bool $markupEscaped = false;

    /**
     * 是否允许链接
     * @param bool $urlsLinked
     * @return $this
     */
    public function setUrlsLinked(bool $urlsLinked)
    {
        $this->urlsLinked = $urlsLinked;

        return $this;
    }

    protected bool $urlsLinked = true;

    /**
     * 安全模式，不允许脚本
     * @param bool $safeMode
     * @return $this
     */
    public function setSafeMode(bool $safeMode) {
        $this->safeMode = $safeMode;
        return $this;
    }

    protected bool $safeMode = false;

    /**
     * 严格模式
     * @param bool $strictMode
     * @return $this
     */
    public function setStrictMode(bool $strictMode) {
        $this->strictMode = $strictMode;
        return $this;
    }

    protected bool $strictMode = false;

    /**
     * 是否允许自定义样式， rss 模式不支持
     */
    protected bool $useCustomStyle = true;

    /**
     * 是否启用图片懒加载
     * @var array
     */
    protected array $lazyLoad = [];

    /**
     * 是否启用图片懒加载
     * @param array $lazyLoad [class, src, default]
     * @return $this
     */
    public function setLazyLoad(array $lazyLoad) {
        $this->lazyLoad = $lazyLoad;
        return $this;
    }

    /**
     * 是否允许自定义样式， rss 模式不支持
     * @param bool $useCustomStyle
     */
    public function setCustomStyle(bool $useCustomStyle) {
        $this->useCustomStyle = $useCustomStyle;
        return $this;
    }


    protected array $safeLinksWhitelist = array(
        'http://',
        'https://',
        'ftp://',
        'ftps://',
        'mailto:',
        'tel:',
        'data:image/png;base64,',
        'data:image/gif;base64,',
        'data:image/jpeg;base64,',
        'irc:',
        'ircs:',
        'git:',
        'ssh:',
        'news:',
        'steam:',
    );

    #
    # Lines
    #

    protected array $blockTypes = array(
        '#' => array('Header'),
        '*' => array('Rule', 'List'),
        '+' => array('List'),
        '-' => array('SetextHeader', 'Table', 'Rule', 'List'),
        '0' => array('List'),
        '1' => array('List'),
        '2' => array('List'),
        '3' => array('List'),
        '4' => array('List'),
        '5' => array('List'),
        '6' => array('List'),
        '7' => array('List'),
        '8' => array('List'),
        '9' => array('List'),
        ':' => array('Table'),
        '<' => array('Comment', 'Markup'),
        '=' => array('SetextHeader'),
        '>' => array('Quote'),
        '[' => array('Reference'),
        '_' => array('Rule'),
        '`' => array('FencedCode'),
        '|' => array('Table'),
        '~' => array('FencedCode'),
    );

    # ~

    protected array $unmarkedBlockTypes = array(
        'Code',
    );

    #
    # Blocks
    #

    protected function lines(array $lines) {
        return $this->elements($this->linesElements($lines));
    }

    protected function linesElements(array $lines) {
        $elements = [];
        $currentBlock = null;

        foreach ($lines as $line)
        {
            if (chop($line) === '')
            {
                if (isset($currentBlock))
                {
                    $currentBlock['interrupted'] = (isset($currentBlock['interrupted'])
                        ? $currentBlock['interrupted'] + 1 : 1
                    );
                }

                continue;
            }

            while (($beforeTab = strstr($line, "\t", true)) !== false)
            {
                $shortage = 4 - mb_strlen($beforeTab, 'utf-8') % 4;

                $line = $beforeTab
                    . str_repeat(' ', $shortage)
                    . substr($line, strlen($beforeTab) + 1)
                ;
            }

            $indent = strspn($line, ' ');

            $text = $indent > 0 ? substr($line, $indent) : $line;

            # ~

            $Line = array('body' => $line, 'indent' => $indent, 'text' => $text);

            # ~

            if (isset($currentBlock['continuable'])) {
                $methodName = 'block' . $currentBlock['type'] . 'Continue';
                $Block = $this->$methodName($Line, $currentBlock);

                if (isset($Block)) {
                    $currentBlock = $Block;

                    continue;
                } else {
                    if ($this->isBlockCompletable($currentBlock['type'])) {
                        $methodName = 'block' . $currentBlock['type'] . 'Complete';
                        $currentBlock = $this->$methodName($currentBlock);
                    }
                }
            }

            # ~

            $marker = $text[0];

            # ~

            $blockTypes = $this->unmarkedBlockTypes;

            if (isset($this->blockTypes[$marker])) {
                foreach ($this->blockTypes[$marker] as $blockType) {
                    $blockTypes[] = $blockType;
                }
            }

            #
            # ~

            foreach ($blockTypes as $blockType) {
                $Block = $this->{"block$blockType"}($Line, $currentBlock);

                if (isset($Block)) {
                    $Block['type'] = $blockType;

                    if ( ! isset($Block['identified'])) {
                        if (isset($currentBlock)) {
                            $elements[] = $this->extractElement($currentBlock);
                        }

                        $Block['identified'] = true;
                    }

                    if ($this->isBlockContinuable($blockType)) {
                        $Block['continuable'] = true;
                    }

                    $currentBlock = $Block;

                    continue 2;
                }
            }

            # ~

            if (isset($currentBlock) and $currentBlock['type'] === 'Paragraph') {
                $Block = $this->paragraphContinue($Line, $currentBlock);
            }

            if (isset($Block)) {
                $currentBlock = $Block;
            } else {
                if (isset($currentBlock))
                {
                    $elements[] = $this->extractElement($currentBlock);
                }

                $currentBlock = $this->paragraph($Line);

                $currentBlock['identified'] = true;
            }
        }

        # ~

        if (isset($currentBlock['continuable']) && $this->isBlockCompletable($currentBlock['type'])) {
            $methodName = 'block' . $currentBlock['type'] . 'Complete';
            $currentBlock = $this->$methodName($currentBlock);
        }

        # ~

        if (isset($currentBlock)) {
            $elements[] = $this->extractElement($currentBlock);
        }

        # ~

        return $elements;
    }

    protected function extractElement(array $component) {
        if ( ! isset($component['element'])) {
            if (isset($component['markup']))
            {
                $component['element'] = array('rawHtml' => $component['markup']);
            } elseif (isset($component['hidden'])) {
                $component['element'] = array();
            }
        }

        return $component['element'];
    }

    #[Pure]
    protected function isBlockContinuable($type) {
        return method_exists($this, 'block' . $type . 'Continue');
    }

    #[Pure]
    protected function isBlockCompletable($type) {
        return method_exists($this, 'block' . $type . 'Complete');
    }

    #
    # Code

    protected function blockCode(array $line, $block = null): array|null {
        if (!empty($block) && $block['type'] === 'Paragraph' && ! isset($block['interrupted'])) {
            return null;
        }

        if ($line['indent'] < 4) {
            return null;
        }
        $text = substr($line['body'], 4);
        return array(
            'element' => array(
                'name' => 'pre',
                'element' => array(
                    'name' => 'code',
                    'text' => $text,
                ),
            ),
        );
    }

    protected function blockCodeContinue(array $line, $block): array|null
    {
        if ($line['indent'] < 4) {
            return null;
        }
        if (isset($block['interrupted'])) {
            $block['element']['element']['text'] .= str_repeat("\n", $block['interrupted']);

            unset($block['interrupted']);
        }

        $block['element']['element']['text'] .= "\n";

        $text = substr($line['body'], 4);

        $block['element']['element']['text'] .= $text;

        return $block;
    }

    protected function blockCodeComplete($block)
    {
        return $block;
    }

    #
    # Comment

    protected function blockComment($line): array|null
    {
        if ($this->markupEscaped || $this->safeMode)
        {
            return null;
        }

        if (!str_starts_with($line['text'], '<!--')) {
            return null;
        }
        $block = array(
            'element' => array(
                'rawHtml' => $line['body'],
                'autobreak' => true,
            ),
        );

        if (str_contains($line['text'], '-->')) {
            $block['closed'] = true;
        }
        return $block;
    }

    protected function blockCommentContinue(array $line, array $block): array|null
    {
        if (isset($block['closed']))
        {
            return null;
        }

        $block['element']['rawHtml'] .= "\n" . $line['body'];

        if (str_contains($line['text'], '-->'))
        {
            $block['closed'] = true;
        }

        return $block;
    }

    /*
    # Fenced Code
    */
    protected function blockFencedCode($line): array|null {
        $marker = $line['text'][0];

        $openerLength = strspn($line['text'], $marker);

        if ($openerLength < 3) {
            return null;
        }

        $infoString = trim(substr($line['text'], $openerLength), "\t ");
        if (str_contains($infoString, '`')) {
            return null;
        }
        $element = array(
            'name' => 'code',
            'text' => '',
        );

        $info = $this->parseCodeItQuote($infoString);
        if (!empty($info['language'])) {
            $element['attributes'] = array('class' => sprintf('language-%s', $info['language']));
        }
        return array(
            'char' => $marker,
            'openerLength' => $openerLength,
            'element' => array(
                'name' => 'pre',
                'element' => $element,
                'it-quote' => $info,
            ),
        );
    }

    /**
     * 增加代码引用
     * @param string $infoString
     * @return array
     */
    protected function parseCodeItQuote(string $infoString): array {
        if (empty($infoString)) {
            return [];
        }
        $data = [
            'language' => substr($infoString, 0, strcspn($infoString, " \t\n\f\r{("))
        ];
        $i = strpos($infoString, '(');
        $j = strpos($infoString, '{');
        if ($i !== false && $j !== false) {
            $j ++;
            $data['lines'] = $this->parseQuoteLine($this->subBlock($infoString, $j, '}'));
            $j = strpos($infoString, '{', $j);
            if ($j !== false) {
                $data['highlight'] = array_map([$this, 'parseQuoteLine'],
                    explode(',',
                        $this->subBlock($infoString, $j + 1, '}')
                    ));
            }
            $data['url'] = $this->subBlock($infoString, $i + 1, ')');
        } elseif ($i !== false && $j === false) {
            $data['url'] = $this->subBlock($infoString, $i + 1, ')');
        } else if ($j !== false) {
            $data['highlight'] = array_map([$this, 'parseQuoteLine'],
                explode(',',
                    $this->subBlock($infoString, $j + 1, '}')));
        }
        return $data;
    }

    protected function subBlock(string $str, int $begin, string $endTag): string {
        $j = strpos($str, $endTag, $begin);
        if ($j === false) {
            return '';
        }
        return substr($str, $begin, $j - $begin);
    }

    protected function parseQuoteLine(string $block): array {
        $res = array_map('intval', explode('-', $block));
        if ($res[0] < 1) {
            $res[0] = 1;
        }
        if (count($res) === 1 || $res[1] < $res[0]) {
            $res[1] = $res[0];
        }
        return $res;
    }

    protected function blockFencedCodeContinue(array $line, array $block): array|null {
        if (isset($block['complete'])) {
            return null;
        }

        if (isset($block['interrupted'])) {
            $block['element']['element']['text'] .= str_repeat("\n", $block['interrupted']);

            unset($block['interrupted']);
        }

        if (($len = strspn($line['text'], $block['char'])) >= $block['openerLength']
            && chop(substr($line['text'], $len), ' ') === ''
        ) {
            $block['element']['element']['text'] = substr($block['element']['element']['text'], 1);
            $block['complete'] = true;
            return $block;
        }
        $block['element']['element']['text'] .= "\n" . $line['body'];
        return $block;
    }

    protected function blockFencedCodeComplete(array $block): array {
        $itQuote = $block['element']['it-quote'] ?? [];
        if (!isset($itQuote['lines'])) {
            $itQuote['lines'] = [1];
        }
        $itQuote['lines'][1] = $itQuote['lines'][0] +
            substr_count($block['element']['element']['text'], "\n");
        $block['element']['it-quote'] = $itQuote;
        return $block;
    }

    #
    # Header

    protected function blockHeader(array $line): array|null
    {
        $level = strspn($line['text'], '#');

        if ($level > 6)
        {
            return null;
        }

        $text = trim($line['text'], '#');

        if ($this->strictMode && isset($text[0]) && $text[0] !== ' ')
        {
            return null;
        }

        $text = trim($text, ' ');

        return array(
            'element' => array(
                'name' => 'h' . $level,
                'handler' => array(
                    'function' => 'lineElements',
                    'argument' => $text,
                    'destination' => 'elements',
                )
            ),
        );
    }

    #
    # List

    protected function blockList(array $line, array|null $currentBlock = null): array|null
    {
        list($name, $pattern) = $line['text'][0] <= '-' ? array('ul', '[*+-]') : array('ol', '[0-9]{1,9}+[.\)]');

        if (preg_match('/^('.$pattern.'([ ]++|$))(.*+)/', $line['text'], $matches))
        {
            $contentIndent = strlen($matches[2]);

            if ($contentIndent >= 5)
            {
                $contentIndent -= 1;
                $matches[1] = substr($matches[1], 0, -$contentIndent);
                $matches[3] = str_repeat(' ', $contentIndent) . $matches[3];
            }
            elseif ($contentIndent === 0)
            {
                $matches[1] .= ' ';
            }

            $markerWithoutWhitespace = strstr($matches[1], ' ', true);

            $block = array(
                'indent' => $line['indent'],
                'pattern' => $pattern,
                'data' => array(
                    'type' => $name,
                    'marker' => $matches[1],
                    'markerType' => ($name === 'ul' ? $markerWithoutWhitespace : substr($markerWithoutWhitespace, -1)),
                ),
                'element' => array(
                    'name' => $name,
                    'elements' => array(),
                ),
            );
            $block['data']['markerTypeRegex'] = preg_quote($block['data']['markerType'], '/');

            if ($name === 'ol')
            {
                $listStart = ltrim(strstr($matches[1], $block['data']['markerType'], true), '0') ?: '0';

                if ($listStart !== '1')
                {
                    if (
                        isset($currentBlock)
                        && $currentBlock['type'] === 'Paragraph'
                        && ! isset($currentBlock['interrupted'])
                    ) {
                        return null;
                    }

                    $block['element']['attributes'] = array('start' => $listStart);
                }
            }

            $block['li'] = array(
                'name' => 'li',
                'handler' => array(
                    'function' => 'li',
                    'argument' => !empty($matches[3]) ? array($matches[3]) : array(),
                    'destination' => 'elements'
                )
            );

            $block['element']['elements'] []= & $block['li'];

            return $block;
        }
        return null;
    }

    protected function blockListContinue(array $line, array $block): array|null
    {
        if (isset($block['interrupted']) && empty($block['li']['handler']['argument']))
        {
            return null;
        }

        $requiredIndent = ($block['indent'] + strlen($block['data']['marker']));

        if ($line['indent'] < $requiredIndent
            && (
                (
                    $block['data']['type'] === 'ol'
                    && preg_match('/^[0-9]++'.$block['data']['markerTypeRegex'].'(?:[ ]++(.*)|$)/', $line['text'], $matches)
                ) || (
                    $block['data']['type'] === 'ul'
                    && preg_match('/^'.$block['data']['markerTypeRegex'].'(?:[ ]++(.*)|$)/', $line['text'], $matches)
                )
            )
        ) {
            if (isset($block['interrupted']))
            {
                $block['li']['handler']['argument'] []= '';

                $block['loose'] = true;

                unset($block['interrupted']);
            }

            unset($block['li']);

            $text = $matches[1] ?? '';

            $block['indent'] = $line['indent'];

            $block['li'] = array(
                'name' => 'li',
                'handler' => array(
                    'function' => 'li',
                    'argument' => array($text),
                    'destination' => 'elements'
                )
            );

            $block['element']['elements'] []= & $block['li'];

            return $block;
        }
        elseif ($line['indent'] < $requiredIndent && $this->blockList($line))
        {
            return null;
        }

        if ($line['text'][0] === '[' && $this->blockReference($line))
        {
            return $block;
        }

        if ($line['indent'] >= $requiredIndent)
        {
            if (isset($block['interrupted']))
            {
                $block['li']['handler']['argument'] []= '';

                $block['loose'] = true;

                unset($block['interrupted']);
            }

            $text = substr($line['body'], $requiredIndent);

            $block['li']['handler']['argument'] []= $text;

            return $block;
        }

        if ( ! isset($block['interrupted']))
        {
            $text = preg_replace('/^[ ]{0,'.$requiredIndent.'}+/', '', $line['body']);

            $block['li']['handler']['argument'] []= $text;

            return $block;
        }
        return null;
    }

    protected function blockListComplete(array $block): array
    {
        if (isset($block['loose']))
        {
            foreach ($block['element']['elements'] as &$li)
            {
                if (end($li['handler']['argument']) !== '')
                {
                    $li['handler']['argument'] []= '';
                }
            }
        }

        return $block;
    }

    #
    # Quote

    protected function blockQuote(array $line): array|null
    {
        if (preg_match('/^>[ ]?+(.*+)/', $line['text'], $matches))
        {
            return array(
                'element' => array(
                    'name' => 'blockquote',
                    'handler' => array(
                        'function' => 'linesElements',
                        'argument' => (array) $matches[1],
                        'destination' => 'elements',
                    )
                ),
            );
        }
        return null;
    }

    protected function blockQuoteContinue(array $line, array $block): array|null
    {
        if (isset($block['interrupted']))
        {
            return null;
        }

        if ($line['text'][0] === '>' && preg_match('/^>[ ]?+(.*+)/', $line['text'], $matches))
        {
            $block['element']['handler']['argument'] []= $matches[1];

            return $block;
        }
        $block['element']['handler']['argument'] []= $line['text'];
        return $block;
    }

    #
    # Rule

    protected function blockRule(array $line): array|null
    {
        $marker = $line['text'][0];

        if (substr_count($line['text'], $marker) >= 3 && chop($line['text'], " $marker") === '')
        {
            return array(
                'element' => array(
                    'name' => 'hr',
                ),
            );
        }
        return null;
    }

    #
    # Setext

    protected function blockSetextHeader(array $line, array|null $block = null): array|null
    {
        if ( ! isset($block) || $block['type'] !== 'Paragraph' || isset($block['interrupted']))
        {
            return null;
        }

        if ($line['indent'] < 4 && chop(chop($line['text'], ' '), $line['text'][0]) === '')
        {
            $block['element']['name'] = $line['text'][0] === '=' ? 'h1' : 'h2';

            return $block;
        }
        return null;
    }

    #
    # Markup

    protected function blockMarkup(array $line): array|null
    {
        if ($this->markupEscaped || $this->safeMode)
        {
            return null;
        }

        if (preg_match('/^<[\/]?+(\w*)(?:[ ]*+'.$this->regexHtmlAttribute.')*+[ ]*+(\/)?>/', $line['text'], $matches))
        {
            $element = strtolower($matches[1]);

            if (in_array($element, $this->textLevelElements))
            {
                return null;
            }

            return array(
                'name' => $matches[1],
                'element' => array(
                    'rawHtml' => $line['text'],
                    'autobreak' => true,
                ),
            );
        }
        return null;
    }

    protected function blockMarkupContinue(array $line, array $block): array|null
    {
        if (isset($block['closed']) || isset($block['interrupted']))
        {
            return null;
        }

        $block['element']['rawHtml'] .= "\n" . $line['body'];

        return $block;
    }

    #
    # Reference

    protected function blockReference(array $line): array|null
    {
        if (str_contains($line['text'], ']')
            && preg_match('/^\[(.+?)\]:[ ]*+<?(\S+?)>?(?:[ ]+["\'(](.+)["\')])?[ ]*+$/', $line['text'], $matches)
        ) {
            $id = strtolower($matches[1]);

            $this->definitionData['Reference'][$id] = array(
                'url' => $matches[2],
                'title' => $matches[3] ?? null,
            );

            return array(
                'element' => array(),
            );
        }
        return null;
    }

    #
    # Table

    protected function blockTable(array $line, array|null $block = null): array|null
    {
        if ( ! isset($block) || $block['type'] !== 'Paragraph' || isset($block['interrupted']))
        {
            return null;
        }

        if (
            !str_contains($block['element']['handler']['argument'], '|')
            && !str_contains($line['text'], '|')
            && !str_contains($line['text'], ':')
            || str_contains($block['element']['handler']['argument'], "\n")
        ) {
            return null;
        }

        if (chop($line['text'], ' -:|') !== '')
        {
            return null;
        }

        $alignments = array();

        $divider = $line['text'];

        $divider = trim($divider);
        $divider = trim($divider, '|');

        $dividerCells = explode('|', $divider);

        foreach ($dividerCells as $dividerCell)
        {
            $dividerCell = trim($dividerCell);

            if ($dividerCell === '')
            {
                return null;
            }

            $alignment = null;

            if ($dividerCell[0] === ':')
            {
                $alignment = 'left';
            }

            if (str_ends_with($dividerCell, ':'))
            {
                $alignment = $alignment === 'left' ? 'center' : 'right';
            }

            $alignments []= $alignment;
        }

        # ~

        $headerElements = array();

        $header = $block['element']['handler']['argument'];

        $header = trim($header);
        $header = trim($header, '|');

        $headerCells = explode('|', $header);

        if (count($headerCells) !== count($alignments))
        {
            return null;
        }

        foreach ($headerCells as $index => $headerCell)
        {
            $headerCell = trim($headerCell);

            $HeaderElement = array(
                'name' => 'th',
                'handler' => array(
                    'function' => 'lineElements',
                    'argument' => $headerCell,
                    'destination' => 'elements',
                )
            );

            if (isset($alignments[$index]))
            {
                $alignment = $alignments[$index];

                $HeaderElement['attributes'] = array(
                    'style' => "text-align: $alignment;",
                );
            }

            $headerElements []= $HeaderElement;
        }

        # ~

        $block = array(
            'alignments' => $alignments,
            'identified' => true,
            'element' => array(
                'name' => 'table',
                'elements' => array(),
            ),
        );

        $block['element']['elements'] []= array(
            'name' => 'thead',
        );

        $block['element']['elements'] []= array(
            'name' => 'tbody',
            'elements' => array(),
        );

        $block['element']['elements'][0]['elements'] []= array(
            'name' => 'tr',
            'elements' => $headerElements,
        );

        return $block;
    }

    protected function blockTableContinue(array $line, array $block): array|null
    {
        if (isset($block['interrupted']))
        {
            return null;
        }

        if (count($block['alignments']) === 1 || $line['text'][0] === '|' || strpos($line['text'], '|'))
        {
            $elements = array();

            $row = $line['text'];

            $row = trim($row);
            $row = trim($row, '|');

            preg_match_all('/(?:(\\\\[|])|[^|`]|`[^`]++`|`)++/', $row, $matches);

            $cells = array_slice($matches[0], 0, count($block['alignments']));

            foreach ($cells as $index => $cell)
            {
                $cell = trim($cell);

                $element = array(
                    'name' => 'td',
                    'handler' => array(
                        'function' => 'lineElements',
                        'argument' => $cell,
                        'destination' => 'elements',
                    )
                );

                if (isset($block['alignments'][$index]))
                {
                    $element['attributes'] = array(
                        'style' => 'text-align: ' . $block['alignments'][$index] . ';',
                    );
                }

                $elements []= $element;
            }

            $element = array(
                'name' => 'tr',
                'elements' => $elements,
            );

            $block['element']['elements'][1]['elements'] []= $element;

            return $block;
        }
        return null;
    }

    #
    # ~
    #

    protected function paragraph(array $line): array
    {
        return array(
            'type' => 'Paragraph',
            'element' => array(
                'name' => 'p',
                'handler' => array(
                    'function' => 'lineElements',
                    'argument' => $line['text'],
                    'destination' => 'elements',
                ),
            ),
        );
    }

    protected function paragraphContinue(array $line, array $block): array|null
    {
        if (isset($block['interrupted']))
        {
            return null;
        }

        $block['element']['handler']['argument'] .= "\n".$line['text'];

        return $block;
    }

    #
    # Inline Elements
    #

    protected array $inlineTypes = array(
        '!' => array('Image'),
        '&' => array('SpecialCharacter'),
        '*' => array('Emphasis'),
        ':' => array('Url'),
        '<' => array('UrlTag', 'EmailTag', 'Markup'),
        '[' => array('Link'),
        '_' => array('Emphasis'),
        '`' => array('Code'),
        '~' => array('Strikethrough'),
        '\\' => array('EscapeSequence'),
    );

    # ~

    protected string $inlineMarkerList = '!*_&[:<`~\\';

    #
    # ~
    #

    public function line(string $text, array $nonNestables = array()): string {
        return $this->elements($this->lineElements($text, $nonNestables));
    }

    protected function lineElements(string $text, array $nonNestables = array()) {
        # standardize line breaks
        $text = str_replace(array("\r\n", "\r"), "\n", $text);

        $elements = array();

        $nonNestables = (empty($nonNestables)
            ? array()
            : array_combine($nonNestables, $nonNestables)
        );

        # $excerpt is based on the first occurrence of a marker

        while ($excerpt = strpbrk($text, $this->inlineMarkerList))
        {
            $marker = $excerpt[0];

            $markerPosition = strlen($text) - strlen($excerpt);

            $excerpt = array('text' => $excerpt, 'context' => $text);

            foreach ($this->inlineTypes[$marker] as $inlineType)
            {
                # check to see if the current inline type is nestable in the current context

                if (isset($nonNestables[$inlineType]))
                {
                    continue;
                }

                $inline = $this->{"inline$inlineType"}($excerpt);

                if ( ! isset($inline))
                {
                    continue;
                }

                # makes sure that the inline belongs to "our" marker

                if (isset($inline['position']) && $inline['position'] > $markerPosition)
                {
                    continue;
                }

                # sets a default inline position

                if ( ! isset($inline['position']))
                {
                    $inline['position'] = $markerPosition;
                }

                # cause the new element to 'inherit' our non nestables


                $inline['element']['nonNestables'] = isset($inline['element']['nonNestables'])
                    ? array_merge($inline['element']['nonNestables'], $nonNestables)
                    : $nonNestables
                ;

                # the text that comes before the inline
                $unmarkedText = substr($text, 0, $inline['position']);

                # compile the unmarked text
                $inlineText = $this->inlineText($unmarkedText);
                $elements[] = $inlineText['element'];

                # compile the inline
                $elements[] = $this->extractElement($inline);

                # remove the examined text
                $text = substr($text, $inline['position'] + $inline['extent']);

                continue 2;
            }

            # the marker does not belong to an inline

            $unmarkedText = substr($text, 0, $markerPosition + 1);

            $inlineText = $this->inlineText($unmarkedText);
            $elements[] = $inlineText['element'];

            $text = substr($text, $markerPosition + 1);
        }

        $inlineText = $this->inlineText($text);
        $elements[] = $inlineText['element'];

        foreach ($elements as &$element)
        {
            if ( ! isset($element['autobreak']))
            {
                $element['autobreak'] = false;
            }
        }

        return $elements;
    }

    #
    # ~
    #

    #[ArrayShape(['extent' => "int", 'element' => "array"])]
    protected function inlineText(string $text): array
    {
        $inline = array(
            'extent' => strlen($text),
            'element' => array(),
        );

        $inline['element']['elements'] = self::pregReplaceElements(
            $this->breaksEnabled ? '/[ ]*+\n/' : '/(?:[ ]*+\\\\|[ ]{2,}+)\n/',
            array(
                array('name' => 'br'),
                array('text' => "\n"),
            ),
            $text
        );

        return $inline;
    }

    protected function inlineCode(array $excerpt): array|null {
        $marker = $excerpt['text'][0];

        if (preg_match('/^(['.$marker.']++)[ ]*+(.+?)[ ]*+(?<!['.$marker.'])\1(?!'.$marker.')/s', $excerpt['text'], $matches))
        {
            $text = $matches[2];
            $text = preg_replace('/[ ]*+\n/', ' ', $text);

            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'code',
                    'text' => $text,
                ),
            );
        }
        return null;
    }

    protected function inlineEmailTag(array $excerpt): array|null
    {
        $hostnameLabel = '[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?';

        $commonMarkEmail = '[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]++@'
            . $hostnameLabel . '(?:\.' . $hostnameLabel . ')*';

        if (str_contains($excerpt['text'], '>')
            && preg_match("/^<((mailto:)?$commonMarkEmail)>/i", $excerpt['text'], $matches)
        ){
            $url = $matches[1];

            if ( ! isset($matches[2]))
            {
                $url = "mailto:$url";
            }

            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'a',
                    'text' => $matches[1],
                    'attributes' => array(
                        'href' => $url,
                    ),
                ),
            );
        }
        return null;
    }

    protected function inlineEmphasis(array $excerpt): array|null
    {
        if ( ! isset($excerpt['text'][1]))
        {
            return null;
        }

        $marker = $excerpt['text'][0];

        if ($excerpt['text'][1] === $marker && preg_match($this->strongRegex[$marker], $excerpt['text'], $matches))
        {
            $emphasis = 'strong';
        }
        elseif (preg_match($this->emRegex[$marker], $excerpt['text'], $matches))
        {
            $emphasis = 'em';
        }
        else
        {
            return null;
        }

        return array(
            'extent' => strlen($matches[0]),
            'element' => array(
                'name' => $emphasis,
                'handler' => array(
                    'function' => 'lineElements',
                    'argument' => $matches[1],
                    'destination' => 'elements',
                )
            ),
        );
    }

    protected function inlineEscapeSequence(array $excerpt): array|null
    {
        if (isset($excerpt['text'][1]) && in_array($excerpt['text'][1], $this->specialCharacters))
        {
            return array(
                'element' => array('rawHtml' => $excerpt['text'][1]),
                'extent' => 2,
            );
        }
        return null;
    }

    protected function inlineImage(array $excerpt): array|null
    {
        if ( ! isset($excerpt['text'][1]) || $excerpt['text'][1] !== '[')
        {
            return null;
        }

        $excerpt['text']= substr($excerpt['text'], 1);

        $link = $this->inlineLink($excerpt);

        if ($link === null)
        {
            return null;
        }


        $attributes = array(
            'src' => $link['element']['attributes']['href'],
        );

        $inline = array(
            'extent' => $link['extent'] + 1,
            'element' => array(
                'name' => 'img',
                'autobreak' => true,
            ),
        );

        if (!empty($link['element']['handler']['argument'])) {
            $attributes['alt'] = $attributes['title'] = $link['element']['handler']['argument'];
        }

        $attributes += $link['element']['attributes'];

        if (!empty($this->lazyLoad) && !empty($attributes['src'])) {
            $attributes['class'] = (isset($attributes['class']) ? $attributes['class'].' ' : '').$this->lazyLoad['class'];
            $attributes[$this->lazyLoad['src']] = $attributes['src'];
            $attributes['src'] = $this->lazyLoad['default'];
        }

        unset($attributes['href']);
        $inline['element']['attributes'] = $attributes;
        return $inline;
    }

    protected function inlineLink(array $excerpt): array|null
    {
        $element = array(
            'name' => 'a',
            'handler' => array(
                'function' => 'lineElements',
                'argument' => null,
                'destination' => 'elements',
            ),
            'nonNestables' => array('Url', 'Link'),
            'attributes' => array(
                'href' => null,
                'title' => null,
            ),
        );

        $extent = 0;

        $remainder = $excerpt['text'];

        if (preg_match('/\[((?:[^][]++|(?R))*+)\]/', $remainder, $matches))
        {
            $element['handler']['argument'] = $matches[1];

            $extent += strlen($matches[0]);

            $remainder = substr($remainder, $extent);
        }
        else
        {
            return null;
        }

        if (preg_match('/^[(]\s*+((?:[^ ()]++|[(][^ )]+[)])++)(?:[ ]+("[^"]*+"|\'[^\']*+\'))?\s*+[)]/', $remainder, $matches))
        {
            $element['attributes']['href'] = $matches[1];

            if (isset($matches[2]))
            {
                $element['attributes']['title'] = substr($matches[2], 1, - 1);
            }

            $extent += strlen($matches[0]);
        }
        else
        {
            if (preg_match('/^\s*\[(.*?)\]/', $remainder, $matches))
            {
                $definition = strlen($matches[1]) ? $matches[1] : $element['handler']['argument'];
                $definition = strtolower($definition);

                $extent += strlen($matches[0]);
            }
            else
            {
                $definition = strtolower($element['handler']['argument']);
            }

            if ( ! isset($this->definitionData['Reference'][$definition]))
            {
                return null;
            }

            $Definition = $this->definitionData['Reference'][$definition];

            $element['attributes']['href'] = $Definition['url'];
            $element['attributes']['title'] = $Definition['title'];
        }

        return array(
            'extent' => $extent,
            'element' => $element,
        );
    }

    protected function inlineMarkup(array $excerpt): array|null
    {
        if ($this->markupEscaped || $this->safeMode || !str_contains($excerpt['text'], '>'))
        {
            return null;
        }

        if ($excerpt['text'][1] === '/' && preg_match('/^<\/\w[\w-]*+[ ]*+>/s', $excerpt['text'], $matches))
        {
            return array(
                'element' => array('rawHtml' => $matches[0]),
                'extent' => strlen($matches[0]),
            );
        }

        if ($excerpt['text'][1] === '!' && preg_match('/^<!---?[^>-](?:-?+[^-])*-->/s', $excerpt['text'], $matches))
        {
            return array(
                'element' => array('rawHtml' => $matches[0]),
                'extent' => strlen($matches[0]),
            );
        }

        if ($excerpt['text'][1] !== ' ' && preg_match('/^<\w[\w-]*+(?:[ ]*+'.$this->regexHtmlAttribute.')*+[ ]*+\/?>/s', $excerpt['text'], $matches))
        {
            return array(
                'element' => array('rawHtml' => $matches[0]),
                'extent' => strlen($matches[0]),
            );
        }
        return null;
    }

    protected function inlineSpecialCharacter(array $excerpt): array|null
    {
        if (substr($excerpt['text'], 1, 1) !== ' ' && str_contains($excerpt['text'], ';')
            && preg_match('/^&(#?+[0-9a-zA-Z]++);/', $excerpt['text'], $matches)
        ) {
            return array(
                'element' => array('rawHtml' => '&' . $matches[1] . ';'),
                'extent' => strlen($matches[0]),
            );
        }

        return null;
    }

    protected function inlineStrikethrough($excerpt): array|null
    {
        if ( ! isset($excerpt['text'][1]))
        {
            return null;
        }

        if ($excerpt['text'][1] === '~' && preg_match('/^~~(?=\S)(.+?)(?<=\S)~~/', $excerpt['text'], $matches))
        {
            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'del',
                    'handler' => array(
                        'function' => 'lineElements',
                        'argument' => $matches[1],
                        'destination' => 'elements',
                    )
                ),
            );
        }
        return null;
    }

    protected function inlineUrl(array $excerpt): array|null
    {
        if (!$this->urlsLinked || ! isset($excerpt['text'][2]) || $excerpt['text'][2] !== '/')
        {
            return null;
        }

        if (str_contains($excerpt['context'], 'http')
            && preg_match('/\bhttps?+:[\/]{2}[^\s<]+\b\/*+/ui', $excerpt['context'], $matches, PREG_OFFSET_CAPTURE)
        ) {
            $url = $matches[0][0];

            return array(
                'extent' => strlen($matches[0][0]),
                'position' => $matches[0][1],
                'element' => array(
                    'name' => 'a',
                    'text' => $url,
                    'attributes' => array(
                        'href' => $url,
                    ),
                ),
            );
        }
        return null;
    }

    protected function inlineUrlTag(array $excerpt): array|null
    {
        if (str_contains($excerpt['text'], '>') && preg_match('/^<(\w++:\/{2}[^ >]++)>/i', $excerpt['text'], $matches))
        {
            $url = $matches[1];

            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'a',
                    'text' => $url,
                    'attributes' => array(
                        'href' => $url,
                    ),
                ),
            );
        }
        return null;
    }

    # ~

    protected function unmarkedText(string $text): string
    {
        $inline = $this->inlineText($text);
        return $this->element($inline['element']);
    }

    #
    # Handlers
    #

    protected function handle(array $element)
    {
        if (isset($element['handler']))
        {
            if (!isset($element['nonNestables']))
            {
                $element['nonNestables'] = array();
            }

            if (is_string($element['handler']))
            {
                $function = $element['handler'];
                $argument = $element['text'];
                unset($element['text']);
                $destination = 'rawHtml';
            }
            else
            {
                $function = $element['handler']['function'];
                $argument = $element['handler']['argument'];
                $destination = $element['handler']['destination'];
            }

            $element[$destination] = $this->{$function}($argument, $element['nonNestables']);

            if ($destination === 'handler')
            {
                $element = $this->handle($element);
            }

            unset($element['handler']);
        }

        return $element;
    }

    protected function handleElementRecursive(array $element)
    {
        return $this->elementApplyRecursive(array($this, 'handle'), $element);
    }

    protected function handleElementsRecursive(array $elements): array
    {
        return $this->elementsApplyRecursive(array($this, 'handle'), $elements);
    }

    protected function elementApplyRecursive($closure, array $element)
    {
        $element = call_user_func($closure, $element);

        if (isset($element['elements']))
        {
            $element['elements'] = $this->elementsApplyRecursive($closure, $element['elements']);
        }
        elseif (isset($element['element']))
        {
            $element['element'] = $this->elementApplyRecursive($closure, $element['element']);
        }

        return $element;
    }

    protected function elementApplyRecursiveDepthFirst($closure, array $element)
    {
        if (isset($element['elements']))
        {
            $element['elements'] = $this->elementsApplyRecursiveDepthFirst($closure, $element['elements']);
        }
        elseif (isset($element['element']))
        {
            $element['element'] = $this->elementsApplyRecursiveDepthFirst($closure, $element['element']);
        }

        return call_user_func($closure, $element);
    }

    protected function elementsApplyRecursive($closure, array $elements): array
    {
        return array_map(function ($element) use ($closure) {
            return $this->elementApplyRecursive($closure, $element);
        }, $elements);
    }

    protected function elementsApplyRecursiveDepthFirst($closure, array $elements): array
    {
        return array_map(function ($element) use ($closure) {
            return $this->elementApplyRecursiveDepthFirst($closure, $element);
        }, $elements);
    }

    protected function element(array $element) {
        if ($this->safeMode) {
            $element = $this->sanitiseElement($element);
        }

        # identity map if element has no handler
        $element = $this->handle($element);
        $hasName = isset($element['name']);

        $markup = '';
        if ($hasName) {
            $markup .= '<' . $element['name'];
            if (isset($element['attributes'])) {
                foreach ($element['attributes'] as $name => $value) {
                    if ($value === null)
                    {
                        continue;
                    }

                    $markup .= " $name=\"".self::escape($value).'"';
                }
            }
        }

        $permitRawHtml = false;

        if (isset($element['text'])) {
            $text = $element['text'];
        }
        // very strongly consider an alternative if you're writing an
        // extension
        elseif (isset($element['rawHtml'])) {
            $text = $element['rawHtml'];

            $allowRawHtmlInSafeMode = isset($element['allowRawHtmlInSafeMode']) && $element['allowRawHtmlInSafeMode'];
            $permitRawHtml = !$this->safeMode || $allowRawHtmlInSafeMode;
        }

        $hasContent = isset($text) || isset($element['element']) || isset($element['elements']);

        if ($hasContent) {
            $markup .= $hasName ? '>' : '';

            if (isset($element['elements'])) {
                $markup .= $this->elements($element['elements']);
            }
            elseif (isset($element['element'])) {
                $markup .= $this->element($element['element']);
            } else {
                if (!$permitRawHtml) {
                    $markup .= self::escape($text, true);
                } else {
                    $markup .= $text;
                }
            }

            $markup .= $hasName ? '</' . $element['name'] . '>' : '';
        } elseif ($hasName) {
            $markup .= ' />';
        }
        if ($this->useCustomStyle && !empty($element['name']) && $element['name'] === 'pre' && !empty($element['it-quote'])) {
            return $this->renderCode($element['it-quote'], $markup);
        }
        return $markup;
    }

    /**
     * 自定义代码块样式
     * @param array $quote
     * @param string $content
     * @return string
     */
    protected function renderCode(array $quote, string $content): string {
        $urlBtn = !empty($quote['url']) ? sprintf('<a href="%s" target="_blank" rel="noopener" title="open url"><i class="icon-cloud"></i></a>',
            $quote['url']
        ) : '';
        $highlight = '';
        $line = '';
        $selected = $quote['highlight'] ?? [];
        for ($i = max($quote['lines'][0], 1); $i <= $quote['lines'][1]; $i ++) {
            $highlight .= $this->isInRange($i, $selected) ?
                '<span class="highlighted">&nbsp;</span>' : '<span>&nbsp;</span>';
            $line .= sprintf('<span>%d</span>', $i);
        }
        $language = $quote['language'] ?? '';
        return <<<HTML
 <div class="code-container">
    <div class="code-header">
        <a data-action="copy" title="copy">
            <i class="icon-copy"></i>
        </a>
        <a data-action="full" title="full screen">
            <i class="icon-full-screen"></i>
        </a>
        {$urlBtn}
        <span>{$language}</span>
    </div>
    <div class="highlight-bar">
        {$highlight}
    </div>
    {$content}
    <div class="line-number-bar">
        {$line}
    </div>
</div>
HTML;
    }

    protected function isInRange(int $i, array $items): bool {
        foreach ($items as $item) {
            if ($i >= $item[0] && $i <= $item[1]) {
                return true;
            }
        }
        return false;
    }

    protected function elements(array $elements): string {
        $markup = '';

        $autoBreak = true;
        foreach ($elements as $element) {
            if (empty($element)) {
                continue;
            }

            $autoBreakNext = $element['autobreak'] ?? isset($element['name']);
            // (autobreak === false) covers both sides of an element
            $autoBreak = !$autoBreak ? $autoBreak : $autoBreakNext;

            $markup .= ($autoBreak ? "\n" : '') . $this->element($element);
            $autoBreak = $autoBreakNext;
        }

        $markup .= $autoBreak ? "\n" : '';

        return $markup;
    }

    # ~

    protected function li(array $lines): array
    {
        $Elements = $this->linesElements($lines);

        if (isset($Elements[0]['name']) && !in_array('', $lines) && $Elements[0]['name'] === 'p'
        ) {
            unset($Elements[0]['name']);
        }

        return $Elements;
    }

    #
    # AST Convenience
    #

    /**
     * Replace occurrences $regexp with $Elements in $text. Return an array of
     * elements representing the replacement.
     * @param $regexp
     * @param $Elements
     * @param $text
     * @return array
     */
    protected static function pregReplaceElements($regexp, $Elements, $text): array
    {
        $newElements = array();

        while (preg_match($regexp, $text, $matches, PREG_OFFSET_CAPTURE))
        {
            $offset = intval($matches[0][1]);
            $before = substr($text, 0, $offset);
            $after = substr($text, $offset + strlen($matches[0][0]));

            $newElements[] = array('text' => $before);

            foreach ($Elements as $Element)
            {
                $newElements[] = $Element;
            }

            $text = $after;
        }

        $newElements[] = array('text' => $text);

        return $newElements;
    }

    protected function sanitiseElement(array $element): array
    {
        static $goodAttribute = '/^[a-zA-Z0-9][a-zA-Z0-9-_]*+$/';
        static $safeUrlNameToAtt  = array(
            'a'   => 'href',
            'img' => 'src',
        );

        if ( ! isset($element['name']))
        {
            unset($element['attributes']);
            return $element;
        }

        if (isset($safeUrlNameToAtt[$element['name']]))
        {
            $element = $this->filterUnsafeUrlInAttribute($element, $safeUrlNameToAtt[$element['name']]);
        }

        if ( ! empty($element['attributes']))
        {
            foreach ($element['attributes'] as $att => $val)
            {
                # filter out badly parsed attribute
                if ( ! preg_match($goodAttribute, $att))
                {
                    unset($element['attributes'][$att]);
                }
                # dump onevent attribute
                elseif (self::striAtStart($att, 'on'))
                {
                    unset($element['attributes'][$att]);
                }
            }
        }

        return $element;
    }

    protected function filterUnsafeUrlInAttribute(array $element, string $attribute): array
    {
        foreach ($this->safeLinksWhitelist as $scheme)
        {
            if (self::striAtStart($element['attributes'][$attribute], $scheme))
            {
                return $element;
            }
        }

        $element['attributes'][$attribute] = str_replace(':', '%3A', $element['attributes'][$attribute]);

        return $element;
    }

    #
    # Static Methods
    #

    #[Pure]
    protected static function escape(string $text, $allowQuotes = false): string
    {
        return htmlspecialchars($text, $allowQuotes ? ENT_NOQUOTES : ENT_QUOTES, 'UTF-8');
    }

    #[Pure]
    protected static function striAtStart(string $string, string $needle): bool
    {
        $len = strlen($needle);

        if ($len > strlen($string))
        {
            return false;
        }
        return strtolower(substr($string, 0, $len)) === strtolower($needle);
    }

    static function instance(string $name = 'default')
    {
        if (isset(self::$instances[$name]))
        {
            return self::$instances[$name];
        }

        $instance = new static();

        self::$instances[$name] = $instance;

        return $instance;
    }

    private static array $instances = array();

    #
    # Fields
    #

    protected array $definitionData;

    #
    # Read-Only

    protected array $specialCharacters = array(
        '\\', '`', '*', '_', '{', '}', '[', ']', '(', ')', '>', '#', '+', '-', '.', '!', '|', '~'
    );

    protected array $strongRegex = array(
        '*' => '/^[*]{2}((?:\\\\\*|[^*]|[*][^*]*+[*])+?)[*]{2}(?![*])/s',
        '_' => '/^__((?:\\\\_|[^_]|_[^_]*+_)+?)__(?!_)/us',
    );

    protected array $emRegex = array(
        '*' => '/^[*]((?:\\\\\*|[^*]|[*][*][^*]+?[*][*])+?)[*](?![*])/s',
        '_' => '/^_((?:\\\\_|[^_]|__[^_]*__)+?)_(?!_)\b/us',
    );

    protected string $regexHtmlAttribute = '[a-zA-Z_:][\w:.-]*+(?:\s*+=\s*+(?:[^"\'=<>`\s]+|"[^"]*+"|\'[^\']*+\'))?+';

    protected array $voidElements = array(
        'area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source',
    );

    protected array $textLevelElements = array(
        'a', 'br', 'bdo', 'abbr', 'blink', 'nextid', 'acronym', 'basefont',
        'b', 'em', 'big', 'cite', 'small', 'spacer', 'listing',
        'i', 'rp', 'del', 'code',          'strike', 'marquee',
        'q', 'rt', 'ins', 'font',          'strong',
        's', 'tt', 'kbd', 'mark',
        'u', 'xm', 'sub', 'nobr',
        'sup', 'ruby',
        'var', 'span',
        'wbr', 'time',
    );
}
