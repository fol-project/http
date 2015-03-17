<?php
namespace Fol\Http;

/**
 * Some http utilities
 */
class Utils
{
    private static $formats;
    private static $languages;
    private static $statuses;

    /**
     * Returns all available formats.
     *
     * @return array
     */
    public static function getFormats()
    {
        if (empty(self::$formats)) {
            self::$formats = require __DIR__.'/data/formats.php';
        }

        return self::$formats;
    }

    /**
     * Returns all available languages.
     *
     * @return array
     */
    public static function getLanguages()
    {
        if (empty(self::$languages)) {
            self::$languages = require __DIR__.'/data/languages.php';
        }

        return self::$languages;
    }

    /**
     * Returns all available statuses.
     *
     * @return array
     */
    public static function getStatuses()
    {
        if (empty(self::$statuses)) {
            self::$statuses = require __DIR__.'/data/statuses.php';
        }

        return self::$statuses;
    }

    /**
     * Gets the format related with a mimetype.
     * Utils::getFormat('text/css') => css.
     *
     * @param string $mimetype The mimetype to search
     *
     * @return false|string
     */
    public static function mimetypeToFormat($mimetype)
    {
        foreach (self::getFormats() as $format => $mimetypes) {
            if (in_array($mimetype, $mimetypes)) {
                return $format;
            }
        }

        return false;
    }

    /**
     * Gets the mimetype related with a format.
     * Utils::getMimetype('css') => text/css.
     *
     * @param string $format The format to search
     *
     * @return false|string
     */
    public static function formatToMimetype($format)
    {
        $formats = self::getFormats();

        return isset($formats[$format][0]) ? $formats[$format][0] : false;
    }

    /**
     * Gets the language
     * Utils::getLanguage('gl-es') => gl.
     *
     * @param string $language The raw language code
     *
     * @return false|string
     */
    public static function getLanguage($language)
    {
        $language = strtolower(substr($language, 0, 2));
        $languages = self::getLanguages();

        return isset($languages[$language]) ? $language : false;
    }

    /**
     * Gets the language name
     * Utils::getLanguageName('gl-es') => Galician.
     *
     * @param string $language The raw language code
     *
     * @return false|string
     */
    public static function getLanguageName($language)
    {
        $language = strtolower(substr($language, 0, 2));
        $languages = self::getLanguages();

        return isset($languages[$language]) ? $languages[$language] : false;
    }

    /**
     * Gets the default reason phrase related with a status code.
     * Utils::getReasonPhrase(200) => OK.
     *
     * @param integer $code The Http code
     *
     * @return false|string
     */
    public static function getReasonPhrase($code)
    {
        $statuses = self::getStatuses();

        return isset($statuses[$code]) ? $statuses[$code] : false;
    }

    /**
     * Parse and return http values.
     *
     * Utils::parseHeader('text/html,application/xhtml+xml,application/xml;q=0.9,* /*;q=0.8')
     * Array (
     *     [text/html] => Array()
     *     [application/xhtml+xml] => Array()
     *     [application/xml] => Array([q] => 0.9)
     *     [* /*] => Array([q] => 0.8)
     * )
     *
     * @param string $header The header value to parse
     *
     * @return array
     */
    public static function parseHeader($header)
    {
        if (!$header) {
            return [];
        }

        $results = [];

        foreach (explode(',', $header) as $values) {
            $items = [];

            foreach (explode(';', $values) as $value) {
                if (strpos($value, '=') === false) {
                    $items[trim($value)] = true;
                } else {
                    list($name, $value) = explode('=', $value, 2);
                    $items[trim($name)] = trim($value);
                }
            }

            $name = key($items);

            if (($items[$name] === true) && (count($items) > 1)) {
                array_shift($items);
                $results[$name] = $items;
            } else {
                $results[$name] = $items[$name];
            }
        }

        return $results;
    }

    /**
     * Convert a parsed http value to string (the opposite of Utils::parseHeader).
     *
     * @param array $header The parsed header
     *
     * @return string
     */
    public static function stringifyHeader(array $header)
    {
        if (!$header) {
            return '';
        }

        $results = array();

        foreach ($header as $name => $value) {
            if (!is_array($value)) {
                $results[] = ($value === true) ? $name : "$name=$value";
                continue;
            }

            $sub_values = array($name);

            foreach ($value as $value_name => $value_value) {
                $sub_values[] = ($value_value === true) ? $value_name : "$value_name=$value_value";
            }

            $results[] = implode(';', $sub_values);
        }

        return implode(',', $results);
    }
}
