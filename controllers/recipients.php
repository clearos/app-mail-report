<?php

/**
 * Mail recipients report controller.
 *
 * @category   Apps
 * @package    Mail_Report
 * @subpackage Controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2012 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/mail_report/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Mail recipients report controller.
 *
 * @category   Apps
 * @package    Mail_Report
 * @subpackage Controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2012 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/mail_report/
 */

class Recipients extends ClearOS_Controller
{
    /**
     * Default report.
     *
     * @return view
     */

    function index()
    {
        $this->_report('simple');
    }

    /**
     * Full report.
     *
     * @return view
     */

    function full()
    {
        $this->_report('full');
    }

    /**
     * Generic report method.
     *
     * @param string $type report type
     *
     * @return view
     */

    function _report($type)
    {
        // Load dependencies
        //------------------

        $this->lang->load('mail_report');
        $this->lang->load('reports');
        $this->load->library('mail_report/Mail_Report');

        // Load view data
        //---------------

        try {
            $data['data'] = $this->mail_report->get_recipients();

            $data['type'] = 'recipients';
            $data['ranges'] = array(
                'today' => lang('reports_today'),
                'yesterday' => lang('reports_yesterday'),
                'month' => lang('reports_last_thirty_days')
            );

            $data['key'] = lang('mail_report_recipient');
            $data['value'] = lang('mail_report_deliveries');
            $data['title'] = lang('mail_report_recipients');
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load views
        //-----------

        if ($type === 'simple')
            $this->page->view_form('mail_report/simple', $data, lang('mail_report_recipients'), $options);
        else
            $this->page->view_form('mail_report/key_value', $data, lang('mail_report_recipients'), $options);
    }

    /**
     * Report data.
     *
     * @param integer summary_index summarize data after this point
     *
     * @return JSON report data
     */

    function get_data($date_range, $summary_index = 10)
    {
        clearos_profile(__METHOD__, __LINE__);

        // Load dependencies
        //------------------

        $this->load->library('mail_report/Mail_Report');

        // Load data
        //----------

        try {
            $data = $this->mail_report->get_recipients($date_range, $summary_index);
        } catch (Exception $e) {
            echo json_encode(array('code' => clearos_exception_code($e), 'errmsg' => clearos_exception_message($e)));
        }

        // Show data
        //----------

        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Fri, 01 Jan 2010 05:00:00 GMT');
        header('Content-type: application/json');
        echo json_encode($data);
    }
}
