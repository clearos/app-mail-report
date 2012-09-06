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

    // Events
    //-------

    $('#range').click(function(){
        generate_report('senders', $('#range').val());
    });

    // Main
    //-----

    if ($('#mail_report_dashboard').length != 0) 
        generate_dashboard_report();

    if ($('#mail_report_month').length != 0) 
        generate_month_report();

    if ($('#mail_report_senders').length != 0) 
        generate_report('senders', 'today');

    if ($('#mail_report_recipients').length != 0) 
        generate_report('recipients', 'today');
});

/**
 * Ajax call for standard report.
 */

function generate_report(type, range) {
    $.ajax({
        url: '/app/mail_report/' + type + '/get_data/' + range + '/10',
        method: 'GET',
        dataType: 'json',
        success : function(payload) {
            create_chart(type, payload);
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            window.setTimeout(generate_report, 3000);
        }
    });
}

/**
 * Ajax call for dashboard report.
 */

function generate_dashboard_report() {
    $.ajax({
        url: '/app/mail_report/dashboard/get_data',
        method: 'GET',
        dataType: 'json',
        success : function(payload) {
            create_dashboard_chart(payload);
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            window.setTimeout(generate_dashboard_report, 3000);
        }
    });
}

/**
 * Ajax call for month report.
 */

function generate_month_report() {
    $.ajax({
        url: '/app/mail_report/month/get_data',
        method: 'GET',
        dataType: 'json',
        success : function(payload) {
            create_month_chart(payload);
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            window.setTimeout(generate_month_report, 3000);
        }
    });
}

/**
 * Generates standard report.
 */

function create_chart(type, payload) {
    var data = new Array();
    var chart_id = 'mail_report_' + type;

    for (var item_info in payload) {
        key = item_info;
        value = payload[key];
        data.push([key, value]);
    }

    var chart = jQuery.jqplot (chart_id, [data],
    {
        legend: { show: true, location: 'e' },
        seriesDefaults: {
            renderer: jQuery.jqplot.PieRenderer,
            shadow: true,
            rendererOptions: {
                showDataLabels: true,
                sliceMargin: 8,
                dataLabels: 'value'
            }
        },
        grid: {
            gridLineColor: 'transparent',
            background: 'transparent',
            borderColor: 'transparent',
            shadow: false
        }
    });

    chart.redraw();
}

/**
 * Generates dashboard report.
 */

function create_dashboard_chart(payload) {

    var data = [
        [ payload.received, lang_received ],
        [ payload.delivered, lang_delivered ],
        [ payload.forwarded, lang_forwarded ],
        [ payload.deferred, lang_deferred ],
        [ payload.bounced, lang_bounced ],
        [ payload.rejected, lang_rejected ],
        [ payload.held, lang_held ],
        [ payload.discarded, lang_discarded ],
    ];

    var chart = jQuery.jqplot ('mail_report_dashboard', [data],
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

/**
 * Generates month report.
 */

function create_month_chart(payload) {

    data = Array();
    received = Array();
    delivered = Array();
    deferred = Array();
    bounced = Array();

    for (var day_info in payload) {
        if (payload.hasOwnProperty(day_info)) {
            received.push([payload[day_info].received, day_info]);
            delivered.push([payload[day_info].delivered, day_info]);
            deferred.push([payload[day_info].deferred, day_info]);
            bounced.push([payload[day_info].bounced, day_info]);
        }
    }

    var chart = jQuery.jqplot ('mail_report_month', [received, delivered, deferred, bounced],
    {
        animate: !$.jqplot.use_excanvas,
        seriesDefaults: {
            renderer: jQuery.jqplot.BarRenderer,
            rendererOptions: {
                barDirection: 'horizontal'
            },
            pointLabels: { show: true, location: 'e', edgeTolerance: -15 },
        },
        axes: {
            yaxis: {
                renderer: $.jqplot.CategoryAxisRenderer,
            }
        }
    });

    chart.redraw();
}

// vim: ts=4 syntax=javascript
