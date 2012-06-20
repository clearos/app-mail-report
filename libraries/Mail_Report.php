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

    const TYPE_DEFERRED = 'deferred';
    const TYPE_DELIVERED = 'delivered';
    const TYPE_FORWARDED = 'forwarded';
    const TYPE_REJECT_WARNING = 'reject_warning';
    const TYPE_HELD = 'held';
    const TYPE_RECEIVED = 'received';

    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    protected $loaded = FALSE;
    protected $data = '';
    protected $date_type;
    protected $nodata = TRUE;

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Mail report constructor.
     */

    public function __construct($date_type)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->date_type = $date_type;
    }

    /**
     * Returns dashboard summary report.
     *
     * @return string dashboard summary in HTML
     */

    public function get_dashboard_summary()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->loaded)
            $this->_get_data();

        if ($this->nodata)
            return;

        $chartheader = array();
        $chartheader[] = "";
        $chartdata = array();
        $chartdata[] = POSTFIX_LANG_MESSAGES;
        $htmlrows = "";

        foreach ($this->data["summary"] as $label => $value) {
            $htmlrows .= "<tr><td class='chartlegendkey'>$label</td><td>$value</td></tr>";
            if (($label == POSTFIX_LANG_RECEIVED) || ($label == POSTFIX_LANG_DELIVERED))
                continue;
            $chartheader[] = $label;
            $chartdata[] = $value;
        }

        // HTML Output
        //------------

        $legend = WebChartLegend(REPORT_LANG_SUMMARY, $htmlrows);
        WebTableOpen(POSTFIX_LANG_MESSAGES, "100%");
        echo "
          <tr>
            <td valign='top'>$legend</td>
            <td valign='top' align='center' width='350'>";
            WebChart(
                POSTFIX_LANG_MESSAGES,
                "bar", 
                350,
                250,
                array($chartheader, $chartdata),
                0,
                0,
                0,
                "/admin/postfixreport.php"
            );
        echo "
            </td>
          </tr>
        ";
        WebTableClose("100%");
    }

    /**
     * Returns monthly summary report.
     *
     * @return void
     */

    public function get_full_report($notimplemented)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->loaded)
            $this->_get_data();

        if ($this->nodata)
            return;

        $htmlrows = "";
        $chartheader = array();
        $chartheader[] = "";
        $chartdata_delivered = array();
        $chartdata_delivered[] = POSTFIX_LANG_DELIVERED;
        $chartdata_deferred = array();
        $chartdata_deferred[] = POSTFIX_LANG_DEFERRED;
        $chartdata_bounced = array();
        $chartdata_bounced[] = POSTFIX_LANG_BOUNCED;
        $chartdata_rejected = array();
        $chartdata_rejected[] = POSTFIX_LANG_REJECTED;

        foreach ($this->data["daily"] as $day => $keys) {
            $htmlrows .= "<tr><td class='chartlegendkey'>$day</td>";
            $chartheader[] = $day;
            foreach ($keys as $key => $value) {
                if (!$value)
                    $value = 0;
                else if ($key == POSTFIX_LANG_DELIVERED)
                    $chartdata_delivered[] = $value;
                else if ($key == POSTFIX_LANG_DEFERRED)
                    $chartdata_deferred[] = $value;
                else if ($key == POSTFIX_LANG_BOUNCED)
                    $chartdata_bounced[] = $value;
                else if ($key == POSTFIX_LANG_REJECTED)
                    $chartdata_rejected[] = $value;

                // Table output formatting
                if (!$value)
                    $value = "&#160;";
                $htmlrows .= "<td>$value</td>";
            }
            $htmlrows .= "</tr>";
        }

        if (! $htmlrows)
            return;

        $htmlrows = "
          <tr>
            <td class='chartlegendtitle'>" . LOCALE_LANG_DATE . "</td>
            <td class='chartlegendtitle'>" . POSTFIX_LANG_RECEIVED . "</td>
            <td class='chartlegendtitle'>" . POSTFIX_LANG_DELIVERED . "</td>
            <td class='chartlegendtitle'>" . POSTFIX_LANG_DEFERRED . "</td>
            <td class='chartlegendtitle'>" . POSTFIX_LANG_BOUNCED . "</td>
            <td class='chartlegendtitle'>" . POSTFIX_LANG_REJECTED . "</td>
          </tr>
        " . $htmlrows;
        $textsummary = WebChartLegend(POSTFIX_LANG_REPORT_DAILY, $htmlrows);

        WebTableOpen(POSTFIX_LANG_REPORT_DAILY, "100%");
        echo "
          <tr>
            <td align='center'>";
            WebChart(
                POSTFIX_LANG_REPORT_DAILY, 
                "stacked bar", 
                550,
                550, 
                array ($chartheader, $chartdata_delivered, $chartdata_deferred, $chartdata_bounced, $chartdata_rejected),
                array (CHART_COLOR_OK1, CHART_COLOR_OK2, CHART_COLOR_WARNING, CHART_COLOR_ALERT),
                0,
                FALSE
            );
        echo "
            </td>
          </tr>
          <tr>
            <td>$textsummary</td>
          </tr>
        ";
        WebTableClose("100%");
    }



    /**
     * Returns domain summary delivered report.
     *
     * @param int $max_records maximum number of records to return
     *
     * @return  void
     */

    public function get_domain_summary_delivered($max_records)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->get_report_detail(POSTFIX_LANG_REPORT_DOMAIN_SUMMARY_DELIVERED, self::TYPE_DOMAIN_SUMMARY_DELIVERED, $max_records);
    }


    /**
     * Returns domain summary received report.
     *
     * @param int $max_records maximum number of records to return
     *
     * @return  void
     */

    public function get_domain_summary_received($max_records)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->get_report_detail(POSTFIX_LANG_REPORT_DOMAIN_SUMMARY_RECEIVED, self::TYPE_DOMAIN_SUMMARY_RECEIVED, $max_records);
    }


    /**
     * Returns recipients by size report.
     *
     * @param int $max_records maximum number of records to return
     *
     * @return  void
     */

    public function get_recipients_by_size($max_records)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->get_report_detail(POSTFIX_LANG_RECIPIENTS . " - " . POSTFIX_LANG_SIZE, self::TYPE_RECIPIENTS_BY_SIZE, $max_records);
    }


    /**
     * Returns recipients report.
     *
     * @param int $max_records maximum number of records to return
     *
     * @return  void
     */

    public function get_recipients($max_records = 10)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->get_report_detail(lang('mail_report_recipients'), self::TYPE_RECIPIENTS, $max_records);
    }


    /**
     * Returns senders report.
     *
     * @param int $max_records maximum number of records to return
     *
     * @return  void
     */

    public function get_senders($max_records)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->get_report_detail(POSTFIX_LANG_SENDERS, self::TYPE_SENDERS, $max_records);
    }

    /**
     * Returns senders by size report.
     *
     * @param int $max_records maximum number of records to return
     *
     * @return  void
     */

    public function get_senders_by_size($max_records)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->get_report_detail(POSTFIX_LANG_SENDERS . " - " . POSTFIX_LANG_SIZE, self::TYPE_SENDERS_BY_SIZE, $max_records);
    }

    /**
     * Returns message bounce detail.
     *
     * @param int $max_records maximum number of records to return
     *
     * @return  void
     */

    public function get_message_bounce_detail($max_records)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->get_report_detail(POSTFIX_LANG_BOUNCED, self::TYPE_BOUNCED, $max_records);
    }


    /**
     * Returns message reject report.
     *
     * @param int $max_records maximum number of records to return
     *
     * @return  void
     */

    public function get_message_reject_detail($max_records)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->get_report_detail(POSTFIX_LANG_REJECTED, self::TYPE_REJECTED, $max_records);
    }


    /**
     * Returns message discard detail report.
     *
     * @param int $max_records maximum number of records to return
     *
     * @return  void
     */

    public function get_message_discard_detail($max_records)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->get_report_detail(POSTFIX_LANG_DISCARDED, self::TYPE_DISCARDED, $max_records);
    }


    /**
     * Returns smtp delivery failures report.
     *
     * @param int $max_records maximum number of records to return
     *
     * @return  void
     */

    public function get_smtp_delivery_failures($max_records)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->get_report_detail(POSTFIX_LANG_DELIVERY_FAILURES, self::TYPE_DELIVERY_FAILURES, $max_records);
    }

    /**
     * Returns the available report types.
     *
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
     * @param int $max_records maximum number of records to return
     *
     * @return  void
     */

    public function get_warnings($max_records)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->get_report_detail(LOCALE_LANG_WARNING, self::TYPE_WARNING, $max_records);
    }

    /**
     * Returns state of data availability.
     *
     *
     * @return boolean TRUE if data is available
     * @throws Engine_Exception
     */

    public function is_data_available()
    {
        if (!$this->loaded)
            $this->_get_data();

        if ($this->nodata)
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
     * @access private
     *
     * @return void
     */

    public function _get_data($date_type = 'today')
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $file = new File(self::PATH_REPORTS . '/data-' . $date_type . '.out');
            $lines = $file->get_contents_as_array();
        } catch (File_Not_Found_Exception $e) {
            return;
        }

        $daily_data = array();     // Daily statistics
        $summary_data = array();   // Message statistics

        $linecount = 0;
        $section = "messages";

        foreach ($lines as $line) {
            if (preg_match("/^message deferral detail/", $line)) {
                $section = self::TYPE_OTHER;
            } else if (preg_match("/bytes received/", $line)) {
                $section = "todo";
            } else if (preg_match("/Per-Day Traffic Summary/", $line)) {
                $section = "daily";
            } else if (preg_match("/Per-Hour Traffic Daily Average/", $line)) {
                $section = "todo";
            } else if (preg_match("/Host\/Domain Summary: Message Delivery/", $line)) {
                $section = self::TYPE_DOMAIN_SUMMARY_DELIVERED;
                continue;
            } else if (preg_match("/Host\/Domain Summary: Messages Received/", $line)) {
                $section = self::TYPE_DOMAIN_SUMMARY_RECEIVED;
                continue;
            } else if (preg_match("/Senders by message count/", $line)) {
                $section = self::TYPE_SENDERS;
                continue;
            } else if (preg_match("/Recipients by message count/", $line)) {
                $section = self::TYPE_RECIPIENTS;
                continue;
            } else if (preg_match("/Senders by message size/", $line)) {
                $section = self::TYPE_SENDERS_BY_SIZE;
                continue;
            } else if (preg_match("/Recipients by message size/", $line)) {
                $section = self::TYPE_RECIPIENTS_BY_SIZE;
                continue;
            } else if (preg_match("/message bounce detail/", $line)) {
                $section = self::TYPE_BOUNCED;
                continue;
            } else if (preg_match("/message reject detail/", $line)) {
                $section = self::TYPE_REJECTED;
                continue;
            } else if (preg_match("/message discard detail/", $line)) {
                $section = self::TYPE_DISCARDED;
                continue;
            } else if (preg_match("/smtp delivery failures/", $line)) {
                $section = self::TYPE_DELIVERY_FAILURES;
                continue;
            } else if (preg_match("/Warnings/", $line)) {
                $section = self::TYPE_WARNING;
                continue;
            }

            // Daily report data
            //------------------

            if ($section == "daily") {
                $line = preg_replace("/\s+/", " ", $line);
                $lineparts = explode(" ", $line);
                if (!preg_match("/^\d+/", $lineparts[2]))
                    continue;
                
                $unixtime = strtotime($lineparts[0] . " " . $lineparts[1] . " " . $lineparts[2]);
                $thedate = strftime("%b %e %Y", $unixtime);

                $daily_data[$thedate][self::TYPE_RECEIVED] = $lineparts[4];
                $daily_data[$thedate][self::TYPE_DELIVERED] = $lineparts[5];
                $daily_data[$thedate][self::TYPE_DEFERRED] = $lineparts[6];
                $daily_data[$thedate][self::TYPE_BOUNCED] = $lineparts[7];
                $daily_data[$thedate][self::TYPE_REJECTED] = $lineparts[8];

            // Grand totals
            //-------------

            } else if ($section == "messages") {
                if (preg_match("/received/", $line)) {
                    $summary_data[self::TYPE_RECEIVED] = trim(preg_replace("/received.*/", "", $line));
                    if ($summary_data[self::TYPE_RECEIVED] != 0)
                        $this->nodata = FALSE;
                } else if (preg_match("/delivered/", $line)) {
                    $summary_data[self::TYPE_DELIVERED] = trim(preg_replace("/delivered.*/", "", $line));
                } else if (preg_match("/forwarded/", $line)) {
                    $summary_data[self::TYPE_FORWARDED] = trim(preg_replace("/forwarded.*/", "", $line));
                } else if (preg_match("/deferred/", $line)) {
                    $summary_data[self::TYPE_DEFERRED] = trim(preg_replace("/deferred.*/", "", $line));
                } else if (preg_match("/bounced/", $line)) {
                    $summary_data[self::TYPE_BOUNCED] = trim(preg_replace("/bounced.*/", "", $line));
                } else if (preg_match("/rejected/", $line)) {
                    $summary_data[self::TYPE_REJECTED] = trim(preg_replace("/rejected.*/", "", $line));
                } else if (preg_match("/reject warnings/", $line)) {
                    $summary_data[self::TYPE_REJECT_WARNING] = trim(preg_replace("/reject warnings.*/", "", $line));
                } else if (preg_match("/held/", $line)) {
                    $summary_data[self::TYPE_HELD] = trim(preg_replace("/held.*/", "", $line));
                } else if (preg_match("/discarded/", $line)) {
                    $summary_data[self::TYPE_DISCARDED] = trim(preg_replace("/discarded.*/", "", $line));
                }

            // Summary data
            //-------------

            } else if ($section != "todo") {
                if (! preg_match("/-------/", $line)) {
                    $linecount++;
                    $data[$section][$linecount] = $line;
                }
            }
        }

        $data["daily"] = $daily_data;
        $data["summary"] = $summary_data;

        $this->loaded = TRUE;
        $this->data = $data;
    }

    /**
     * Returns report detail.
     *
     * @param string  $title       report title
     * @param string  $type        report type
     * @param integer $max_records maximum number of records to return
     *
     * @return void
     */

    public function get_report_detail($title, $type, $max_records = 10)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->loaded)
            $this->_get_data();

        $tablerows = "";
        $lastsection = "";
        $linecount = 0;

        foreach ($this->data[$type] as $ignore => $line) {
            $line = preg_replace("/</", "&lt;", $line);
            $line = preg_replace("/>/", "&gt;", $line);
            if (strlen($line) > 80)
                $line = substr($line, 0, 80);

            if (preg_match("/^\s*$/", $line)) {
                // Skip blank lines
            } else if (preg_match("/^\s*[0-9]/", $line)) {
                $tablerows .= "<tr>";
                $tablerows .= "<td><pre style='margin: 0px'>$line</pre></td>";
                $tablerows .= "</tr>";
            } else {
                // Table header
                $tablerows .= "<tr>";
                $tablerows .= "<td class='mytableheader'><pre style='margin: 0px'>$line</pre></td>";
                $tablerows .= "</tr>";
            }
            
            $linecount++;
            if ($linecount > $max_records)
                break;
        }

        // WebFormOpen($_SERVER['PHP_SELF'], "post");
        // WebTableOpen($title, "100%");
        if ($linecount <= 1)
            $tablerows .= "<tr><td align='center'>" . lang('mail_report_nothing_to_report') . "</td></tr>\n";
        // else if ($linecount > $max_records)
        //   $tablereows .= "<tr><td align='center'>" . WebButtonShowFullReport($type) . "</td></tr>\n";
        // WebTableClose("100%");
        // WebFormClose();

        return $tablerows;
    }
}
