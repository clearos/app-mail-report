<?php

/**
 * Mail report class.
 *
 * @category   Apps
 * @package    Mail_Report
 * @subpackage Libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2012 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/mail_report/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Lesser General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// N A M E S P A C E
///////////////////////////////////////////////////////////////////////////////

namespace clearos\apps\mail_report;

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

///////////////////////////////////////////////////////////////////////////////
// T R A N S L A T I O N S
///////////////////////////////////////////////////////////////////////////////

clearos_load_language('mail_report');

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

// Classes
//--------

use \clearos\apps\base\Engine as Engine;
use \clearos\apps\base\File as File;

clearos_load_library('base/Engine');
clearos_load_library('base/File');

// Exceptions
//-----------

use \Exception as Exception;
use \clearos\apps\base\File_Not_Found_Exception as File_Not_Found_Exception;

clearos_load_library('base/File_Not_Found_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Mail report class.
 *
 * @category   Apps
 * @package    Mail_Report
 * @subpackage Libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2012 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/mail_report/
 */

class Mail_Report extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    const PATH_REPORTS = '/var/clearos/mail_report';
    const DEFAULT_MAX_RECORDS = 500;

    const TIME_YESTERDAY = 'yesterday';
    const TIME_TODAY = 'today';
    const TIME_MONTH = 'month';

    const TYPE_OTHER = 'other';
    const TYPE_DOMAIN_SUMMARY_DELIVERED = 'domain_delivered';
    const TYPE_DOMAIN_SUMMARY_RECEIVED = 'domain_received';
    const TYPE_SENDERS = 'senders';
    const TYPE_RECIPIENTS = 'recipients';
    const TYPE_SENDERS_BY_SIZE = 'senders_by_size';
    const TYPE_RECIPIENTS_BY_SIZE = 'recipients_by_size';
    const TYPE_BOUNCED = 'bounced';
    const TYPE_REJECTED = 'rejected';
    const TYPE_DISCARDED = 'discarded';
    const TYPE_DELIVERY_FAILURES = 'delivery_failures';
    const TYPE_WARNING = 'warning';
    const TYPE_SUMMARY = 'summary';
    const TYPE_DAILY = 'daily';

    const TYPE_DEFERRED = 'deferred';
    const TYPE_DELIVERED = 'delivered';
    const TYPE_FORWARDED = 'forwarded';
    const TYPE_REJECT_WARNING = 'reject_warning';
    const TYPE_HELD = 'held';
    const TYPE_RECEIVED = 'received';

    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    protected $data = '';
    protected $loaded = FALSE;
    protected $no_data = TRUE;

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Mail report constructor.
     */

    public function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
     * Returns dashboard summary report.
     *
     * @param string $range report time range
     *
     * @return array summary data
     */

    public function get_summary($range = self::TIME_TODAY)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->loaded)
            $this->_get_data($range);

        if ($this->no_data)
            return array();

        return $this->data[self::TYPE_SUMMARY];
    }

    /**
     * Returns daily summary over the last 30 days.
     *
     * @return array summary data
     */

    public function get_month_summary()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->loaded)
            $this->_get_data(self::TIME_MONTH);

        if ($this->no_data)
            return array();

        return $this->data[self::TYPE_DAILY];
    }

    /**
     * Returns domain summary delivered report.
     *
     * @return domain summary sent data
     */

    public function get_domain_sent()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->loaded)
            $this->_get_data();

        $data = array();

        foreach ($this->data[self::TYPE_DOMAIN_SUMMARY_DELIVERED] as $line) {
            $matches = array();

            if (preg_match('/\s*(\d+)\s+(\d+)\s+(\d+)\s+([0-9\.]* \w)\s+([0-9\.]* \w)\s+(.*)\s*/', $line, $matches)) {
                $data[$matches[6]]['count']= $matches[1];
                $data[$matches[6]]['size']= $matches[2];
                $data[$matches[6]]['defers']= $matches[3];
                $data[$matches[6]]['average_time']= $matches[4];
                $data[$matches[6]]['max_time']= $matches[5];
            }
        }

        return $data;
    }

    /**
     * Returns domain summary received report.
     *
     * @return domain summary received data
     */

    public function get_domain_received()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->loaded)
            $this->_get_data();

        $data = array();

        foreach ($this->data[self::TYPE_DOMAIN_SUMMARY_RECEIVED] as $line) {
            $matches = array();

            if (preg_match('/\s*(\d+)\s+(\d+)\s+(.*)\s*/', $line, $matches)) {
                $data[$matches[3]]['count']= $matches[1];
                $data[$matches[3]]['size']= $matches[2];
            }
        }

        return $data;
    }

    /**
     * Returns recipients report.
     *
     * @param string  $range         report time range
     * @param integer $summary_index summarize data after this point
     *
     * @return array recipients with number of deliveries
     */

    public function get_recipients($range = self::TIME_TODAY, $summary_index = 0)
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->_parse_simple_report(self::TYPE_RECIPIENTS, $range, $summary_index);
    }

    /**
     * Returns recipients by size report.
     *
     * @return array recipients with mail size
     */

    public function get_recipients_by_size()
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->_parse_simple_report(self::TYPE_RECIPIENTS_BY_SIZE);
    }

    /**
     * Returns senders report.
     *
     * @param string  $range         report time range
     * @param integer $summary_index summarize data after this point
     *
     * @return array senders with number of deliveries
     */

    public function get_senders($range = self::TIME_TODAY, $summary_index = 0)
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->_parse_simple_report(self::TYPE_SENDERS, $range, $summary_index);
    }

    /**
     * Returns senders by size report.
     *
     * @return array senders with mail size
     */

    public function get_senders_by_size()
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->_parse_simple_report(self::TYPE_SENDERS_BY_SIZE);
    }

    /**
     * Returns message bounce detail.
     *
     * @return array message bounce detail
     */

    public function get_message_bounce_detail()
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->_parse_simple_report(self::TYPE_BOUNCED);
    }

    /**
     * Returns message reject report.
     *
     * @return array message reject detail
     */

    public function get_message_reject_detail()
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->_parse_simple_report(self::TYPE_REJECTED);
    }

    /**
     * Returns message discard detail report.
     *
     * @return array message discard detail
     */

    public function get_message_discard_detail()
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->_parse_simple_report(self::TYPE_DISCARDED);
    }

    /**
     * Returns smtp delivery failures report.
     *
     * @return array delivery failures
     */

    public function get_delivery_failures()
    {
        clearos_profile(__METHOD__, __LINE__);

        // FIXME - verify with real data
        return $this->_parse_simple_report(self::TYPE_DELIVERY_FAILURES);
    }

    /**
     * Returns the available report types.
     *
     * @return array list of report types
     */

    public function get_types()
    {
        clearos_profile(__METHOD__, __LINE__);

        $types = array();

        $types[self::TIME_MONTH] = POSTFIX_LANG_MONTH;
        $types[self::TIME_YESTERDAY] = POSTFIX_LANG_YESTERDAY;
        $types[self::TIME_TODAY] = POSTFIX_LANG_TODAY;

        return $types;
    }

    /**
     * Returns warnings report.
     *
     * @return array mail warnings
     */

    public function get_warnings()
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->_parse_simple_report(self::TYPE_WARNING);
    }

    /**
     * Returns state of data availability.
     *
     * @return boolean TRUE if data is available
     * @throws Engine_Exception
     */

    public function is_data_available()
    {
        if (!$this->loaded)
            $this->_get_data();

        if ($this->no_data)
            return FALSE;
        else
            return TRUE;
    }

    ///////////////////////////////////////////////////////////////////////////////
    // P R I V A T E  M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Loads report data.
     *
     * @param string $range time range
     *
     * @access private
     * @return void
     */

    protected function _get_data($range = self::TIME_TODAY)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $file = new File(self::PATH_REPORTS . '/data-' . $range . '.out');
            $lines = $file->get_contents_as_array();
        } catch (File_Not_Found_Exception $e) {
            return;
        }

        $daily_data = array();     // Daily statistics
        $summary_data = array();   // Message statistics

        $linecount = 0;
        $section = 'messages';

        foreach ($lines as $line) {
            if (preg_match('/^message deferral detail/', $line)) {
                $section = self::TYPE_OTHER;
            } else if (preg_match('/bytes received/', $line)) {
                $section = 'todo';
            } else if (preg_match('/Per-Day Traffic Summary/', $line)) {
                $section = 'daily';
            } else if (preg_match('/Per-Hour Traffic Daily Average/', $line)) {
                $section = 'todo';
            } else if (preg_match('/Host\/Domain Summary: Message Delivery/', $line)) {
                $section = self::TYPE_DOMAIN_SUMMARY_DELIVERED;
                continue;
            } else if (preg_match('/Host\/Domain Summary: Messages Received/', $line)) {
                $section = self::TYPE_DOMAIN_SUMMARY_RECEIVED;
                continue;
            } else if (preg_match('/Senders by message count/', $line)) {
                $section = self::TYPE_SENDERS;
                continue;
            } else if (preg_match('/Recipients by message count/', $line)) {
                $section = self::TYPE_RECIPIENTS;
                continue;
            } else if (preg_match('/Senders by message size/', $line)) {
                $section = self::TYPE_SENDERS_BY_SIZE;
                continue;
            } else if (preg_match('/Recipients by message size/', $line)) {
                $section = self::TYPE_RECIPIENTS_BY_SIZE;
                continue;
            } else if (preg_match('/message bounce detail/', $line)) {
                $section = self::TYPE_BOUNCED;
                continue;
            } else if (preg_match('/message reject detail/', $line)) {
                $section = self::TYPE_REJECTED;
                continue;
            } else if (preg_match('/message discard detail/', $line)) {
                $section = self::TYPE_DISCARDED;
                continue;
            } else if (preg_match('/smtp delivery failures/', $line)) {
                $section = self::TYPE_DELIVERY_FAILURES;
                continue;
            } else if (preg_match('/Warnings/', $line)) {
                $section = self::TYPE_WARNING;
                continue;
            }

            // Daily report data
            //------------------

            if ($section == 'daily') {
                $line = preg_replace('/\s+/', ' ', $line);
                $lineparts = explode(' ', $line);

                if (!isset($lineparts[8]) || (!preg_match('/^\d+/', $lineparts[2])))
                    continue;
                
                $unixtime = strtotime($lineparts[0] . ' ' . $lineparts[1] . ' ' . $lineparts[2]);
                $thedate = strftime('%b %e %Y', $unixtime);

                $daily_data[$thedate][self::TYPE_RECEIVED] = (int) trim($lineparts[4]);
                $daily_data[$thedate][self::TYPE_DELIVERED] = (int) trim($lineparts[5]);
                $daily_data[$thedate][self::TYPE_DEFERRED] = (int) trim($lineparts[6]);
                $daily_data[$thedate][self::TYPE_BOUNCED] = (int) trim($lineparts[7]);
                $daily_data[$thedate][self::TYPE_REJECTED] = (int) trim($lineparts[8]);

            } else if ($section == 'messages') {

                // Grand totals
                //-------------

                if (preg_match('/received/', $line)) {
                    $summary_data[self::TYPE_RECEIVED] = (int) trim(preg_replace('/received.*/', '', $line));
                    if ($summary_data[self::TYPE_RECEIVED] != 0)
                        $this->no_data = FALSE;
                } else if (preg_match('/delivered/', $line)) {
                    $summary_data[self::TYPE_DELIVERED] = (int) trim(preg_replace('/delivered.*/', '', $line));
                } else if (preg_match('/forwarded/', $line)) {
                    $summary_data[self::TYPE_FORWARDED] = (int) trim(preg_replace('/forwarded.*/', '', $line));
                } else if (preg_match('/deferred/', $line)) {
                    $summary_data[self::TYPE_DEFERRED] = (int) trim(preg_replace('/deferred.*/', '', $line));
                } else if (preg_match('/bounced/', $line)) {
                    $summary_data[self::TYPE_BOUNCED] = (int) trim(preg_replace('/bounced.*/', '', $line));
                } else if (preg_match('/rejected/', $line)) {
                    $summary_data[self::TYPE_REJECTED] = (int) trim(preg_replace('/rejected.*/', '', $line));
                } else if (preg_match('/reject warnings/', $line)) {
                    $summary_data[self::TYPE_REJECT_WARNING] = (int) trim(preg_replace('/reject warnings.*/', '', $line));
                } else if (preg_match('/held/', $line)) {
                    $summary_data[self::TYPE_HELD] = (int) trim(preg_replace('/held.*/', '', $line));
                } else if (preg_match('/discarded/', $line)) {
                    $summary_data[self::TYPE_DISCARDED] = (int) trim(preg_replace('/discarded.*/', '', $line));
                }


            } else if ($section != 'todo') {

                // Summary data
                //-------------

                if (! preg_match('/-------/', $line)) {
                    $linecount++;
                    $data[$section][$linecount] = $line;
                }
            }
        }

        $data['daily'] = $daily_data;
        $data['summary'] = $summary_data;

        $this->loaded = TRUE;
        $this->data = $data;
    }

    /**
     * Parses simple key/value reports.
     *
     * @param string  $type          report type
     * @param integer $summary_index summarize data after this point
     *
     * @return void
     */

    protected function _parse_simple_report($type, $range, $summary_index = 10)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->loaded)
            $this->_get_data($range);

        $count = 1;
        $summary_total = 0;
        $summary_required = FALSE;
        $data = array();

        foreach ($this->data[$type] as $line) {
            $matches = array();

            if (preg_match('/\s*(\d+)\s+(.*)\s*/', $line, $matches)) {
                $count++;

                if (($summary_index != 0) && ($count > $summary_index)) {
                    $summary_total += (int) ($matches[1]);
                    $summary_required = TRUE;
                } else if ($count >= self::DEFAULT_MAX_RECORDS) {
                    break;
                } else {
                    $data[$matches[2]] = (int) $matches[1];
                }
            }
        }

        if ($summary_required)
            $data['other'] = $summary_total;

        return $data;
    }
}
