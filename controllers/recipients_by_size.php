<?php

/**
 * Mail recipients size report controller.
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
 * Mail recipients size report controller.
 *
 * @category   Apps
 * @package    Mail_Report
 * @subpackage Controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2012 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/mail_report/
 */

class Recipients_By_Size extends ClearOS_Controller
{
    /**
     * Mail recipients size controller.
     *
     * @return view
     */

    function index()
    {
        // Load dependencies
        //------------------

        $this->lang->load('mail_report');
        $this->load->library('mail_report/Mail_Report');

        // Load view data
        //---------------

        try {
            $data['data'] = $this->mail_report->get_recipients_by_size();

            $data['key'] = lang('mail_report_recipient');
            $data['value'] = lang('mail_report_size');
            $data['title'] = lang('mail_report_recipients_by_size');
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load views
        //-----------

       $this->page->view_form('mail_report/key_value', $data, lang('mail_report_recipients_by_size'));
    }
}
