<?php
/**
 * DateFormat.php
 *
 * (c) AMJones <am@jonesiscoding.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DevCoding\Arranged\Object;

use DevCoding\Arranged\Exception\DateFormatException;

/**
 * Object representing a string that consists of PHP date format characters.
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/arranged/blob/main/LICENSE
 *
 * @package DevCoding\Arranged\Object
 */
class DateFormat
{
  const PATTERN_TIME     = '([HhGg][isaAuveT\s:.]+)';
  const PATTERN_DATE     = '([FmMnYydDjl\s._,•\-/\\\]+)';
  const PATTERN_NOT_HOUR = '([^HhGg]+)';

  /** @var bool */
  protected $date_present;
  /** @var bool */
  protected $day_present;
  /** @var bool */
  protected $date_separated;
  /** @var string */
  protected $string;
  /** @var DateFormat|null */
  protected $format_date;
  /** @var DateFormat|null */
  protected $format_time;
  /** @var bool */
  protected $time_present;
  /** @var bool */
  protected $time_separated;

  /**
   * @param string $format   a string usable by \DateTime::format (https://www.php.net/manual/en/datetime.format.php)
   * @param bool   $validate determines whether the given format is validated at instantiation
   */
  public function __construct(string $format, $validate = true)
  {
    $this->string = $format;

    if ($validate)
    {
      if (!preg_match('#[crU]#', $this->string))
      {
        if (!preg_match('#'.self::PATTERN_DATE.'#', $this->string))
        {
          if (!preg_match('#'.self::PATTERN_TIME.'#', $this->string))
          {
            throw new DateFormatException($this->string);
          }
        }
      }
    }
  }

  /**
   * @return string
   */
  public function __toString()
  {
    return $this->string;
  }

  /**
   * Formats the given DateTimeInterface object using the format this object represents.
   *
   * @param \DateTimeInterface $DateTime the date/time to format
   *
   * @return string the formatted date/time
   */
  public function format(\DateTimeInterface $DateTime)
  {
    return $DateTime->format($this->toString());
  }

  /**
   * Returns a new DateFormat object that represents only the date portion of the format, or null if no tokens that
   * represent a date are present.
   *
   * @return DateFormat|null
   */
  public function getDatePart()
  {
    if (is_null($this->format_date) && $this->isDatePresent())
    {
      $part = preg_replace('#'.self::PATTERN_TIME.'#', '', $this->string);

      if (preg_match('#'.self::PATTERN_DATE.'#', $part, $matches))
      {
        $trimmed = preg_replace('#([\n\r\s\\\/,•._\-]+)$#','',$matches[1]);

        $this->format_date = new DateFormat($trimmed);
      }
      else
      {
        // Make sure we only parse once
        $this->format_date = false;
      }
    }

    return $this->format_date ?? null;
  }

  /**
   * Returns a new DateFormat object that represents only the date portion of the format, or null if no tokens that
   * represent a date are present.
   *
   * @return DateFormat|null
   */
  public function getTimePart()
  {
    if (is_null($this->format_time) && $this->isTimePresent())
    {
      $part = preg_replace('#'.self::PATTERN_DATE.'#', '', $this->string);

      if (preg_match('#'.self::PATTERN_TIME.'#', $part, $matches))
      {
        $this->format_time = $this->getObject($matches[1]);
      }
      else
      {
        // Make sure we only parse once
        $this->format_time = false;
      }
    }

    return $this->format_time ?? null;
  }

  /**
   * Evaluates whether a token that represents a year is present in date format string represented by this object.
   *
   * @return bool
   */
  public function isYearPresent()
  {
    if ($this->isDatePresent())
    {
      return false !== stripos($this->string, 'y');
    }

    return false;
  }

  /**
   * Evaluates whether tokens representing a date are present in date format string represented by this object.
   *
   * @return bool
   */
  public function isDatePresent(): bool
  {
    if (is_null($this->date_present))
    {
      $this->date_present = preg_match('#([FmMnYydDjl\s._\-/\\\]+)#', $this->string);
    }

    return $this->date_present;
  }

