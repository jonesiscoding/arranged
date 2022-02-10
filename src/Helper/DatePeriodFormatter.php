<?php
/**
 * DatePeriodFormatter.php
 *
 * (c) AMJones <am@jonesiscoding.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DevCoding\Arranged\Helper;

use DevCoding\Arranged\Object\DateFormat;

/**
 * Provides methods to format the given DatePeriod, and reduce the length of the formatted string by removing redundant
 * portions of the format given at the time of the reduction.
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/arranged/blob/main/LICENSE
 *
 * @package DevCoding\Arranged\Helper
 */
class DatePeriodFormatter
{
  /** @var \DatePeriod */
  protected $period;

  /**
   * @param \DatePeriod $period the date period object this formatter will format
   */
  public function __construct(\DatePeriod $period)
  {
    $this->period = $period;
  }

  // region //////////////////////////////////////////////// Public Methods

  /**
   * Formats the DatePeriod into a string, eliminating any redundant word portions of the end date. For instance, if the
   * start & end dates are on the same calendar day, the date is entirely removed from the end date. If the start & end
   * date are in the same month, and there is no time or the time is identical, the month is omitted.
   *
   * @param string $format the format to apply to this object's DatePeriod, applied to both start and end
   * @param string $sep    the separator between start and end date/time
   *
   * @return string the formatted start & end dates, with the separator between
   */
  public function format($format, $sep = '-')
  {
    $DateFormat = new DateFormat($format);

    if ($this->isSameDateTime())
    {
      return $this->period->getStartDate()->format($format);
    }
    elseif (!$this->isSameDay())
    {
      // We don't have the same date for start/end, check if we have the same month
      if ($this->isSameMonth() && !$DateFormat->isYearPresent() && !$DateFormat->isDateSeparated())
      {
        if (!$DateFormat->isTimePresent() || $this->isSameTime())
        {
          // If we have the same month, no year, no standard separators, and no time, remove the Month from the End Format
          $EndFormat = $DateFormat->replace('/([FmMn]+)(.*)/', '$2');
        }
      }
    }
    elseif ($DateFormat->isDatePresent())
    {
      // If we have the same date for start & end, but the format includes the date, omit it for the end.
      $EndFormat = $DateFormat->getTimePart();
    }

    $EndFormat = $EndFormat ?? $DateFormat;
    $template  = 1 === strlen($sep) ? '%s%s%s' : '%s %s %s';

    return sprintf($template, $DateFormat->format($this->period->getStartDate()), $sep, $EndFormat->format($this->period->getEndDate()));
  }

  /**
   * Evaluates whether the start & end DateTime of this object's DatePeriod are within the same calendar day.
   *
   * @return bool
   */
  public function isSameDay(): bool
  {
    return $this->isSameDatePart('Ymd');
  }

  /**
   * @return bool
   */
  public function isSameDateTime()
  {
    return $this->isSameDatePart('YmdHis');
  }

  /**
   * Evaluates whether the start & end DateTime of this object's DatePeriod are both post-meridiem or ante-meridiem.
   *
   * @return bool
   */
  public function isSameMeridiem(): bool
  {
    return $this->isSameDatePart('a');
  }

  /**
   * Evaluates whether the start & end DateTime of this object's DatePeriod are within the same calendar month.
   *
   * @return bool
   */
  public function isSameMonth(): bool
  {
    return $this->isSameDatePart('Ym');
  }

  /**
   * Evaluates whether the start & end DateTime of this object's DatePeriod occur at the same time of day.  This does
   * not imply that they occur on the same calendar day, just that they occur at the same hour, minute, and second.
   *
   * @return bool
   */
  public function isSameTime(): bool
  {
    return $this->isSameDatePart('His');
  }

  /**
   * Evaluates whether the start & end DateTime of this object's DatePeriod are within the same calendar year.
   *
   * @return bool
   */
  public function isSameYear(): bool
  {
    return $this->isSameDatePart('Y');
  }

