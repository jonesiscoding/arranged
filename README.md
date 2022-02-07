# Dates Arranged
PHP Library with objects for managing date formats and formatting for date periods.

### Formatting Functionality

Utilizing the `DevCoding\Arranger\Helper\DatePeriodFormatter::format` method, a native PHP `DatePeriod` object can be
formatted using the same tokens as the `\DateTimeInterface::format` method. The end date string is modified to remove
any redundant portions.  For example, if the start & end dates are on the same calendar day, the date is entirely
removed from the end date string. For more examples, see the `format` method.

### Reduction Functionality

Utilizing the `DevCoding\Arranger\Helper\DatePeriodFormatter::reduce` method, a native PHP `DatePeriod` object can be
formatted using the same tokens as the `\DateTimeInterface::format` method, then reduced in length to a specific length
or as much as possible. For example, if the start or end times are at the top of the hour and a separator is included
in the format string, the separator and the 00 is removed. For more examples, see the `reduce` method.

### Convenience Class

For convenience, the `DevCoding\Arranger\Object\DatePeriod` class extends the native PHP `DatePeriod` class with`format` 
and `reduce` methods that utilize the methods from `DevCoding\Arranger\Helper\DatePeriodFormatter`.

### Installation & Usage

Installation is simple via composer `composer require deviscoding/arranged`.

Usage is simple:

    $StartDate  = new \DateTime('2021-01-06 14:20:00')
    $EndDate    = new \DateTime('2021-01-06 23:32:00');
    $Interval   = new \DateInterval('PT15M');
    $DatePeriod = new \DevCoding\DevCoding\Object\Date\DatePeriod($StartDate, $Interval, $EndDate);
    $formatted  = $DatePeriod->format('l, F dS g:ia');
    $reduced    = $DatePeriod->reduce('l, F dS g:ia', 40);
    $further    = $DatePeriod->reduce('l, F dS g:ia');
    echo $formatted.' OR '.$reduced.' OR '.$further;

The above should print *Wednesday, January 6th 2:20pm-11:30pm OR Wednesday, January 6th 2:20-11:30pm OR Wed, Jan 6th, 
2:20-11:30pm*.

### Dependencies
There are no dependencies for this library other than PHP 7+.

### Expressing Your Gratitude
To be honest, simply telling a friend about this library would be thrilling. Well, that is, if I knew about it. So,
maybe star the repo, mention [@jonesiscoding](https://twitter.com/jonesiscoding/) in a tweet, or drop me a line via
the email address found in the source.

I enjoy coding enough that writing code can be its own reward, but writing code that no one ever sees or uses is
quite boring. I'll be very glad if this little library gets some use beyond its original use case.
