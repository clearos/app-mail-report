<?php

/**
 * Mail report javascript helper.
 *
 * @category   Apps
 * @package    Mail_Report
 * @subpackage Javascript
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
// FIXME: remove "received" and "delivered"?

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
// J A V A S C R I P T
///////////////////////////////////////////////////////////////////////////////

header('Content-Type:application/x-javascript');
?>

$(document).ready(function() {

    // Translations
    //-------------

    lang_received = '<?php echo lang("mail_report_received"); ?>';
    lang_delivered = '<?php echo lang("mail_report_delivered"); ?>';
    lang_forwarded = '<?php echo lang("mail_report_forwarded"); ?>';
    lang_deferred = '<?php echo lang("mail_report_deferred"); ?>';
    lang_bounced = '<?php echo lang("mail_report_bounced"); ?>';
    lang_rejected = '<?php echo lang("mail_report_rejected"); ?>';
    lang_held = '<?php echo lang("mail_report_held"); ?>';
    lang_discarded = '<?php echo lang("mail_report_discarded"); ?>';

    // Main
    //-----

    if ($('#mail_report_chart').length != 0) 
        get_report();
});


function get_report() {
    $.ajax({
        url: '/app/mail_report/dashboard/get_data',
        method: 'GET',
        dataType: 'json',
        success : function(payload) {
            graph_data(payload);
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            window.setTimeout(get_report, 3000);
        }
    });
}

function graph_data(payload) {

    var series = new Array();
    var ticks = new Array();

//    var ticks = [ lang_received, lang_delivered, lang_forwarded, lang_deferred, lang_bounced, lang_rejected, lang_held, lang_discarded ];
//    var series = [ payload.received, payload.delivered, payload.forwarded, payload.deferred, payload.bounced, payload.rejected, payload.held, payload.discarded ];
    var ticks = ['a', 'b', 'c', 'd'];
    var series = [ 10, 11, 12, 13 ];

    var data = new Array();
    var data = [
        [ lang_received, payload.received],
        [ lang_delivered, payload.delivered],
        [ lang_forwarded, payload.forwarded],
        [ lang_deferred, payload.deferred],
        [ lang_bounced, payload.bounced],
        [ lang_rejected, payload.rejected],
        [ lang_held, payload.held],
        [ lang_discarded, payload.discarded],
    ];

    var data2 = new Array();
    var data2 = [
        [ payload.received, lang_received ],
        [ payload.delivered, lang_delivered ],
        [ payload.forwarded, lang_forwarded ],
        [ payload.deferred, lang_deferred ],
        [ payload.bounced, lang_bounced ],
        [ payload.rejected, lang_rejected ],
        [ payload.held, lang_held ],
        [ payload.discarded, lang_discarded ],
    ];
//    var chart = jQuery.jqplot ('mail_report_chart', [series],
    var chart = jQuery.jqplot ('mail_report_chart', [data2],
    {
        animate: !$.jqplot.use_excanvas,
        seriesColors: [ '#E1852E' ],
        seriesDefaults: {
            renderer: jQuery.jqplot.BarRenderer,
            rendererOptions: {
                barDirection: 'horizontal'
            },
            pointLabels: { show: true }
        },
        axes: {
            yaxis: {
                renderer: $.jqplot.CategoryAxisRenderer,
                ticks: { fontSize: "30px" }
            }
        },
        axesDefaults: {
            max: null
        },

        highlighter: { show: false }
    });

    chart.redraw();
}

// vim: ts=4 syntax=javascript