  public function isDayPresent(): bool {
    if (is_null($this->day_present))
    {
      $this->day_present = $this->isLongDay() || $this->isShortDay();
    }

    return $this->day_present;
  }

  /**
   * Evaluates whether a token that represent post-meridiem or ante-meridiem are present in date format string
   * represented by this object.
   *
   * @return bool
   */
  public function isMeridiemPresent()
  {
    return false !== stripos($this->string, 'a');
  }

  /**
   * Evaluates whether tokens representing a time are present in date format string represented by this object.
   *
   * @return bool
   */
  public function isTimePresent(): bool
  {
    if (is_null($this->time_present))
    {
      $this->time_present = preg_match('#([^HhGg]+([HhGg][isaAuveT\s:]+))#', $this->string);
    }

    return $this->time_present;
  }

  /**
   * Evaluates whether a token that represents a minute is present in date format string represented by this object.
   *
   * @return bool
   */
  public function isMinutePresent()
  {
    return false !== strpos($this->string, 'i');
  }

  /**
   * Evaluates whether the date portion of the date format string represented by this object contains separators such
   * as backslashes, slashes, bullets, commas, periods, underscores, or dashes.
   *
   * @return bool
   */
  public function isDateSeparated(): bool
  {
    if (is_null($this->date_separated))
    {
      $datePart = $this->getDatePart();
      $this->date_separated = preg_match('#([\\\/,•._\-]+)#', $datePart);
    }

    return $this->date_separated;
  }

  /**
   * Evaluates whether the time portion of the date format string represented by this object contains separators such
   * as backslashes, slashes, colons, periods, underscores, or dashes.
   *
   * @return bool
   */
  public function isTimeSeparated(): bool
  {
    if (is_null($this->time_separated))
    {
      $this->time_separated = preg_match('#([\\\/:._\-]+)#', $this->getTimePart()->toString());
    }

    return $this->time_separated;
  }

  /**
   * Evaluates whether tokens that will result in leading zeros are present within the date format string represented
   * by this object.
   *
   * @return bool
   */
  public function isLeadingZeros()
  {
    foreach (['d', 'm', 'h'] as $try)
    {
      if (false !== strpos($this->string, $try))
      {
        return true;
      }
    }

    return false;
  }

  /**
   * Evaluates whether the token that represents a fully spelled day of the month is present in date format string
   * represented by this object.
   *
   * @return bool
   */
  public function isLongMonth()
  {
    return false !== strpos($this->string, 'F');
  }

  /**
   * Evaluates whether the token that represents a fully spelled out day of the week is present in date format string
   * represented by this object.
   *
   * @return bool
   */
  public function isLongDay()
  {
    return false !== strpos($this->string, 'l');
  }

  public function isShortDay()
  {
    return false !== strpos($this->string, 'D');
  }

  /**
   * Removes any tokens that will result in leading zeros from the date format represented by this object, then returns
   * a new DateFormat object.
   *
   * @return DateFormat
   */
  public function removeLeadingZeros()
  {
    return $this->getObject(str_replace(['D', 'm', 'h'], ['j', 'n', 'g'], $this->string));
  }

  /**
   * Replaces the tokens using the given regex pattern and replacement string, then returns a new DAteTime object
   * that represents the new date/time format string.
   *
   * @param string $delimitedPattern a regex pattern
   * @param string $replacement      a replacement string, optionally containing regex replacement variables
   *
   * @return DateFormat
   */
  public function replace($delimitedPattern, $replacement)
  {
    return $this->getObject(preg_replace($delimitedPattern, $replacement, $this->string));
  }

  /**
   * @return string
   */
  public function toString()
  {
    return (string) $this;
  }

  /**
   * Trims the given format, removing any separators, line breaks or spaces, then returns a new DateFormat object.
   *
   * @param string $format the date/time format string
   *
   * @return DateFormat
   */
  private function getObject($format)
  {
    return new static(trim($format, "\n\s\r-_./\\"));
  }
}