  /**
   * Formats the DatePeriod into a string, reducing the length of the string.  First, the rules of the 'format' method
   * of this class are utilized, then redundant time elements (such as :00 min, repeated post-meridiem/ante-meridiem).
   *
   * If the string is still too long for the $max argument or the $max argument is null, additional optimization is
   * performed. The length of the resulting string is checked after each optimization. The leading zeros from month,
   * day, and hour removed, then the month name is abbreviated, then the day of the week name is abbreviated.
   *
   * @param string   $format the format to apply to this object's DatePeriod, applied to both start and end
   * @param string   $sep    the separator between start and end date/time
   * @param int|null $max    optional maximum number of characters; if null will reduce using all methods
   *
   * @return string the formatted DatePeriod string, reduced to meet the criteria given
   */
  public function reduce(string $format, $sep = '-', $max = null): string
  {
    if ($this->isSameDateTime())
    {
      return $this->format($format, $sep);
    }

    $output = $this->reduceTime($format, $sep);
    if ($this->isMaxExceeded($output, $max))
    {
      $DateFormat = new DateFormat($format);

      // Remove the Leading Zeros
      if ($DateFormat->isLeadingZeros())
      {
        $newFormat = $DateFormat->removeLeadingZeros()->toString();
        if ($newFormat != $format)
        {
          return $this->reduce($newFormat, $sep, $max);
        }
      }

      // Reduce Month
      if ($DateFormat->isLongMonth())
      {
        $newFormat = $DateFormat->replace('#[F]#', 'M')->toString();
        if ($newFormat != $format)
        {
          return $this->reduce($newFormat, $sep, $max);
        }
      }

      // Reduce Day of Week
      if ($DateFormat->isLongDay())
      {
        $newFormat = $DateFormat->replace('#[l]#', 'D')->toString();
        if ($newFormat != $format)
        {
          return $this->reduce($newFormat, $sep, $max);
        }
      }
    }

    return $output;
  }

  // endregion ///////////////////////////////////////////// Public Methods

  // region //////////////////////////////////////////////// Helper Methods

  /**
   * Reduces the time portion of the start and end dates of this object's DatePeriod if a time is present in the given
   * format. If minutes are present and the parts of the time are separated in the format, the minutes are removed for
   * any time that is at the top of the hour (IE - :00).  If post-meridiem or ante-meridiem is present, are the same,
   * and the start and end dates are within the same calendar day, the meridiem is removed from the starting time.
   *
   * @param string $format the format to apply to this object's DatePeriod, applied to both start and end
   * @param string $sep    the separator between start and end date/time
   *
   * @return string the formatted DatePeriod string, reduced to meet the criteria mentioned above
   */
  protected function reduceTime($format, $sep = '-'): string
  {
    $TimeFormat = new DateFormat($format);
    if ($TimeFormat->isTimePresent())
    {
      if ($TimeFormat->isMinutePresent())
      {
        if ($TimeFormat->isTimeSeparated())
        {
          $isStartHourTop = '00' === $this->period->getStartDate()->format('i');
          $isEndHourTop   = '00' === $this->period->getEndDate()->format('i');

          if ($isStartHourTop && $isEndHourTop)
          {
            $TimeFormat = $TimeFormat->replace('#:i#', '');
          }
          elseif ($isEndHourTop)
          {
            $EndFormat = $TimeFormat->replace('#:i#', '');
          }
          elseif ($isStartHourTop)
          {
            $EndFormat  = $EndFormat ?? $TimeFormat;
            $TimeFormat = $TimeFormat->replace('#:i#', '');
          }
        }
      }

      if ($TimeFormat->isMeridiemPresent() && $this->isSameMeridiem() && $this->isSameDay())
      {
        $EndFormat  = $EndFormat ?? $TimeFormat;
        $TimeFormat = $TimeFormat->replace('#[aA]#', '');
        $EndFormat  = $EndFormat->getTimePart();
      }
      elseif ($this->isSameDay())
      {
        $EndFormat = $EndFormat ?? $TimeFormat;
        $EndFormat = $EndFormat->getTimePart();
      }

      $EndFormat = $EndFormat ?? $TimeFormat;
      $template  = 1 === strlen($sep) ? '%s%s%s' : '%s %s %s';

      return sprintf($template, $TimeFormat->format($this->period->getStartDate()), $sep, $EndFormat->format($this->period->getEndDate()));
    }

    return $this->format($format, $sep);
  }

  /**
   * Evaluates the length of the string given to determine if it exceeds the max length given. If the $max argument is
   * empty, the string is always considered to be exceeding the maximum.
   *
   * @param string   $str
   * @param int|null $max
   *
   * @return bool
   */
  private function isMaxExceeded($str, $max = null): bool
  {
    return strlen($str) > ($max ?? 0);
  }

  /**
   * Evaluates the start date and end date of this object's DatePeriod to determine if the two strings are identical
   * when formatted with the given format.
   *
   * @param string $format
   *
   * @return bool
   */
  private function isSameDatePart($format): bool
  {
    return $this->period->getStartDate()->format($format) === $this->period->getEndDate()->format($format);
  }

  // endregion ///////////////////////////////////////////// Helper Methods
}
