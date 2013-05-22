<?php

/**
 * Mail report controller.
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
 * Mail report controller.
 *
 * @category   apps
 * @package    mail-report
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2012 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/mail_report/
 */

class Mail_Report extends ClearOS_Controller
{
    /**
     * Mail report summary view.
     *
     * @return view
     */

    function index()
    {
        // Load libraries
        //---------------

        $this->lang->load('mail_report');

        // Load views
        //-----------

        $views = array(
            'mail_report/senders',
            'mail_report/recipients'
        );
/*
        $views = array(
            'mail_report/dashboard',
            'mail_report/domain_received',
            'mail_report/domain_sent',
            'mail_report/recipients',
            'mail_report/recipients_by_size',
            'mail_report/senders',
            'mail_report/senders_by_size',
            'mail_report/warnings',
            'mail_report/failures'
        );
*/

        $this->page->view_forms($views, lang('mail_report_app_name'));
    }
}
