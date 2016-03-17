<?php namespace Golonka\BBCode;

use \Golonka\BBCode\Traits\ArrayTrait;

class BBCodeParser
{

    use ArrayTrait;

    private $parsers = [];
    private $enabledParsers = [];
    private $BBTagsHandled = [];

    public function __construct()
    {
        $tags = config('bbcode.tags');
        $parsers = [];
        foreach ($tags as $name => $parser) {
            if (array_has($parser, 'without_attribute')) {
                $parsers[$name] = [
                    'pattern' => '/\[quote\](.*?)\[\/quote\]/s',
                    'replace' => $parser['without_attribute'],
                    'content' => '$1',
                ];
            }
            if (array_has($parser, 'with_attribute')) {
                $parsers[$name . '_a'] = [
                    'pattern' => '/\[quote\=(.*?)\](.*?)\[\/quote\]/s',
                    'replace' => $parser['with_attribute'],
                    'content' => '$2',
                ];
            }
        }
        $this->parsers = $this->enabledParsers = $parsers;
        $this->BBTagsHandled = array_keys($tags);
    }

    /**
     * Parses the BBCode string
     * @param  string $source String containing the BBCode
     * @return string Parsed string
     */
    public function parse($source, $caseInsensitive = false)
    {
        foreach ($this->enabledParsers as $name => $parser) {
            $pattern = ($caseInsensitive) ? $parser['pattern'] . 'i' : $parser['pattern'];

            $source = $this->searchAndReplace($pattern, $parser['replace'], $source);
        }
        $source = $this->cleanup($source);
        return $source;
    }

    /**
     * Cleans up mismatched tags after the parser is done with the string
     * @param string $string
     * @return string
     */
    protected function cleanup($string)
    {
        $search = [];
        $replace = '';
        foreach ($this->BBTagsHandled as $tag) {
            $search[] = '[' . $tag . ']';
            $search[] = '[/' . $tag . ']';
        }
        return str_ireplace($search, $replace, $string);
    }

    /**
     * Remove all BBCode
     * @param  string $source
     * @return string Parsed text
     */
    public function stripBBCodeTags($source)
    {
        foreach ($this->parsers as $name => $parser) {
            $source = $this->searchAndReplace($parser['pattern'] . 'i', $parser['content'], $source);
        }
        return $source;
    }

    /**
     * Remove all BBCode AND the text contained within
     * @param  string $source
     * @return string Parsed text
     */
    public function stripBBCodeTagsAndContent($source)
    {
        foreach ($this->parsers as $name => $parser) {
            $source = $this->searchAndReplace($parser['pattern'] . 'i', '', $source);
        }
        return $source;
    }

    /**
     * Searches after a specified pattern and replaces it with provided structure
     * @param  string $pattern Search pattern
     * @param  string $replace Replacement structure
     * @param  string $source Text to search in
     * @return string Parsed text
     */
    protected function searchAndReplace($pattern, $replace, $source)
    {
        while (preg_match($pattern, $source)) {
            $source = preg_replace($pattern, $replace, $source);
        }

        return $source;
    }

    /**
     * Helper function to parse case sensitive
     * @param  string $source String containing the BBCode
     * @return string Parsed text
     */
    public function parseCaseSensitive($source)
    {
        return $this->parse($source, false);
    }

    /**
     * Helper function to parse case insensitive
     * @param  string $source String containing the BBCode
     * @return string Parsed text
     */
    public function parseCaseInsensitive($source)
    {
        return $this->parse($source, true);
    }

    /**
     * Limits the parsers to only those you specify
     * @param  mixed $only parsers
     * @return object BBCodeParser object
     */
    public function only($only = null)
    {
        $only = (is_array($only)) ? $only : func_get_args();
        $this->enabledParsers = $this->arrayOnly($this->parsers, $only);
        return $this;
    }

    /**
     * Removes the parsers you want to exclude
     * @param  mixed $except parsers
     * @return object BBCodeParser object
     */
    public function except($except = null)
    {
        $except = (is_array($except)) ? $except : func_get_args();
        $this->enabledParsers = $this->arrayExcept($this->parsers, $except);
        return $this;
    }

    /**
     * List of chosen parsers
     * @return array array of parsers
     */
    public function getParsers()
    {
        return $this->enabledParsers;
    }

    /**
     * Sets the parser pattern and replace.
     * This can be used for new parsers or overwriting existing ones.
     * @param string $name Parser name
     * @param string $pattern Pattern
     * @param string $replace Replace pattern
     * @return void
     */
    public function setParser($name, $pattern, $replace)
    {
        $this->parsers[$name] = [
            'pattern' => $pattern,
            'replace' => $replace,
        ];

        $this->enabledParsers[$name] = [
            'pattern' => $pattern,
            'replace' => $replace,
        ];
    }
}
