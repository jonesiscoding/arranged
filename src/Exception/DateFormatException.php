<?php
/**
 * DateFormatException.php
 *
 * (c) AMJones <am@jonesiscoding.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DevCoding\Arranged\Exception;

/**
 * Exception used in the instance that a format string does not contain the proper tokens to be used to format
 * a DateTimeInterface using DateTimeInterface::format.
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/arranged/blob/main/LICENSE
 * @package DevCoding\Arranged\Exception
 */
class DateFormatException extends \InvalidArgumentException
{
  public function __construct($format, $code = 0, \Throwable $previous = null)
  {
    $template = 'The given string "%s" does not contain the characters to properly format a date using PHP\'s \DateTimeInterface::format.';

    parent::__construct(sprintf($template, $format), $code, $previous);
  }
}

