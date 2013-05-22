<?php

/**
 * Domain reports view.
 *
 * @category   apps
 * @package    mail-report
 * @subpackage views
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
// Load dependencies
///////////////////////////////////////////////////////////////////////////////

$this->lang->load('mail_report');

///////////////////////////////////////////////////////////////////////////////
// Anchors
///////////////////////////////////////////////////////////////////////////////

$anchors = array();

///////////////////////////////////////////////////////////////////////////////
// Headers
///////////////////////////////////////////////////////////////////////////////

$headers = array(
    lang('mail_report_domain'),
    lang('mail_report_deliveries'),
    lang('mail_report_size')
);

///////////////////////////////////////////////////////////////////////////////
// Items
///////////////////////////////////////////////////////////////////////////////

foreach ($data as $domain => $details) {
    $item['title'] = $domain;
    $item['details'] = array(
        $domain,
        $details['count'],
        $details['size'],
    );

    $items[] = $item;
}

///////////////////////////////////////////////////////////////////////////////
// List table
///////////////////////////////////////////////////////////////////////////////

echo summary_table(
    lang('mail_report_domain_received_report'),
    $anchors,
    $headers,
    $items,
    array(
        'no_action' => TRUE,
        'sort-default-col' => 1,
        'sort-default-dir' => 'desc'
    )
);
