<?php
/**
 * This file is part of the KDN OZG package.
 *
 * @author    Gert Hammes <info@gerthammes.de>
 * @copyright 2019 Gert Hammes
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Import;

use DateTime;

class DataParser
{
    /**
     * Replace some characters
     *
     * @param mixed $value
     * @return string
     */
    public function formatString($value): string
    {
        $trgEnc = 'UTF-8';
        $cleanedValue = (string) $value;//$this->cleanStringValue($value);
        if (function_exists('mb_detect_encoding')) {
            $enc = mb_detect_encoding($value, $trgEnc, true);
            if (empty($enc)) {
                $enc = mb_detect_encoding($value, 'ISO-8859-1', true);
            }
        }
        if (!empty($enc) && $enc !== 'UTF-8' && function_exists('iconv')) {
            $cleanedValue = iconv($enc, $trgEnc, $cleanedValue);
        }
        return $cleanedValue;//$this->cleanStringValue($cleanedValue);
    }

    public static function cleanStringValue(string $value): string
    {
        $cleanVal = $value;
        $chIn = array('ç', 'ä', 'ü', 'ö', 'Ä', 'Ü', 'Ö', 'ß', 'á', 'à',
            'â', 'é', 'è', 'ê', 'ë', 'í', 'ì', 'î', 'ó', 'ò', 'ô', 'ú', 'ù',
            'û', 'Á', 'À', 'Â', 'É', 'È', 'Ê', 'Í', 'Ì', 'Î', 'Ó', 'Ò', 'ô',
            'Ú', 'Ù', 'Û', 'ñ', '´', '`');
        $chOut = array('c', 'ae', 'ue', 'oe', 'Ae', 'Ue', 'Oe', 'ss', 'a',
            'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'o', 'o', 'o', 'u',
            'u', 'u', 'A', 'A', 'A', 'E', 'E', 'E', 'I', 'I', 'I', 'O', 'O',
            'O', 'U', 'U', 'U', 'n', '', '');
        foreach ($chIn as $offset => $srcChar) {
            $trgChars = $chOut[$offset];
            $cleanVal = str_replace(array($srcChar, utf8_decode($srcChar)), array($trgChars, $trgChars), $cleanVal);
        }
        return $cleanVal;
    }

    /**
     * Format string to boolean value
     *
     * @param mixed $value
     *
     * @return boolean
     */
    public function formatBoolean($value): bool
    {
        $formattedValue = false;
        $chkVal = strtolower((string) $value);
        if ($chkVal === 'ja' || $chkVal === 'yes' || $chkVal === '1' || $chkVal === 'true') {
            $formattedValue = true;
        }
        return $formattedValue;
    }

    /**
     * Format value in callback
     *
     * @param mixed $value
     * @param array $choices
     * @return mixed
     */
    public function formatChoice($value, array $choices)
    {
        $key = array_search((string) $value, $choices, false);
        return $key !== false ? $key : null;
    }

    /**
     * Format csv string to array value
     *
     * @param mixed $value
     *
     * @return array
     */
    public function formatCsv($value): array
    {
        $possibleDelimiters = ['|', ',', ';',];
        $delimiter = ';';
        foreach ($possibleDelimiters as $checkDelimiter) {
            if (strpos($value, $checkDelimiter) !== false) {
                $delimiter = $checkDelimiter;
            }
        }
        return $value !== '' ? explode($delimiter, $value) : [];
    }

    /**
     * Parses the given string value to extract a number. The function can
     * create valid float and integer values for different input formats,
     * e.g. if "," is used for float numbers instead of ".".
     *
     * Usage: Helper::parseNumber($value);
     *
     * @param mixed $string A string containing a number
     *
     * @return float A valid number
     */
    public function formatFloat($string): float
    {
        if ($string === '') {
            return 0;
        }
        $value = $string;
        $commaPos = strpos($value, ',');
        $commaSet = $commaPos !== false;
        $pointPos = strpos($value, '.');
        $pointSet = $pointPos !== false;
        if ($commaSet) {
            if ($pointSet) {
                //12,345.67
                if ($pointPos > $commaPos) {
                    $value = str_replace(',', '', $value);
                    //12.345,67
                } else {
                    $value = str_replace(['.', ','], ['', '.'], $value);
                }
                $value = (float)$value;
                //12345,67
            } else {
                $value = str_replace(',', '.', $value);
                $value = (float)$value;
            }
        } //invalid number e.g. 1a4c
        elseif (!$pointSet) {
            $value = $this->formatInt($value);
        } else {
            $value = (float)$value;
        }
        if (empty($value)) {
            $value = 0.0;
        } else {
            $value = (float)str_replace(',', '.', $value);
        }
        return $value;
    }

    /**
     * Parse values as decimal numbers
     *
     * @param mixed $string A string containing a number
     *
     * @return string|null A valid decimal number
     */
    public function formatDecimal($string): ?string
    {
        if ($string === '') {
            return null;
        }
        $number = $this->formatFloat($string);
        return str_replace('.', ',', $number);
    }


    /**
     * Parses the given string value to extract an integer.
     *
     * @param mixed $value A string containing an integer
     *
     * @return int A valid integer
     */
    public function formatInt($value): int
    {
        $intVal = preg_replace('/\D/', '', trim($value));
        if (strlen($intVal) > 1) {
            $intVal = ltrim($intVal, '0');
        }
        //Return 0 if string is empty
        if ($intVal === '') {
            $intVal = 0;
        }
        //if value starts with minus sign, add sign to intVal (negative value)
        if (strpos($value, '-') === 0) {
            $intVal = '-' . $intVal;
        }
        return $intVal;
    }

    /**
     * Try to convert a date value into the format "YYYY-MM-DD"
     *
     * @param mixed $value
     * @return DateTime|null
     */
    public function formatDate($value): ?DateTime
    {
        if (empty($value)) {
            return null;
        }
        $date = $this->convertDateString($value);
        // Value is already in correct format
        if ($date instanceof DateTime) {
            return $date;
        }
        $year = 0;
        $month = 0;
        $day = 0;
        if (strpos($value, '.') !== false) {
            $dateParts = explode('.', $value);
            if (count($dateParts) === 3) {
                $year = (int)$dateParts[2];
                $month = (int)$dateParts[1];
                $day = (int)$dateParts[0];
            }
        } else {
            $dateParts = explode('-', $value);
            $year = (int)$dateParts[0];
            $month = (int)$dateParts[1];
            $day = (int)$dateParts[2];
        }
        if ($day > 0 && $month > 0 && $year > 0) {
            if (strlen($year) === 2) {
                $year += $year > 50 ? 1900 : 2000;
            }
            if ($month < 10) {
                $month = '0' . $month;
            }
            if ($day < 10) {
                $day = '0' . $day;
            }
            $value = $year . '-' . $month . '-' . $day;
            $dateObj = date_create($value);
            $dateObj->setTime(0, 0, 0);
            return $dateObj;
        }
        return null;
    }

    /**
     * Attempts to convert a string into a DateTime object
     *
     * @param string $value
     *
     * @return bool|DateTime
     */
    protected function convertDateString(string $value)
    {
        $date = self::dateGerman2Iso($value);
        if (method_exists('DateTime', 'createFromFormat')) {
            $date = DateTime::createFromFormat('Y-m-d', $date);

            // Invalid dates can show up as warnings (ie. "2007-02-99")
            // and still return a DateTime object.
            $errors = DateTime::getLastErrors();
            if ($errors['warning_count'] > 0) {
                return false;
            }
            $date->setTime(0, 0, 0);
        }

        return $date;
    }

    /**
     * Transforms a german date to a MySQL date (ISO-Date).
     *
     * @param string $checkDate Date string with format dd.mm.YYYY (or YY)
     * @return string Iso date
     */
    public static function dateGerman2Iso(string $checkDate): string
    {
        if (empty($checkDate)) {
            return '0000-00-00';
        }
        //If date does not have format dd.mm.YYYY
        if (strpos($checkDate, '.') === false) {
            return $checkDate;
        }
        $date = $checkDate;
        [$day, $month, $year] = explode('.', $date);
        if (strlen($year) === 2) {
            $year += 2000;
        }
        return sprintf('%04d-%02d-%02d', $year, $month, $day);
    }

    /**
     * Returns the given name as a cleaned field name (lowercase, without special characters)
     * @param string $name
     * @return string
     */
    public function getCleanFieldName(string $name): string
    {
        return strtolower(
            str_replace(['/', ' ', '-', '(', ')', '.', '___', '__'],
                '_',
                self::cleanStringValue($this->formatString(trim($name))))
        );
    }
}
