<?php

/**
 * Mail domain sent report controller.
 *
 * @category   apps
 * @package    mail-report
 * @subpackage controllers
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
 * Mail domain sent report controller.
 *
 * @category   apps
 * @package    mail-report
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2012 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/mail_report/
 */

class Domain_Sent extends ClearOS_Controller
{
    /**
     * Mail senders controller.
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
            $data['data'] = $this->mail_report->get_domain_sent();
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load views
        //-----------

       $this->page->view_form('mail_report/domain_sent', $data, lang('mail_report_domain_sent_report'));
    }
}
