<?php

namespace Kanboard\Core;

use DateTime;

/**
 * Date Parser
 *
 * @package  core
 * @author   Frederic Guillot
 */
class DateParser extends Base
{
    /**
     * List of time formats
     *
     * @access public
     * @return string[]
     */
    public function getTimeFormats()
    {
        return array(
            'H:i',
            'g:i a',
        );
    }

    /**
     * List of date formats
     *
     * @access public
     * @param  boolean  $iso
     * @return string[]
     */
    public function getDateFormats($iso = false)
    {
        $iso_formats = array(
            'Y-m-d',
            'Y_m_d',
        );

        $user_formats = array(
            'm/d/Y',
            'd/m/Y',
            'Y/m/d',
            'd.m.Y',
        );

        return $iso ? array_merge($iso_formats, $user_formats) : $user_formats;
    }

    /**
     * List of datetime formats
     *
     * @access public
     * @param  boolean  $iso
     * @return string[]
     */
    public function getDateTimeFormats($iso = false)
    {
        $formats = array();

        foreach ($this->getDateFormats($iso) as $date) {
            foreach ($this->getTimeFormats() as $time) {
                $formats[] = $date.' '.$time;
            }
        }

        return $formats;
    }

    /**
     * List of all date formats
     *
     * @access public
     * @param  boolean  $iso
     * @return string[]
     */
    public function getAllDateFormats($iso = false)
    {
        return array_merge($this->getDateFormats($iso), $this->getDateTimeFormats($iso));
    }

    /**
     * Get available formats (visible in settings)
     *
     * @access public
     * @param  array  $formats
     * @return array
     */
    public function getAvailableFormats(array $formats)
    {
        $values = array();

        foreach ($formats as $format) {
            $values[$format] = date($format);
        }

        return $values;
    }

    /**
     * Parse a date and return a unix timestamp, try different date formats
     *
     * @access public
     * @param  string   $value   Date to parse
     * @return integer
     */
    public function getTimestamp($value)
    {
        if (ctype_digit($value)) {
            return (int) $value;
        }

        foreach ($this->getAllDateFormats(true) as $format) {
            $timestamp = $this->getValidDate($value, $format);

            if ($timestamp !== 0) {
                return $timestamp;
            }
        }

        return 0;
    }

    /**
     * Return a timestamp if the given date format is correct otherwise return 0
     *
     * @access private
     * @param  string   $value  Date to parse
     * @param  string   $format Date format
     * @return integer
     */
    private function getValidDate($value, $format)
    {
        $date = DateTime::createFromFormat($format, $value);

        if ($date !== false) {
            $errors = DateTime::getLastErrors();
            if ($errors['error_count'] === 0 && $errors['warning_count'] === 0) {
                $timestamp = $date->getTimestamp();
                return $timestamp > 0 ? $timestamp : 0;
            }
        }

        return 0;
    }

    /**
     * Return true if the date is within the date range
     *
     * @access public
     * @param  DateTime  $date
     * @param  DateTime  $start
     * @param  DateTime  $end
     * @return boolean
     */
    public function withinDateRange(DateTime $date, DateTime $start, DateTime $end)
    {
        return $date >= $start && $date <= $end;
    }

    /**
     * Get the total number of hours between 2 datetime objects
     * Minutes are rounded to the nearest quarter
     *
     * @access public
     * @param  DateTime $d1
     * @param  DateTime $d2
     * @return float
     */
    public function getHours(DateTime $d1, DateTime $d2)
    {
        $seconds = $this->getRoundedSeconds(abs($d1->getTimestamp() - $d2->getTimestamp()));
        return round($seconds / 3600, 2);
    }

    /**
     * Round the timestamp to the nearest quarter
     *
     * @access public
     * @param  integer    $seconds   Timestamp
     * @return integer
     */
    public function getRoundedSeconds($seconds)
    {
        return (int) round($seconds / (15 * 60)) * (15 * 60);
    }

    /**
     * Get ISO-8601 date from user input
     *
     * @access public
     * @param  string   $value   Date to parse
     * @return string
     */
    public function getIsoDate($value)
    {
        return date('Y-m-d', $this->getTimestamp($value));
    }

    /**
     * Get a timetstamp from an ISO date format
     *
     * @access public
     * @param  string   $value
     * @return integer
     */
    public function getTimestampFromIsoFormat($value)
    {
        return $this->removeTimeFromTimestamp(ctype_digit($value) ? $value : strtotime($value));
    }

    /**
     * Remove the time from a timestamp
     *
     * @access public
     * @param  integer $timestamp
     * @return integer
     */
    public function removeTimeFromTimestamp($timestamp)
    {
        return mktime(0, 0, 0, date('m', $timestamp), date('d', $timestamp), date('Y', $timestamp));
    }

    /**
     * Format date (form display)
     *
     * @access public
     * @param  array    $values   Database values
     * @param  string[] $fields   Date fields
     * @param  string   $format   Date format
     * @return array
     */
    public function format(array $values, array $fields, $format)
    {
        foreach ($fields as $field) {
            if (! empty($values[$field])) {
                $values[$field] = date($format, $values[$field]);
            } else {
                $values[$field] = '';
            }
        }

        return $values;
    }

    /**
     * Convert date to timestamp
     *
     * @access public
     * @param  array    $values     Database values
     * @param  string[] $fields     Date fields
     * @param  boolean  $keep_time  Keep time or not
     * @return array
     */
    public function convert(array $values, array $fields, $keep_time = false)
    {
        foreach ($fields as $field) {
            if (! empty($values[$field])) {
                $timestamp = $this->getTimestamp($values[$field]);
                $values[$field] = $keep_time ? $timestamp : $this->removeTimeFromTimestamp($timestamp);
            }
        }

        return $values;
    }
}
