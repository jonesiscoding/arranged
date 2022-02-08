<?php
/**
 * DatePeriod.php
 *
 * (c) AMJones <am@jonesiscoding.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DevCoding\Arranged\Object;

use DevCoding\Arranged\Helper\DatePeriodFormatter;

/**
 * Object representing a DatePeriod, extending the native PHP DatePeriod object by adding formatting and reduction
 * methods.
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/arranged/blob/main/LICENSE
 * @package DevCoding\Arranged\Object
 */
class DatePeriod extends \DatePeriod
{
  /**
   * Formats the DatePeriod into a string.  See DatePeriodFormatter::format for details.
   *
   * @param string $format the format to apply to this object's DatePeriod, applied to both start and end
   * @param string $sep    the separator between start and end date/time
   *
   * @see DatePeriodFormatter::format()
   * @return string the formatted start & end dates, with the separator between
   */
  public function format($format, $sep = null)
  {
    return (new DatePeriodFormatter($this))->format($format, $sep);
  }

  /**
   * Formats the DatePeriod into a string, reducing the size of the resulting string. See DatePeriodFormatter::reduce
   * for details.
   *
   * @param string   $format the format to apply to this object's DatePeriod, applied to both start and end
   * @param string   $sep    the separator between start and end date/time
   * @param int|null $max    optional maximum number of characters; if null will reduce using all methods
   *
   * @see DatePeriodFormatter::reduce()
   * @return string the formatted and reduced start & end dates, with the separator between
   */
  public function reduce($format, $sep = null, $max = null)
  {
    return (new DatePeriodFormatter($this))->reduce($format, $sep, $max);
  }
}
